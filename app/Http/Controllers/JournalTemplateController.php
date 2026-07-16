<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateDetail;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JournalTemplateController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $templates = JournalTemplate::with(['organization', 'details.account'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($request->filled('organization_id'), function ($q) use ($request, $user) {
                abort_unless($user->canAccessOrganization($request->organization_id), 403);
                $q->where('organization_id', $request->organization_id);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('code', 'like', $s)
                    ->orWhere('name', 'like', $s)
                    ->orWhere('category', 'like', $s));
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('journal-templates.index', compact('templates', 'organizations'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $selectedOrgId = $request->input('organization_id') ?: $organizations->first()?->id;
        $accounts = collect();
        if ($selectedOrgId) {
            abort_unless($user->canAccessOrganization($selectedOrgId), 403);
            $accounts = $this->accountsFor($selectedOrgId);
        }

        $categories = $this->existingCategories($orgIds);

        return view('journal-templates.create', compact('organizations', 'accounts', 'selectedOrgId', 'categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $this->validateTemplate($request);

        abort_unless($user->canAccessOrganization($request->organization_id), 403);
        $this->assertAccountsBelongToOrg($validated['details'], $request->organization_id);

        DB::transaction(function () use ($request, $validated) {
            $template = JournalTemplate::create([
                'organization_id' => $request->organization_id,
                'code'            => $validated['code'],
                'name'            => $validated['name'],
                'category'        => $validated['category'] ?? null,
                'is_active'       => true,
            ]);

            $this->saveDetails($template, $validated['details']);
        });

        return redirect()->route('journal-templates.index')
            ->with('success', 'Template jurnal ' . $validated['code'] . ' berhasil dibuat.');
    }

    public function edit(JournalTemplate $journalTemplate)
    {
        $user = auth()->user();
        abort_unless($user->canAccessOrganization($journalTemplate->organization_id), 403);

        $journalTemplate->load('details.account');
        $accounts   = $this->accountsFor($journalTemplate->organization_id);
        $categories = $this->existingCategories($user->organizationIds());

        return view('journal-templates.edit', compact('journalTemplate', 'accounts', 'categories'));
    }

    public function update(Request $request, JournalTemplate $journalTemplate)
    {
        $user = auth()->user();
        abort_unless($user->canAccessOrganization($journalTemplate->organization_id), 403);

        $validated = $this->validateTemplate($request, $journalTemplate);
        $this->assertAccountsBelongToOrg($validated['details'], $journalTemplate->organization_id);

        DB::transaction(function () use ($request, $journalTemplate, $validated) {
            $journalTemplate->update([
                'code'      => $validated['code'],
                'name'      => $validated['name'],
                'category'  => $validated['category'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $journalTemplate->details()->delete();
            $this->saveDetails($journalTemplate, $validated['details']);
        });

        return redirect()->route('journal-templates.index')
            ->with('success', 'Template jurnal ' . $validated['code'] . ' berhasil diperbarui.');
    }

    public function destroy(JournalTemplate $journalTemplate)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalTemplate->organization_id), 403);

        $journalTemplate->delete();

        return redirect()->route('journal-templates.index')
            ->with('success', 'Template jurnal berhasil dihapus.');
    }

    // Daftar template aktif per organisasi (untuk dropdown di form jurnal)
    public function options(Request $request)
    {
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);

        $templates = JournalTemplate::where('organization_id', $request->organization_id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'category']);

        return response()->json($templates);
    }

    // Baris template (untuk mengisi baris jurnal otomatis)
    public function lines(JournalTemplate $journalTemplate)
    {
        abort_unless(auth()->user()->canAccessOrganization($journalTemplate->organization_id), 403);

        $lines = $journalTemplate->details()->with('account:id,code,name')->get()
            ->map(fn($d) => [
                'account_id'   => $d->account_id,
                'balance_type' => $d->balance_type,
                'description'  => $d->description,
            ]);

        return response()->json([
            'name'  => $journalTemplate->name,
            'lines' => $lines,
        ]);
    }

    private function validateTemplate(Request $request, ?JournalTemplate $existing = null): array
    {
        $orgId = $existing?->organization_id ?? $request->organization_id;

        $validated = $request->validate([
            'organization_id' => $existing ? 'nullable' : 'required|exists:organizations,id',
            'code'            => ['required', 'string', 'max:50',
                Rule::unique('journal_templates', 'code')
                    ->where('organization_id', $orgId)
                    ->ignore($existing?->id),
            ],
            'name'     => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'details'  => 'required|array|min:2',
            'details.*.account_id'   => 'required|exists:accounts,id',
            'details.*.balance_type' => 'required|in:debit,credit',
            'details.*.description'  => 'nullable|string|max:255',
        ], [
            'code.required'    => 'Kode template wajib diisi.',
            'code.unique'      => 'Kode template sudah digunakan pada organisasi ini.',
            'name.required'    => 'Nama template wajib diisi.',
            'details.required' => 'Template harus memiliki baris jurnal.',
            'details.min'      => 'Template minimal memiliki 2 baris (debit dan kredit).',
        ]);

        $hasDebit  = collect($validated['details'])->contains(fn($d) => $d['balance_type'] === 'debit');
        $hasCredit = collect($validated['details'])->contains(fn($d) => $d['balance_type'] === 'credit');
        if (!$hasDebit || !$hasCredit) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'details' => 'Template harus memiliki minimal satu baris debit dan satu baris kredit.',
            ]);
        }

        return $validated;
    }

    private function saveDetails(JournalTemplate $template, array $details): void
    {
        foreach (array_values($details) as $i => $detail) {
            JournalTemplateDetail::create([
                'journal_template_id' => $template->id,
                'account_id'          => $detail['account_id'],
                'balance_type'        => $detail['balance_type'],
                'description'         => $detail['description'] ?? null,
                'sequence'            => $i,
            ]);
        }
    }

    private function assertAccountsBelongToOrg(array $details, string $orgId): void
    {
        foreach ($details as $detail) {
            $account = Account::find($detail['account_id']);
            abort_unless($account && $account->organization_id === $orgId, 422, 'Akun tidak valid untuk organisasi ini.');
            abort_if($account->is_header, 422, 'Akun induk tidak dapat digunakan dalam template jurnal.');
        }
    }

    private function accountsFor(string $orgId)
    {
        return Account::where('organization_id', $orgId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();
    }

    private function existingCategories($orgIds)
    {
        return JournalTemplate::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }
}
