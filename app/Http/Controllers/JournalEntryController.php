<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = $this->allowedOrgs($user)->orderBy('name')->get();

        $query = JournalEntry::with(['organization', 'creator', 'lines'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds));

        if ($request->filled('organization_id')) {
            abort_unless($user->canAccessOrganization((int) $request->organization_id), 403);
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('reference', 'like', $s)->orWhere('description', 'like', $s));
        }

        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('journal-entries.index', compact('entries', 'organizations'));
    }

    public function create(Request $request)
    {
        $user  = auth()->user();
        $organizations = $this->allowedOrgs($user)->orderBy('name')->get();

        $selectedOrgId = $request->integer('organization_id') ?: $organizations->first()?->id;
        $accounts = collect();
        if ($selectedOrgId) {
            abort_unless($user->canAccessOrganization($selectedOrgId), 403);
            $accounts = Account::where('organization_id', $selectedOrgId)
                ->where('is_active', true)
                ->where('is_header', false)
                ->orderBy('code')
                ->get();
        }

        return view('journal-entries.create', compact('organizations', 'accounts', 'selectedOrgId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'organization_id' => 'required|integer|exists:organizations,id',
            'entry_date'      => 'required|date',
            'description'     => 'nullable|string|max:500',
            'lines'           => 'required|array|min:2',
            'lines.*.account_id'   => 'required|integer|exists:accounts,id',
            'lines.*.description'  => 'nullable|string|max:255',
            'lines.*.debit'        => 'required|numeric|min:0',
            'lines.*.credit'       => 'required|numeric|min:0',
        ]);

        abort_unless($user->canAccessOrganization((int) $request->organization_id), 403);

        $lines = $request->input('lines');
        $this->validateLines($lines, (int) $request->organization_id);

        DB::transaction(function () use ($request, $user, $lines) {
            $reference = JournalEntry::generateReference(
                (int) $request->organization_id,
                $request->entry_date
            );

            $entry = JournalEntry::create([
                'organization_id' => $request->organization_id,
                'entry_date'      => $request->entry_date,
                'reference'       => $reference,
                'description'     => $request->description,
                'status'          => 'draft',
                'created_by'      => $user->id,
            ]);

            foreach ($lines as $i => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'description'      => $line['description'] ?? null,
                    'debit'            => (float) $line['debit'],
                    'credit'           => (float) $line['credit'],
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()->route('journal-entries.index', ['organization_id' => $request->organization_id])
            ->with('success', 'Jurnal berhasil disimpan sebagai draft.');
    }

    public function show(JournalEntry $journalEntry)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalEntry->organization_id), 403);
        $journalEntry->load(['organization', 'creator', 'poster', 'lines.account']);
        return view('journal-entries.show', compact('journalEntry'));
    }

    public function edit(JournalEntry $journalEntry)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalEntry->organization_id), 403);
        abort_unless($journalEntry->isDraft(), 403, 'Jurnal yang sudah diposting tidak dapat diedit.');

        $user = auth()->user();
        $organizations = $this->allowedOrgs($user)->orderBy('name')->get();
        $journalEntry->load(['lines.account']);

        $accounts = Account::where('organization_id', $journalEntry->organization_id)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('journal-entries.edit', compact('journalEntry', 'organizations', 'accounts'));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalEntry->organization_id), 403);
        abort_unless($journalEntry->isDraft(), 403, 'Jurnal yang sudah diposting tidak dapat diedit.');

        $request->validate([
            'entry_date'  => 'required|date',
            'description' => 'nullable|string|max:500',
            'lines'       => 'required|array|min:2',
            'lines.*.account_id'  => 'required|integer|exists:accounts,id',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.debit'       => 'required|numeric|min:0',
            'lines.*.credit'      => 'required|numeric|min:0',
        ]);

        $lines = $request->input('lines');
        $this->validateLines($lines, $journalEntry->organization_id);

        DB::transaction(function () use ($request, $journalEntry, $lines) {
            $journalEntry->update([
                'entry_date'  => $request->entry_date,
                'description' => $request->description,
            ]);

            $journalEntry->lines()->delete();

            foreach ($lines as $i => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $line['account_id'],
                    'description'      => $line['description'] ?? null,
                    'debit'            => (float) $line['debit'],
                    'credit'           => (float) $line['credit'],
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()->route('journal-entries.show', $journalEntry)
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(JournalEntry $journalEntry)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalEntry->organization_id), 403);
        abort_unless($journalEntry->isDraft(), 403, 'Jurnal yang sudah diposting tidak dapat dihapus.');

        $journalEntry->delete();

        return redirect()->route('journal-entries.index', ['organization_id' => $journalEntry->organization_id])
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    public function post(JournalEntry $journalEntry)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalEntry->organization_id), 403);
        abort_unless($journalEntry->isDraft(), 403, 'Jurnal sudah diposting.');

        $journalEntry->update([
            'status'    => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);

        return redirect()->route('journal-entries.show', $journalEntry)
            ->with('success', 'Jurnal berhasil diposting.');
    }

    public function getAccounts(Request $request)
    {
        $orgId = (int) $request->organization_id;
        abort_unless(auth()->user()->canAccessOrganization($orgId), 403);

        $accounts = Account::where('organization_id', $orgId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'account_type', 'normal_balance']);

        return response()->json($accounts);
    }

    private function validateLines(array $lines, int $orgId): void
    {
        $totalDebit  = collect($lines)->sum(fn($l) => (float) $l['debit']);
        $totalCredit = collect($lines)->sum(fn($l) => (float) $l['credit']);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            back()->withInput();
            abort(422, 'Total debit harus sama dengan total kredit.');
        }

        if ($totalDebit <= 0) {
            abort(422, 'Jurnal harus memiliki minimal satu baris debit dan satu baris kredit.');
        }

        foreach ($lines as $line) {
            $account = Account::find($line['account_id']);
            abort_unless($account && $account->organization_id === $orgId, 422, 'Akun tidak valid untuk organisasi ini.');
            abort_if($account->is_header, 422, 'Akun induk tidak dapat digunakan dalam jurnal.');
        }
    }

    private function allowedOrgs($user)
    {
        $orgIds = $user->organizationIds();
        return Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds));
    }
}
