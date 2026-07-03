<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Organization;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $orgIds  = auth()->user()->organizationIds();
        $search  = $request->input('search');
        $filterType = $request->input('account_type');
        $filterOrg  = $request->input('organization_id');

        // Pilih organisasi (untuk superadmin bisa pilih org)
        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        // Default org jika tidak ada pilihan
        if (!$filterOrg && $organizations->count() === 1) {
            $filterOrg = $organizations->first()->id;
        }

        $accounts = Account::with(['parent', 'organization'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($filterOrg, fn($q) => $q->where('organization_id', $filterOrg))
            ->when($filterType, fn($q) => $q->where('account_type', $filterType))
            ->when($search, fn($q) => $q->where(fn($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
            ))
            ->orderBy('code')
            ->get();

        // Kelompokkan per tipe untuk tampilan tree (hanya jika tidak ada search/filter tipe)
        $grouped = $accounts->groupBy('account_type');

        return view('accounts.index', compact('accounts', 'grouped', 'organizations', 'filterOrg'));
    }

    private function allowedOrgs(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        return Organization::where('is_active', true)
            ->when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds));
    }

    public function create(Request $request)
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        $orgId         = $request->input('organization_id', $organizations->first()?->id);
        $selectedOrg   = $organizations->firstWhere('id', $orgId);

        $parents = collect();
        if ($selectedOrg) {
            $parents = Account::where('organization_id', $orgId)
                ->where('is_header', true)
                ->orderBy('code')->get();
        }

        return view('accounts.create', compact('organizations', 'selectedOrg', 'parents'));
    }

    public function store(Request $request)
    {
        $orgId = (int) $request->organization_id;
        abort_unless(auth()->user()->canAccessOrganization($orgId), 403);

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'parent_id'       => 'nullable|exists:accounts,id',
            'code'            => 'required|string|max:20',
            'name'            => 'required|string|max:150',
            'account_type'    => 'required|in:aset,kewajiban,ekuitas,pendapatan,beban',
            'normal_balance'  => 'required|in:debit,kredit',
            'description'     => 'nullable|string|max:500',
            'is_header'       => 'boolean',
        ]);

        $validated['code']      = strtoupper($validated['code']);
        $validated['is_header'] = $request->boolean('is_header');
        $validated['is_active'] = true;

        // Cek kode unik per organisasi
        $exists = Account::where('organization_id', $orgId)
            ->where('code', $validated['code'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'Kode akun sudah digunakan di organisasi ini.']);
        }

        // Validasi parent harus milik org yang sama
        if (!empty($validated['parent_id'])) {
            $parent = Account::findOrFail($validated['parent_id']);
            abort_unless($parent->organization_id === $orgId, 403);
        }

        Account::create($validated);

        return redirect()->route('accounts.index', ['organization_id' => $orgId])
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(Account $account)
    {
        abort_unless(auth()->user()->canAccessOrganization($account->organization_id), 403);

        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        $parents       = Account::where('organization_id', $account->organization_id)
            ->where('is_header', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')->get();

        return view('accounts.edit', compact('account', 'organizations', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        abort_unless(auth()->user()->canAccessOrganization($account->organization_id), 403);

        $validated = $request->validate([
            'parent_id'      => 'nullable|exists:accounts,id',
            'code'           => 'required|string|max:20',
            'name'           => 'required|string|max:150',
            'account_type'   => 'required|in:aset,kewajiban,ekuitas,pendapatan,beban',
            'normal_balance' => 'required|in:debit,kredit',
            'description'    => 'nullable|string|max:500',
            'is_header'      => 'boolean',
            'is_active'      => 'boolean',
        ]);

        $validated['code']      = strtoupper($validated['code']);
        $validated['is_header'] = $request->boolean('is_header');
        $validated['is_active'] = $request->boolean('is_active');

        $exists = Account::where('organization_id', $account->organization_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $account->id)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'Kode akun sudah digunakan di organisasi ini.']);
        }

        $account->update($validated);

        return redirect()->route('accounts.index', ['organization_id' => $account->organization_id])
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Account $account)
    {
        abort_unless(auth()->user()->canAccessOrganization($account->organization_id), 403);

        if ($account->children()->exists()) {
            return back()->withErrors(['delete' => 'Akun ini memiliki sub-akun. Hapus sub-akun terlebih dahulu.']);
        }

        $orgId = $account->organization_id;
        $account->delete();

        return redirect()->route('accounts.index', ['organization_id' => $orgId])
            ->with('success', 'Akun berhasil dihapus.');
    }

    public function getParents(Request $request)
    {
        $orgId = (int) $request->organization_id;
        abort_unless(auth()->user()->canAccessOrganization($orgId), 403);

        $parents = Account::where('organization_id', $orgId)
            ->where('is_header', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return response()->json($parents);
    }
}
