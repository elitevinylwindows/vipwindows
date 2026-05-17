<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class InstallerOnly
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('vip')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('vip')->user();

        // Admins can access installer area
        if ($user->isInstaller() || $user->isAdmin()) {
            return $next($request);
        }

        // Redirect customers to their own dashboard
        if ($user->role === 'customer') {
            return redirect()->route('customer.dashboard');
        }

        abort(403);
    }
}
