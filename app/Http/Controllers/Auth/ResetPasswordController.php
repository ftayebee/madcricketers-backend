<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo;

    protected function redirectTo()
    {
        $user = Auth::guard($guard)->user();
        if ($user->hasRole('admin')) {
            return RouteServiceProvider::ADMIN_DASHBOARD;
        } elseif ($user->hasRole('manager')) {
            return RouteServiceProvider::MANAGER_DASHBOARD;
        }

        return RouteServiceProvider::PLAYER_DASHBOARD;
    }
}
