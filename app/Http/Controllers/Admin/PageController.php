<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}
