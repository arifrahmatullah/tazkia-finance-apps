<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeginningBalanceController extends Controller
{
    public const SOURCE_TYPE = 'beginning_balance';

    public function index(Request $request)
    {
        $user = auth()->user();
        $organizations = $this->allowedOrgs($user)->orderBy('name')->get();

        $orgId = $request->input('organization_id') ?: $organizations->first()?->id;
        abort_unless($orgId && $organizations->contains('id', $orgId), 403);

        $year = (int) ($request->input('year') ?: now()->year);
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $entry = $this->findEntry($orgId, $year);
        $existing = $entry ? $entry->lines->keyBy('account_id') : collect();

        $accounts = Account::where('organization_id', $orgId)
            ->where('is_header', false)
            ->where(fn($q) => $q->where('is_active', true)->orWhereIn('id', $existing->keys()))
            ->orderBy('code')
            ->get();

        $yearOptions = collect(range(now()->year + 1, now()->year - 5));

        return view('beginning-balances.index', compact(
            'organizations', 'orgId', 'year', 'yearOptions', 'entry', 'existing', 'accounts'
        ));
    }

    public function save(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'organization_id'    => 'required|exists:organizations,id',
            'year'               => 'required|integer|min:2000|max:2100',
            'balances'           => 'nullable|array',
            'balances.*.debit'   => 'nullable|numeric|min:0',
            'balances.*.credit'  => 'nullable|numeric|min:0',
        ]);

        abort_unless($user->canAccessOrganization($request->organization_id), 403);

        $orgId = $request->organization_id;
        $year  = (int) $request->year;

        // Kumpulkan baris yang terisi, urut sesuai kode akun
        $balances = $request->input('balances', []);
        $accounts = Account::whereIn('id', array_keys($balances))
            ->where('organization_id', $orgId)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $debit  = (float) ($balances[$account->id]['debit'] ?? 0);
            $credit = (float) ($balances[$account->id]['credit'] ?? 0);

            if ($debit <= 0 && $credit <= 0) {
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                return back()->withInput()->withErrors([
                    'balances' => "Akun {$account->code} — {$account->name} hanya boleh diisi salah satu sisi (debit atau kredit).",
                ]);
            }

            $rows[] = [
                'account_id' => $account->id,
                'debit'      => $debit,
                'credit'     => $credit,
            ];
            $totalDebit  += $debit;
            $totalCredit += $credit;
        }

        // Semua kosong = hapus saldo awal tahun tersebut (jika ada)
        if (empty($rows)) {
            $entry = $this->findEntry($orgId, $year);
            if ($entry) {
                DB::transaction(function () use ($entry) {
                    $entry->lines()->delete();
                    $entry->delete();
                });
                return redirect()
                    ->route('beginning-balances.index', ['organization_id' => $orgId, 'year' => $year])
                    ->with('success', "Saldo awal tahun {$year} berhasil dihapus.");
            }
            return back()->withInput()->withErrors([
                'balances' => 'Isi minimal satu akun untuk menyimpan saldo awal.',
            ]);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->withErrors([
                'balances' => 'Total debit harus sama dengan total kredit (selisih Rp ' .
                    number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . ').',
            ]);
        }

        DB::transaction(function () use ($orgId, $year, $rows, $user) {
            $entry = $this->findEntry($orgId, $year, lock: true);

            if (!$entry) {
                $entry = JournalEntry::create([
                    'organization_id' => $orgId,
                    'entry_date'      => "{$year}-01-01",
                    'reference'       => $this->generateReference($year),
                    'description'     => "Saldo awal per 1 Januari {$year}",
                    'status'          => 'posted',
                    'source_type'     => self::SOURCE_TYPE,
                    'created_by'      => $user->id,
                    'posted_at'       => now(),
                    'posted_by'       => $user->id,
                ]);
            }

            $entry->lines()->delete();

            foreach ($rows as $i => $row) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $row['account_id'],
                    'description'      => "Saldo awal {$year}",
                    'debit'            => $row['debit'],
                    'credit'           => $row['credit'],
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()
            ->route('beginning-balances.index', ['organization_id' => $orgId, 'year' => $year])
            ->with('success', "Saldo awal tahun {$year} berhasil disimpan (Rp " . number_format($totalDebit, 0, ',', '.') . ' — balance).');
    }

    private function findEntry(string $orgId, int $year, bool $lock = false): ?JournalEntry
    {
        return JournalEntry::with('lines')
            ->where('organization_id', $orgId)
            ->where('source_type', self::SOURCE_TYPE)
            ->whereYear('entry_date', $year)
            ->when($lock, fn($q) => $q->lockForUpdate())
            ->first();
    }

    private function generateReference(int $year): string
    {
        $prefix = "SA-{$year}-";

        // Global (tanpa scope organisasi) karena kolom reference unique se-tabel;
        // termasuk yang soft-deleted agar nomor tidak tabrakan.
        $last = JournalEntry::withTrashed()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function allowedOrgs($user)
    {
        $orgIds = $user->organizationIds();
        return Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds));
    }
}
