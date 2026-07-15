<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FundRefund;
use App\Models\FundReport;
use App\Models\FundRequest;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Posting jurnal otomatis untuk siklus dana, mengikuti perlakuan aplikasi
 * keuangan lama (Java):
 *
 *  - Pencairan kegiatan/pengadaan  : Dr Uang Muka        / Cr Bank
 *  - Pencairan pembayaran          : Dr Beban (langsung) / Cr Bank
 *  - Laporan disetujui             : Dr Beban            / Cr Uang Muka (sebesar terpakai)
 *  - Pengembalian dikonfirmasi     : Dr Bank             / Cr Uang Muka (sebesar sisa)
 *
 * Semua method mengembalikan [JournalEntry|null, string|null $warning].
 * Jika akun yang dibutuhkan tidak ada di COA organisasi, jurnal dilewati
 * dengan pesan peringatan — proses bisnisnya tidak diblokir.
 */
class FundJournalService
{
    public const ADVANCE_CODES = [
        'kegiatan'  => '1.1.03.00.01', // Uang Muka Kegiatan
        'pengadaan' => '1.1.03.00.02', // Uang Muka Pengadaan Barang dan Jasa
    ];

    public function postDisbursement(FundRequest $fundRequest, User $user): array
    {
        $fundRequest->loadMissing(['budgetProgram.details', 'disburseAccount']);

        if ($existing = $this->existingEntry('fund_request.disbursement', $fundRequest->id)) {
            return [$existing, null];
        }

        $type = $fundRequest->budgetProgram?->type;
        if (!$type) {
            return [null, 'Jurnal tidak dibuat: program kerja belum memiliki jenis (pengadaan/kegiatan/pembayaran).'];
        }

        $bank = $fundRequest->disburseAccount;
        if (!$bank) {
            return [null, 'Jurnal tidak dibuat: rekening pencairan tidak ditemukan.'];
        }

        if ($type === 'pembayaran') {
            $debit = $this->expenseAccount($fundRequest);
            if (!$debit) {
                return [null, 'Jurnal tidak dibuat: akun beban belum diatur pada program kerja "' . $fundRequest->budgetProgram->name . '".'];
            }
            $description = 'Pembayaran langsung ' . $fundRequest->reference . ' — ' . $fundRequest->title;
        } else {
            $debit = $this->advanceAccount($fundRequest->organization_id, $type);
            if (!$debit) {
                return [null, 'Jurnal tidak dibuat: akun "' . $this->advanceName($type) . '" (' . self::ADVANCE_CODES[$type] . ') belum ada di COA organisasi ini.'];
            }
            $description = 'Pencairan uang muka ' . $fundRequest->reference . ' — ' . $fundRequest->title;
        }

        $entry = $this->createEntry(
            $fundRequest->organization_id,
            $user,
            'fund_request.disbursement',
            $fundRequest->id,
            $description,
            $debit,
            $bank,
            (float) $fundRequest->amount,
        );

        return [$entry, null];
    }

    public function postReportApproval(FundReport $fundReport, User $user): array
    {
        $fundReport->loadMissing('fundRequest.budgetProgram.details');
        $fundRequest = $fundReport->fundRequest;

        if ($existing = $this->existingEntry('fund_report.approval', $fundReport->id)) {
            return [$existing, null];
        }

        $type = $fundRequest->budgetProgram?->type;
        if (!in_array($type, ['kegiatan', 'pengadaan'], true)) {
            // Pembayaran dibebankan penuh saat pencairan; tanpa jenis tidak ada uang muka
            return [null, null];
        }

        $advance = $this->advanceAccount($fundRequest->organization_id, $type);
        if (!$advance) {
            return [null, 'Jurnal realisasi tidak dibuat: akun "' . $this->advanceName($type) . '" (' . self::ADVANCE_CODES[$type] . ') belum ada di COA organisasi ini.'];
        }

        $expense = $this->expenseAccount($fundRequest);
        if (!$expense) {
            return [null, 'Jurnal realisasi tidak dibuat: akun beban belum diatur pada program kerja "' . $fundRequest->budgetProgram->name . '".'];
        }

        $entry = $this->createEntry(
            $fundRequest->organization_id,
            $user,
            'fund_report.approval',
            $fundReport->id,
            'Realisasi laporan dana ' . $fundRequest->reference . ' — ' . $fundRequest->title,
            $expense,
            $advance,
            (float) $fundReport->amount_used,
        );

        return [$entry, null];
    }

    public function postRefundConfirmation(FundRefund $fundRefund, User $user): array
    {
        $fundRefund->loadMissing(['fundRequest.budgetProgram', 'refundAccount']);
        $fundRequest = $fundRefund->fundRequest;

        if ($existing = $this->existingEntry('fund_refund.confirmation', $fundRefund->id)) {
            return [$existing, null];
        }

        $type = $fundRequest->budgetProgram?->type;
        if (!in_array($type, ['kegiatan', 'pengadaan'], true)) {
            return [null, null];
        }

        $advance = $this->advanceAccount($fundRequest->organization_id, $type);
        if (!$advance) {
            return [null, 'Jurnal pengembalian tidak dibuat: akun "' . $this->advanceName($type) . '" (' . self::ADVANCE_CODES[$type] . ') belum ada di COA organisasi ini.'];
        }

        $bank = $fundRefund->refundAccount;
        if (!$bank) {
            return [null, 'Jurnal pengembalian tidak dibuat: rekening tujuan pengembalian tidak ditemukan.'];
        }

        $entry = $this->createEntry(
            $fundRequest->organization_id,
            $user,
            'fund_refund.confirmation',
            $fundRefund->id,
            'Pengembalian sisa dana ' . $fundRequest->reference . ' — ' . $fundRequest->title,
            $bank,
            $advance,
            (float) $fundRefund->amount,
        );

        return [$entry, null];
    }

    private function createEntry(
        string $organizationId,
        User $user,
        string $sourceType,
        string $sourceId,
        string $description,
        Account $debitAccount,
        Account $creditAccount,
        float $amount,
    ): JournalEntry {
        return DB::transaction(function () use ($organizationId, $user, $sourceType, $sourceId, $description, $debitAccount, $creditAccount, $amount) {
            $date = now()->toDateString();

            $entry = JournalEntry::create([
                'organization_id' => $organizationId,
                'entry_date'      => $date,
                'reference'       => JournalEntry::generateReference($organizationId, $date),
                'description'     => $description,
                'status'          => 'posted',
                'source_type'     => $sourceType,
                'source_id'       => $sourceId,
                'created_by'      => $user->id,
                'posted_at'       => now(),
                'posted_by'       => $user->id,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $debitAccount->id,
                'description'      => $description,
                'debit'            => $amount,
                'credit'           => 0,
                'sort_order'       => 0,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $creditAccount->id,
                'description'      => $description,
                'debit'            => 0,
                'credit'           => $amount,
                'sort_order'       => 1,
            ]);

            return $entry;
        });
    }

    private function existingEntry(string $sourceType, string $sourceId): ?JournalEntry
    {
        return JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();
    }

    private function advanceAccount(string $organizationId, string $type): ?Account
    {
        return Account::where('organization_id', $organizationId)
            ->where('code', self::ADVANCE_CODES[$type])
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();
    }

    private function advanceName(string $type): string
    {
        return $type === 'kegiatan' ? 'Uang Muka Kegiatan' : 'Uang Muka Pengadaan Barang dan Jasa';
    }

    // Akun beban program: akun di program kerja, atau akun rincian pertama yang terisi
    private function expenseAccount(FundRequest $fundRequest): ?Account
    {
        $program = $fundRequest->budgetProgram;
        if (!$program) return null;

        if ($program->account && !$program->account->is_header) {
            return $program->account;
        }

        $detailAccountId = $program->details->firstWhere('account_id', '!=', null)?->account_id;

        return $detailAccountId ? Account::find($detailAccountId) : null;
    }
}
