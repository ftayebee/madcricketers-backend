<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class PageController extends Controller
{
    public function dashboard()
    {
        try {
            if(!Auth::check()){
                return redirect()->route('login');
            }

            session([
                'title' => 'Dashboard',
                'breadcrumbs' => [
                    [
                        'title' => 'Dashboard',
                        'url' => route('admin.dashboard')
                    ]
                ]
            ]);

            $currentMonth = now()->month;

            // Get current running tournament
            $currentTournament = \App\Models\Tournament::where('status', 'ongoing')->with(['groups.teams.players'])->first();

            // Payments summary for current month
            $payments = \App\Models\Payment::whereMonth('payment_date', $currentMonth)->get();
            $paymentSummary = [
                'donations' => $payments->where('type', 'donation')->sum('amount'),
                'tournament' => $payments->where('type', 'tournament')->sum('amount'),
                'jersey' => $payments->where('type', 'jersey')->sum('amount'),
                'other' => $payments->where('type', 'other')->sum('amount'),
                'total' => $payments->sum('amount'),
            ];

            // Players who haven’t paid this month
            $playersNotPaid = \App\Models\Player::whereDoesntHave('payments', function ($q) use ($currentMonth) {
                $q->whereMonth('payment_date', $currentMonth)->where('status', 'paid');
            })->get();

            return view('admin.dashboard', compact('currentTournament', 'paymentSummary', 'playersNotPaid'));
        } catch (Exception $e) {
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

    public function profile()
    {
        try {
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
        } catch (Exception $e) {
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
