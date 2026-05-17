<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): mixed
    {
        $guards = empty($guards) ? ['vip'] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                return match ($user->role) {
                    'admin', 'technician' => redirect()->route('admin.dashboard'),
                    'installer'           => redirect()->route('installer.dashboard'),
                    'customer'            => redirect()->route('customer.dashboard'),
                    default               => redirect('/'),
                };
            }
        }

        return $next($request);
    }
}
