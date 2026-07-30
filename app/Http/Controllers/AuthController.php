<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('active_role_id');
            AuditLog::record('login', auth()->user());

            $roles = auth()->user()->availableRoles();
            if ($roles->count() > 1) {
                return redirect()->route('role-select.show');
            }
            if ($roles->count() === 1) {
                $request->session()->put('active_role_id', $roles->first()->id);
            }

            return redirect()->intended(route('dashboard'));
        }

        AuditLog::record('login_failed', null, ['attempted_email' => $credentials['email']]);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        AuditLog::record('logout', auth()->user());
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
