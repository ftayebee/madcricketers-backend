<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class PageController extends Controller
{
    public function dashboard(){
        try{
            session([
                'title' => 'Dashboard',
                'breadcrumbs' => [
                    [
                        'title' => 'Dashboard',
                        'url' => route('admin.dashboard')
                    ]
                ]
            ]);

            return view('admin.dashboard');
        } catch(Exception $e){
            Log::error('Error Loading Admin Dashboard: ', [
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'file' => $e->getFile()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Admin Dashboard',
            ]);
        }
    }

    public function profile(){
        try{
            session([
                'title' => 'Profile',
                'breadcrumbs' => [
                    [
                        'title' => 'Profile',
                        'url' => route('admin.profile')
                    ]
                ]
            ]);
            $user = auth()->user();
            $roles = Role::all();

            return view('admin.pages.profile', compact('user', 'roles'));
        } catch(Exception $e){
            Log::error('Error Loading Admin Profile: ', [
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'file' => $e->getFile()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Admin Profile',
            ]);
        }
    }
}
