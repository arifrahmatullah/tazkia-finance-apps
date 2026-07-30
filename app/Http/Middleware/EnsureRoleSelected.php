<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->hasMultipleRoles() && !session('active_role_id')) {
            return redirect()->route('role-select.show');
        }

        return $next($request);
    }
}
