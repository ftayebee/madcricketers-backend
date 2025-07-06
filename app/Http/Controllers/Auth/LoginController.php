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

    protected $redirectTo;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

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
