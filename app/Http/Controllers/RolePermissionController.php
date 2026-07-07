<?php

namespace App\Http\Controllers;

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

        $permissionIds = Permission::whereIn('slug', $request->input('permissions', []))->pluck('id');
        $role->permissions()->sync($permissionIds);

        return redirect()->route('role-permissions.index')
            ->with('success', "Permission untuk role \"{$role->name}\" berhasil disimpan.");
    }
}
