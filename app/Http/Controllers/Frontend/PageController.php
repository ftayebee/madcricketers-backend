<?php

namespace App\Http\Controllers\Frontend;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function index(){
        try{
            session([
                'title' => 'Home Page',
                'breadcrumbs' => [
                    [
                        'title' => 'Home',
                        'url' => route('frontend.home')
                    ]
                ]
            ]);

            return view('frontend.index');
        } catch(Exception $e){
            Log::error('Error Loading Frontend Index: ', [
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'file' => $e->getFile()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Frontend Index',
            ]);
        }
    }
}
