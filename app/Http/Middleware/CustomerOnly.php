<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CustomerOnly
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('vip')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('vip')->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isInstaller()) {
            return redirect()->route('installer.dashboard');
        }

        return $next($request);
    }
}
