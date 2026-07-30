<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class RoleSelectionController extends Controller
{
    public function show()
    {
        $roles = auth()->user()->availableRoles();

        if ($roles->count() <= 1) {
            if ($roles->count() === 1) {
                session(['active_role_id' => $roles->first()->id]);
            }
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.role-select', compact('roles'));
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $roles = auth()->user()->availableRoles();
        abort_unless($roles->contains('id', $validated['role_id']), 403);

        session(['active_role_id' => $validated['role_id']]);
        AuditLog::record('switch_role', auth()->user(), ['role_id' => $validated['role_id']]);

        return redirect()->intended(route('dashboard'));
    }
}
