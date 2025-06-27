<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        try {
            return User::create([
                'full_name'    => $data['full_name'],
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'visible_pass' => $data['password'],
                'role_id'      => 1,
            ]);
        } catch (Exception $e) {
            Log::error('User Registration Failed: ' . $e->getMessage());
            abort(500, 'Registration failed. Please try again later.');
        }
    }
}
