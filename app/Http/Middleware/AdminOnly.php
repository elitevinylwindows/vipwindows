<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard('vip')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        // Redirect non-admins to their proper dashboard
        if ($user->isInstaller()) {
            return redirect()->route('installer.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }
}
