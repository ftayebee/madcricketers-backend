<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function redirectTo()
    {
        $user = Auth::user();
        
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return RouteServiceProvider::ADMIN_DASHBOARD;
        } elseif ($user->hasRole('manager')) {
            return RouteServiceProvider::MANAGER_DASHBOARD;
        } elseif ($user->hasRole('player')) {
            return RouteServiceProvider::PLAYER_DASHBOARD;
        }

        // fallback
        return RouteServiceProvider::HOME;
    }
}
