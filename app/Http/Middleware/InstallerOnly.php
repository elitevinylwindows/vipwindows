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

        if (!$user->isInstaller() && !$user->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
