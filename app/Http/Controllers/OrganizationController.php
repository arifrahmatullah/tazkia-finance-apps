<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $orgIds = auth()->user()->organizationIds();

        $organizations = Organization::with('parent')
            ->when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderByRaw("FIELD(type, 'yayasan', 'kampus', 'unit')")
            ->orderBy('name')
            ->get();

        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $parents = Organization::where('type', 'yayasan')->orWhere('type', 'kampus')->orderBy('name')->get();
        return view('organizations.create', compact('parents'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:50|unique:organizations,code',
            'type'      => 'required|in:yayasan,kampus,unit',
            'parent_id' => 'nullable|exists:organizations,id',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
        ]);

        $validated['is_active'] = true;

        Organization::create($validated);

        return redirect()->route('organizations.index')
            ->with('success', 'Organisasi berhasil ditambahkan.');
    }

    public function edit(Organization $organization)
    {
        abort_unless(auth()->user()->canAccessOrganization($organization->id), 403);

        $parents = Organization::where('id', '!=', $organization->id)
            ->whereIn('type', ['yayasan', 'kampus'])
            ->orderBy('name')->get();

        return view('organizations.edit', compact('organization', 'parents'));
    }

    public function update(Request $request, Organization $organization)
    {
        abort_unless(auth()->user()->canAccessOrganization($organization->id), 403);

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:50|unique:organizations,code,' . $organization->id,
            'type'      => 'required|in:yayasan,kampus,unit',
            'parent_id' => 'nullable|exists:organizations,id',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $organization->update($validated);

        return redirect()->route('organizations.index')
            ->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function destroy(Organization $organization)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $organization->delete();

        return redirect()->route('organizations.index')
            ->with('success', 'Organisasi berhasil dihapus.');
    }
}
