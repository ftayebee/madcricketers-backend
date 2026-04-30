<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            return $next($request);
        }

        if ($user->getAllPermissions()->isNotEmpty()) {
            return $next($request);
        }

        abort(403, 'Unauthorized admin access.');
    }
}
