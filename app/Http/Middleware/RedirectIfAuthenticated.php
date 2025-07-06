<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                if ($user->hasRole('admin')) {
                    return redirect(RouteServiceProvider::ADMIN_DASHBOARD);
                } elseif ($user->hasRole('manager')) {
                    return redirect(RouteServiceProvider::MANAGER_DASHBOARD);
                }

                return redirect(RouteServiceProvider::PLAYER_DASHBOARD);
            }
        }

        return $next($request);
    }
}
