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
        if (Auth::user()->role->slug == 'super-admin' || Auth::user()->role->slug == 'admin') {
            return RouteServiceProvider::ADMIN_DASHBOARD;
        } else if(Auth::user()->role->slug == 'manager') {
            return RouteServiceProvider::MANAGER_DASHBOARD;
        }

        return RouteServiceProvider::PLAYER_DASHBOARD;
    }
}
