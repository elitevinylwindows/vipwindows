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

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
