<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FundRefund;
use App\Models\FundReport;
use App\Models\FundRequest;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Posting jurnal otomatis untuk siklus dana, mengikuti perlakuan aplikasi
 * keuangan lama (Java):
 *
 *  - Pencairan kegiatan/pengadaan  : Dr Uang Muka          / Cr Bank
 *  - Pencairan pembayaran          : Dr Beban (per akun)   / Cr Bank
 *  - Laporan disetujui             : Dr Beban (per akun)   / Cr Uang Muka (sebesar terpakai)
 *  - Pengembalian dikonfirmasi     : Dr Bank               / Cr Uang Muka (sebesar sisa)
 *
 * "Per akun": debit dipecah mengikuti akun-akun di rincian kegiatan yang
 * benar-benar diajukan (fund_request_details) -- rincian dengan akun yang
 * sama digabung jadi satu baris, akun berbeda jadi baris terpisah. Untuk
 * realisasi laporan (amount_used bisa < amount diajukan), porsi tiap akun
 * dialokasikan proporsional terhadap nominal yang direncanakan per akun.
 * Kalau pengajuan tidak punya rincian per baris (data lama sebelum fitur
 * ini ada), jatuh balik ke satu akun representatif (expenseAccount()).
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
        $fundRequest->loadMissing(['budgetProgram.details', 'disburseAccount', 'details.account']);

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

        $amount = (float) $fundRequest->amount;

        if ($type === 'pembayaran') {
            $description = 'Pembayaran langsung ' . $fundRequest->reference . ' — ' . $fundRequest->title;

            $splitLines = $this->splitAccountAmounts($fundRequest->details, $amount);
            if ($splitLines) {
                $entry = $this->createSplitEntry(
                    $fundRequest->organization_id, $user, 'fund_request.disbursement', $fundRequest->id,
                    $description, $splitLines, $bank, $amount,
                );
                return [$entry, null];
            }

            $debit = $this->expenseAccount($fundRequest);
            if (!$debit) {
                return [null, 'Jurnal tidak dibuat: akun beban belum diatur pada program kerja "' . $fundRequest->budgetProgram->name . '".'];
            }

            $entry = $this->createEntry(
                $fundRequest->organization_id, $user, 'fund_request.disbursement', $fundRequest->id,
                $description, $debit, $bank, $amount,
            );
            return [$entry, null];
        }

        $debit = $this->advanceAccount($fundRequest->organization_id, $type);
        if (!$debit) {
            return [null, 'Jurnal tidak dibuat: akun "' . $this->advanceName($type) . '" (' . self::ADVANCE_CODES[$type] . ') belum ada di COA organisasi ini.'];
        }
        $description = 'Pencairan uang muka ' . $fundRequest->reference . ' — ' . $fundRequest->title;

        $entry = $this->createEntry(
            $fundRequest->organization_id,
            $user,
            'fund_request.disbursement',
            $fundRequest->id,
            $description,
            $debit,
            $bank,
            $amount,
        );

        return [$entry, null];
    }

    public function postReportApproval(FundReport $fundReport, User $user): array
    {
        $fundReport->loadMissing('fundRequest.budgetProgram.details', 'fundRequest.details.account');
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

        $amountUsed  = (float) $fundReport->amount_used;
        $description = 'Realisasi laporan dana ' . $fundRequest->reference . ' — ' . $fundRequest->title;

        // Dipecah proporsional per akun sesuai porsi rencana masing-masing di rincian yang
        // diajukan -- misal rencana 60% Bensin/40% Konsumsi, dan yang kepakai cuma 80% dari
        // total diajukan, maka realisasinya 80% dari porsi masing-masing akun tsb.
        $splitLines = $this->splitAccountAmounts($fundRequest->details, $amountUsed);
        if ($splitLines) {
            $entry = $this->createSplitEntry(
                $fundRequest->organization_id, $user, 'fund_report.approval', $fundReport->id,
                $description, $splitLines, $advance, $amountUsed,
            );
            return [$entry, null];
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
            $description,
            $expense,
            $advance,
            $amountUsed,
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

    // Sama seperti createEntry(), tapi sisi debit boleh lebih dari satu baris --
    // satu baris per akun berbeda (dari splitAccountAmounts()).
    private function createSplitEntry(
        string $organizationId,
        User $user,
        string $sourceType,
        string $sourceId,
        string $description,
        Collection $debitLines,
        Account $creditAccount,
        float $totalAmount,
    ): JournalEntry {
        return DB::transaction(function () use ($organizationId, $user, $sourceType, $sourceId, $description, $debitLines, $creditAccount, $totalAmount) {
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

            $sort = 0;
            foreach ($debitLines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account']->id,
                    'description'      => $description,
                    'debit'            => $line['amount'],
                    'credit'           => 0,
                    'sort_order'       => $sort++,
                ]);
            }

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $creditAccount->id,
                'description'      => $description,
                'debit'            => 0,
                'credit'           => $totalAmount,
                'sort_order'       => $sort,
            ]);

            return $entry;
        });
    }

    // Kelompokkan rincian pengajuan (fund_request_details) per akun, lalu alokasikan
    // $targetTotal ke tiap kelompok proporsional terhadap porsi rencananya masing-masing
    // (baris terakhir menyerap sisa pembulatan supaya totalnya pas sama dengan $targetTotal).
    // Rincian dengan akun sama otomatis tergabung jadi satu baris. Null kalau pengajuan
    // tidak punya rincian per baris sama sekali (data lama) atau tidak ada akun valid --
    // caller lalu jatuh balik ke satu akun representatif (expenseAccount()).
    private function splitAccountAmounts(Collection $details, float $targetTotal): ?Collection
    {
        $details = $details->filter(fn($d) => $d->account_id && $d->account);
        if ($details->isEmpty()) {
            return null;
        }

        $grandTotal = (float) $details->sum('total_amount');
        if ($grandTotal <= 0) {
            return null;
        }

        $groups = $details->groupBy('account_id')->values();
        $lines = collect();
        $allocated = 0.0;
        $lastIndex = $groups->count() - 1;

        foreach ($groups as $i => $group) {
            $account = $group->first()->account;

            if ($i === $lastIndex) {
                $amount = round($targetTotal - $allocated, 2);
            } else {
                $share = (float) $group->sum('total_amount') / $grandTotal;
                $amount = round($targetTotal * $share, 2);
                $allocated += $amount;
            }

            if ($amount > 0) {
                $lines->push(['account' => $account, 'amount' => $amount]);
            }
        }

        return $lines->isEmpty() ? null : $lines;
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
