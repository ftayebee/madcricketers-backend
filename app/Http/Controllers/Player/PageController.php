<?php

namespace App\Http\Controllers\Player;

use Exception;
use App\Models\Player;
use App\Models\Payment;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard()
    {
        try {
            session([
                'title' => 'Player Dashboard',
                'breadcrumbs' => [
                    [
                        'title' => 'Dashboard',
                        'url' => route('player.dashboard')
                    ]
                ]
            ]);

            $user = auth()->user();
            $player = $user->player;

            if (!$player) {
                return redirect()->back()->with('error', 'No player profile found.');
            }

            $currentMonth = now()->month;

            // Fetch statistics safely
            $statistics = $player->statistics ?? collect();

            // Initialize empty stats
            $stats = [
                'matches'        => 0,
                'innings_batted'  => 0,
                'total_runs'     => 0,
                'balls_faced'    => 0,
                'fifties'        => 0,
                'hundreds'       => 0,
                'fours'          => 0,
                'sixes'          => 0,
                'strike_rate'    => 0,
                'average'        => 0,
                'innings_bowled' => 0,
                'overs_bowled'   => 0,
                'runs_conceded'  => 0,
                'wickets'        => 0,
                'bowling_average' => 0,
                'economy_rate'   => 0,
                'catches'        => 0,
                'runouts'        => 0,
                'stumpings'      => 0,
            ];

            if ($statistics->isNotEmpty()) {
                $stats['matches']        = $statistics->count();
                $stats['innings_batted'] = $statistics->sum('innings_batted');
                $stats['total_runs']     = $statistics->sum('total_runs');
                $stats['balls_faced']    = $statistics->sum('balls_faced');
                $stats['fifties']        = $statistics->sum('fifties');
                $stats['hundreds']       = $statistics->sum('hundreds');
                $stats['fours']          = $statistics->sum('fours');
                $stats['sixes']          = $statistics->sum('sixes');
                $stats['innings_bowled'] = $statistics->sum('innings_bowled');
                $stats['overs_bowled']   = $statistics->sum('overs_bowled');
                $stats['runs_conceded']  = $statistics->sum('runs_conceded');
                $stats['wickets']        = $statistics->sum('wickets');
                $stats['catches']        = $statistics->sum('catches');
                $stats['runouts']        = $statistics->sum('runouts');
                $stats['stumpings']      = $statistics->sum('stumpings');

                // Derived stats
                $stats['strike_rate'] = $stats['balls_faced'] > 0
                    ? round(($stats['total_runs'] / $stats['balls_faced']) * 100, 2)
                    : 0;

                $stats['average'] = $stats['innings_batted'] > 0
                    ? round($stats['total_runs'] / $stats['innings_batted'], 2)
                    : 0;

                $stats['bowling_average'] = $stats['wickets'] > 0
                    ? round($stats['runs_conceded'] / $stats['wickets'], 2)
                    : 0;

                $stats['economy_rate'] = $stats['overs_bowled'] > 0
                    ? round($stats['runs_conceded'] / $stats['overs_bowled'], 2)
                    : 0;
            }

            // Notifications (latest 5)
            $notifications = collect();
            $recentMatches = collect();
            $upcomingMatches = collect();
            $paymentSummary = collect();

            // Recent Matches (last 5 completed)
            if ($player->matches()->count() > 0) {
                $recentMatches = $player->matches()
                    ->where('status', 'completed')
                    ->latest('match_date')
                    ->take(5)
                    ->get() ?? collect();

                $upcomingMatches = $player->matches()
                    ->where('status', 'upcoming')
                    ->orderBy('match_date', 'asc')
                    ->take(5)
                    ->get() ?? collect();
            }

            $payments = $player->payments()->whereMonth('payment_date', $currentMonth)->get() ?? collect();
            $paymentSummary = [
                'donation'   => $payments->where('type', 'donation')->sum('amount'),
                'tournament' => $payments->where('type', 'tournament')->sum('amount'),
                'jersey'     => $payments->where('type', 'jersey')->sum('amount'),
                'other'      => $payments->where('type', 'other')->sum('amount'),
                'total'      => $payments->sum('amount'),
            ];

            return view('player.dashboard', compact(
                'player',
                'stats',
                'recentMatches',
                'upcomingMatches',
                'paymentSummary',
                'notifications'
            ));
        } catch (\Exception $e) {
            Log::error('Error Loading Player Dashboard: ', [
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'file' => $e->getFile()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading Player Dashboard',
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
