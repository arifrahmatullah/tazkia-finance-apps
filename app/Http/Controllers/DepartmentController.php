<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $search = $request->input('search');
        $filterOrg    = $request->input('organization_id');
        $filterStatus = $request->input('status');

        $departments = Department::with('organization')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($search, fn($q) => $q->where(fn($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
            ))
            ->when($filterOrg, fn($q) => $q->where('organization_id', $filterOrg))
            ->when($filterStatus !== null && $filterStatus !== '', fn($q) => $q->where('is_active', $filterStatus))
            ->orderBy('organization_id')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        return view('departments.index', compact('departments', 'organizations'));
    }

    private function allowedOrgs(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        $query  = Organization::where('is_active', true);
        return $orgIds !== null ? $query->whereIn('id', $orgIds) : $query;
    }

    public function create()
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('departments.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'code'            => 'required|string|max:50',
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string|max:255',
            'has_budget'      => 'boolean',
            'budget_blocking' => 'boolean',
        ]);

        $validated['has_budget']      = $request->boolean('has_budget');
        $validated['budget_blocking'] = $request->boolean('budget_blocking');
        $validated['is_active']       = true;

        $exists = Department::where('organization_id', $validated['organization_id'])
            ->where('code', strtoupper($validated['code']))
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'Kode departemen sudah dipakai di organisasi ini.']);
        }

        $validated['code'] = strtoupper($validated['code']);
        Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        abort_unless(auth()->user()->canAccessOrganization($department->organization_id), 403);

        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('departments.edit', compact('department', 'organizations'));
    }

    public function update(Request $request, Department $department)
    {
        abort_unless(auth()->user()->canAccessOrganization($department->organization_id), 403);
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'code'            => 'required|string|max:50',
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string|max:255',
            'has_budget'      => 'boolean',
            'budget_blocking' => 'boolean',
            'is_active'       => 'boolean',
        ]);

        $validated['has_budget']      = $request->boolean('has_budget');
        $validated['budget_blocking'] = $request->boolean('budget_blocking');
        $validated['is_active']       = $request->boolean('is_active');

        $exists = Department::where('organization_id', $validated['organization_id'])
            ->where('code', strtoupper($validated['code']))
            ->where('id', '!=', $department->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'Kode departemen sudah dipakai di organisasi ini.']);
        }

        $validated['code'] = strtoupper($validated['code']);
        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        abort_unless(auth()->user()->canAccessOrganization($department->organization_id), 403);

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }
}
