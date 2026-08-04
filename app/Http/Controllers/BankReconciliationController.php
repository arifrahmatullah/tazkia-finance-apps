<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        $reconciliations = BankReconciliation::with(['account', 'organization', 'items'])
            ->when($orgId, fn($q) => $q->where('organization_id', $orgId))
            ->orderByDesc('period')
            ->get();

        $kasAccounts = $orgId ? $this->kasAccounts($orgId) : collect();

        return view('bank-reconciliations.index', compact(
            'organizations', 'orgId', 'reconciliations', 'kasAccounts'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id'   => 'required|exists:organizations,id',
            'account_id'        => 'required|exists:accounts,id',
            'period'            => 'required|date_format:Y-m',
            'statement_balance' => 'required|numeric',
        ]);

        abort_unless(auth()->user()->canAccessOrganization($validated['organization_id']), 403);

        $account = Account::where('id', $validated['account_id'])
            ->where('organization_id', $validated['organization_id'])
            ->firstOrFail();
        abort_unless(str_starts_with($account->code, '1.1.01.'), 422, 'Akun yang dipilih bukan akun kas/bank.');

        $period = $validated['period'] . '-01';

        if (BankReconciliation::where('account_id', $account->id)->where('period', $period)->exists()) {
            return back()->withInput()->withErrors([
                'period' => 'Rekonsiliasi untuk akun & periode ini sudah pernah dibuat.',
            ]);
        }

        $reconciliation = BankReconciliation::create([
            'organization_id'   => $validated['organization_id'],
            'account_id'        => $account->id,
            'period'            => $period,
            'statement_balance' => $validated['statement_balance'],
            'status'            => 'draft',
            'created_by'        => auth()->id(),
        ]);

        return redirect()->route('bank-reconciliations.show', $reconciliation)
            ->with('success', 'Rekonsiliasi dibuat. Silakan tambahkan item penyesuai jika ada selisih.');
    }

    public function show(BankReconciliation $bankReconciliation)
    {
        abort_unless(auth()->user()->canAccessOrganization($bankReconciliation->organization_id), 403);

        $bankReconciliation->load(['account', 'organization', 'items.counterAccount', 'items.journalEntry', 'creator', 'completer']);

        $bookBalance = $this->liveBookBalance($bankReconciliation->account_id, $bankReconciliation->periodEnd()->toDateString());

        $bukuItems = $bankReconciliation->items->where('side', 'buku')->values();
        $bankItems = $bankReconciliation->items->where('side', 'bank')->values();

        // Item 'buku' yang sudah diposting jurnalnya sudah ikut dalam $bookBalance (live),
        // jadi hanya item yang BELUM diposting yang perlu ditambahkan sebagai penyesuai.
        $pendingBukuTotal = $bukuItems->whereNull('journal_entry_id')->sum('amount');
        $bankItemsTotal = $bankItems->sum('amount');

        $adjustedBook = $bookBalance + $pendingBukuTotal;
        $adjustedBank = $bankReconciliation->statement_balance + $bankItemsTotal;
        $difference = $adjustedBank - $adjustedBook;
        $isMatched = abs($difference) < 0.5;

        $counterAccounts = Account::where('organization_id', $bankReconciliation->organization_id)
            ->where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'account_type']);

        $unpostedBukuCount = $bukuItems->whereNull('journal_entry_id')->count();

        return view('bank-reconciliations.show', compact(
            'bankReconciliation', 'bookBalance', 'bukuItems', 'bankItems',
            'pendingBukuTotal', 'bankItemsTotal', 'adjustedBook', 'adjustedBank',
            'difference', 'isMatched', 'counterAccounts', 'unpostedBukuCount'
        ));
    }

    public function storeItem(Request $request, BankReconciliation $bankReconciliation)
    {
        abort_unless(auth()->user()->canAccessOrganization($bankReconciliation->organization_id), 403);
        abort_unless($bankReconciliation->isDraft(), 422, 'Rekonsiliasi ini sudah selesai, tidak bisa diubah.');

        $validated = $request->validate([
            'side'                => 'required|in:buku,bank',
            'description'         => 'required|string|max:255',
            'amount'              => 'required|numeric|not_in:0',
            'counter_account_id'  => 'required_if:side,buku|nullable|exists:accounts,id',
        ]);

        $bankReconciliation->items()->create([
            'side'               => $validated['side'],
            'description'        => $validated['description'],
            'amount'             => $validated['amount'],
            'counter_account_id' => $validated['side'] === 'buku' ? $validated['counter_account_id'] : null,
            'created_by'         => auth()->id(),
        ]);

        return back()->with('success', 'Item penyesuai berhasil ditambahkan.');
    }

    public function destroyItem(BankReconciliationItem $item)
    {
        $reconciliation = $item->bankReconciliation;
        abort_unless(auth()->user()->canAccessOrganization($reconciliation->organization_id), 403);
        abort_unless($reconciliation->isDraft(), 422, 'Rekonsiliasi ini sudah selesai, tidak bisa diubah.');

        if ($item->isPosted()) {
            return back()->withErrors(['item' => 'Item ini sudah diposting ke jurnal dan tidak bisa dihapus.']);
        }

        $item->delete();

        return back()->with('success', 'Item penyesuai berhasil dihapus.');
    }

    // Posting satu jurnal gabungan untuk semua item sisi "buku" yang belum dijurnal:
    // debit/kredit akun kas vs akun lawan masing-masing item, sesuai tanda amount-nya.
    public function postAdjustments(BankReconciliation $bankReconciliation)
    {
        abort_unless(auth()->user()->canAccessOrganization($bankReconciliation->organization_id), 403);
        abort_unless($bankReconciliation->isDraft(), 422, 'Rekonsiliasi ini sudah selesai.');

        $result = DB::transaction(function () use ($bankReconciliation) {
            $items = $bankReconciliation->items()
                ->where('side', 'buku')
                ->whereNull('journal_entry_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return null;
            }

            $lines = [];
            $sortOrder = 0;

            foreach ($items as $item) {
                $amount = abs($item->amount);
                $kasDebit = $item->amount > 0;

                $lines[] = [
                    'account_id'  => $bankReconciliation->account_id,
                    'description' => $item->description,
                    'debit'       => $kasDebit ? $amount : 0,
                    'credit'      => $kasDebit ? 0 : $amount,
                    'sort_order'  => $sortOrder,
                ];
                $lines[] = [
                    'account_id'  => $item->counter_account_id,
                    'description' => $item->description,
                    'debit'       => $kasDebit ? 0 : $amount,
                    'credit'      => $kasDebit ? $amount : 0,
                    'sort_order'  => $sortOrder + 1,
                ];
                $sortOrder += 2;
            }

            $entry = JournalEntry::create([
                'organization_id' => $bankReconciliation->organization_id,
                'entry_date'      => $bankReconciliation->periodEnd()->toDateString(),
                'reference'       => $this->generateReference($bankReconciliation->periodEnd()),
                'description'     => 'Jurnal penyesuaian rekonsiliasi bank periode ' . $bankReconciliation->period->translatedFormat('F Y'),
                'status'          => 'posted',
                'source_type'     => 'bank_reconciliation',
                'created_by'      => auth()->id(),
                'posted_at'       => now(),
                'posted_by'       => auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create($line + ['journal_entry_id' => $entry->id]);
            }

            $items->each->update(['journal_entry_id' => $entry->id]);

            return ['count' => $items->count()];
        });

        if (!$result) {
            return back()->with('info', 'Tidak ada item sisi "buku" yang perlu diposting.');
        }

        return back()->with('success', "Jurnal penyesuaian berhasil diposting untuk {$result['count']} item.");
    }

    public function complete(BankReconciliation $bankReconciliation)
    {
        abort_unless(auth()->user()->canAccessOrganization($bankReconciliation->organization_id), 403);
        abort_unless($bankReconciliation->isDraft(), 422, 'Rekonsiliasi ini sudah selesai.');

        $bankReconciliation->load('items');
        $bookBalance = $this->liveBookBalance($bankReconciliation->account_id, $bankReconciliation->periodEnd()->toDateString());
        $pendingBuku = $bankReconciliation->items->where('side', 'buku')->whereNull('journal_entry_id')->sum('amount');
        $bankTotal = $bankReconciliation->items->where('side', 'bank')->sum('amount');

        $adjustedBook = $bookBalance + $pendingBuku;
        $adjustedBank = $bankReconciliation->statement_balance + $bankTotal;

        if (abs($adjustedBank - $adjustedBook) >= 0.5) {
            return back()->withErrors([
                'complete' => 'Saldo buku dan saldo bank belum cocok. Selisih Rp ' .
                    number_format(abs($adjustedBank - $adjustedBook), 0, ',', '.') . '. Tambahkan/posting item penyesuai terlebih dahulu.',
            ]);
        }

        $bankReconciliation->update([
            'status'       => 'selesai',
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return redirect()->route('bank-reconciliations.index', ['organization_id' => $bankReconciliation->organization_id])
            ->with('success', 'Rekonsiliasi bank berhasil diselesaikan.');
    }

    public function destroy(BankReconciliation $bankReconciliation)
    {
        abort_unless(auth()->user()->canAccessOrganization($bankReconciliation->organization_id), 403);

        if (!$bankReconciliation->isDraft() || $bankReconciliation->items()->whereNotNull('journal_entry_id')->exists()) {
            return back()->withErrors([
                'delete' => 'Rekonsiliasi tidak bisa dihapus karena sudah selesai atau sudah punya jurnal penyesuaian yang diposting.',
            ]);
        }

        $bankReconciliation->delete();

        return redirect()->route('bank-reconciliations.index', ['organization_id' => $bankReconciliation->organization_id])
            ->with('success', 'Rekonsiliasi berhasil dihapus.');
    }

    // Saldo kas/bank kumulatif menurut jurnal posted s.d. tanggal tertentu
    private function liveBookBalance(string $accountId, string $asOf): float
    {
        $sum = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')->whereDate('entry_date', '<=', $asOf))
            ->selectRaw('COALESCE(SUM(debit), 0) as d, COALESCE(SUM(credit), 0) as c')
            ->first();

        return (float) $sum->d - (float) $sum->c;
    }

    private function kasAccounts(string $orgId)
    {
        return Account::where('organization_id', $orgId)
            ->where('is_header', false)
            ->where('is_active', true)
            ->where('code', 'like', '1.1.01.%')
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    private function generateReference(\Carbon\Carbon $date): string
    {
        $prefix = 'RB-' . $date->format('Ym') . '-';

        $last = JournalEntry::withTrashed()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function allowedOrgs()
    {
        $orgIds = auth()->user()->organizationIds();
        return Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds));
    }
}
