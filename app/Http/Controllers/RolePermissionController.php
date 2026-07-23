<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles       = Role::with('permissions')->whereNot('slug', 'superadmin')->orderBy('name')->get();
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        return view('role-permissions.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->slug === 'superadmin', 403);

        $oldSlugs = $role->permissions()->pluck('slug')->sort()->values()->toArray();

        $permissionIds = Permission::whereIn('slug', $request->input('permissions', []))->pluck('id');
        $role->permissions()->sync($permissionIds);

        $newSlugs = $role->permissions()->pluck('slug')->sort()->values()->toArray();

        // sync() pada pivot table tidak memicu event model, jadi dicatat manual
        if ($oldSlugs !== $newSlugs) {
            AuditLog::create([
                'user_id'        => auth()->id(),
                'user_name'      => auth()->user()->name,
                'action'         => 'updated',
                'auditable_type' => Role::class,
                'auditable_id'   => $role->id,
                'old_values'     => ['permissions' => $oldSlugs],
                'new_values'     => ['permissions' => $newSlugs],
                'ip_address'     => $request->ip(),
                'url'            => $request->fullUrl(),
                'created_at'     => now(),
            ]);
        }

        return redirect()->route('role-permissions.index')
            ->with('success', "Permission untuk role \"{$role->name}\" berhasil disimpan.");
    }
}
