<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan Google gagal. Silakan coba lagi.',
            ]);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun dengan email ' . $googleUser->getEmail() . ' tidak terdaftar di sistem.',
            ]);
        }

        if (!$user->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        Auth::login($user, remember: true);
        AuditLog::record('login', $user, ['via' => 'google']);

        return redirect()->intended(route('dashboard'));
    }
}
