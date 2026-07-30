<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganizationRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $orgIds       = auth()->user()->organizationIds();
        $search       = $request->input('search');
        $filterRole   = $request->input('role_id');
        $filterOrg    = $request->input('organization_id');
        $filterStatus = $request->input('status');

        $users = User::with(['role', 'organizationRoles.organization', 'organizationRoles.role'])
            ->when(!$isSuperAdmin, fn($q) => $q->whereHas('organizationRoles',
                fn($r) => $r->whereIn('organization_id', $orgIds)
            ))
            ->when($search, fn($q) => $q->where(fn($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($filterRole, fn($q) => $q->where(fn($q2) => $q2
                ->where('role_id', $filterRole)
                ->orWhereHas('organizationRoles', fn($r) => $r->where('role_id', $filterRole))
            ))
            ->when($filterOrg, fn($q) => $q->whereHas('organizationRoles',
                fn($r) => $r->where('organization_id', $filterOrg)
            ))
            ->when($filterStatus !== null && $filterStatus !== '',
                fn($q) => $q->where('is_active', $filterStatus)
            )
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles         = Role::orderBy('name')->get();
        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'organizations'));
    }

    private function allowedOrgs(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        $query  = Organization::where('is_active', true);
        return $orgIds !== null ? $query->whereIn('id', $orgIds) : $query;
    }

    public function create()
    {
        $roles         = Role::orderBy('name')->get();
        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('users.create', compact('roles', 'organizations'));
    }

    public function store(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email'               => 'required|email|max:150|unique:users,email',
            'password'            => 'required|string|min:8|confirmed',
            'role_ids'            => 'required|array|min:1',
            'role_ids.*'          => 'exists:roles,id',
            'organization_ids'    => 'nullable|array',
            'organization_ids.*'  => 'exists:organizations,id',
        ]);

        $roles = Role::whereIn('id', $validated['role_ids'])->get();

        // Hanya superadmin yang bisa buat user superadmin
        if ($roles->contains('slug', 'superadmin')) {
            abort_unless($isSuperAdmin, 403);
        }

        // Pastikan org yang dipilih boleh diakses
        $orgIds = $request->input('organization_ids', []);
        if (!$isSuperAdmin) {
            $allowed = auth()->user()->organizationIds();
            foreach ($orgIds as $orgId) {
                abort_unless(in_array($orgId, $allowed), 403);
            }
        }

        $nonSuperadminRoles = $roles->reject(fn($r) => $r->slug === 'superadmin');
        if ($nonSuperadminRoles->isNotEmpty() && empty($orgIds)) {
            return back()->withInput()->withErrors([
                'organization_ids' => 'Pilih minimal satu organisasi untuk role yang dipilih.',
            ]);
        }

        $primaryRole = $roles->firstWhere('slug', 'superadmin') ?? $roles->first();

        $user = \DB::transaction(function () use ($validated, $roles, $nonSuperadminRoles, $orgIds, $primaryRole) {
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role_id'   => $primaryRole->id,
                'is_active' => true,
            ]);

            foreach ($nonSuperadminRoles as $role) {
                foreach ($orgIds as $orgId) {
                    UserOrganizationRole::create([
                        'user_id'         => $user->id,
                        'organization_id' => $orgId,
                        'role_id'         => $role->id,
                    ]);
                }
            }

            return $user;
        });

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        $this->authorizeUserAccess($user);

        $roles         = Role::orderBy('name')->get();
        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        $assignedOrgIds = $user->organizationRoles()->pluck('organization_id')->toArray();
        $assignedRoleIds = $user->availableRoles()->pluck('id')->toArray();

        // Akun lama kadang cuma punya role_id tanpa baris user_organization_roles.
        // Supaya checkbox organisasi tidak kosong (dan gampang lupa dicentang), fallback ke organisasi karyawannya.
        if (empty($assignedOrgIds)) {
            $user->loadMissing('employee');
            if ($user->employee?->organization_id) {
                $assignedOrgIds = [$user->employee->organization_id];
            }
        }

        return view('users.edit', compact('user', 'roles', 'organizations', 'assignedOrgIds', 'assignedRoleIds'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserAccess($user);

        $isSuperAdmin = auth()->user()->isSuperAdmin();

        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email'               => 'required|email|max:150|unique:users,email,' . $user->id,
            'password'            => 'nullable|string|min:8|confirmed',
            'role_ids'            => 'required|array|min:1',
            'role_ids.*'          => 'exists:roles,id',
            'organization_ids'    => 'nullable|array',
            'organization_ids.*'  => 'exists:organizations,id',
            'is_active'           => 'boolean',
        ]);

        $roles = Role::whereIn('id', $validated['role_ids'])->get();
        if ($roles->contains('slug', 'superadmin')) {
            abort_unless($isSuperAdmin, 403);
        }

        $orgIds = $request->input('organization_ids', []);
        if (!$isSuperAdmin) {
            $allowed = auth()->user()->organizationIds();
            foreach ($orgIds as $orgId) {
                abort_unless(in_array($orgId, $allowed), 403);
            }
        }

        $nonSuperadminRoles = $roles->reject(fn($r) => $r->slug === 'superadmin');
        if ($nonSuperadminRoles->isNotEmpty() && empty($orgIds)) {
            return back()->withInput()->withErrors([
                'organization_ids' => 'Pilih minimal satu organisasi untuk role yang dipilih.',
            ]);
        }

        $primaryRole = $roles->firstWhere('slug', 'superadmin') ?? $roles->first();

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role_id'   => $primaryRole->id,
            'is_active' => $request->boolean('is_active'),
        ];
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        \DB::transaction(function () use ($user, $data, $nonSuperadminRoles, $orgIds) {
            $user->update($data);

            $user->organizationRoles()->delete();
            foreach ($nonSuperadminRoles as $role) {
                foreach ($orgIds as $orgId) {
                    UserOrganizationRole::create([
                        'user_id'         => $user->id,
                        'organization_id' => $orgId,
                        'role_id'         => $role->id,
                    ]);
                }
            }
        });

        return redirect()->route('users.index')
            ->with('success', "Data user {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        $this->authorizeUserAccess($user);
        abort_if($user->id === auth()->id(), 403);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} berhasil dihapus.");
    }

    private function authorizeUserAccess(User $user): void
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        if ($isSuperAdmin) return;

        // Non-superadmin tidak bisa edit user superadmin
        abort_if($user->role?->slug === 'superadmin', 403);

        // Harus punya org yang sama
        $myOrgIds   = auth()->user()->organizationIds();
        $userOrgIds = $user->organizationRoles()->pluck('organization_id')->toArray();
        $overlap    = array_intersect($myOrgIds ?? [], $userOrgIds);
        abort_if(empty($overlap), 403);
    }
}
