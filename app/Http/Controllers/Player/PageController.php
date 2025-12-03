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
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

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
            Log::info('Image: ' . $user->image);
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

            if ($statistics) {
                $stats['matches']        = $statistics->matches_played;
                $stats['innings_batted'] = $statistics->innings_batted;
                $stats['total_runs']     = $statistics->total_runs;
                $stats['balls_faced']    = $statistics->balls_faced;
                $stats['fifties']        = $statistics->fifties;
                $stats['hundreds']       = $statistics->hundreds;
                $stats['fours']          = $statistics->fours;
                $stats['sixes']          = $statistics->sixes;
                $stats['innings_bowled'] = $statistics->innings_bowled;
                $stats['overs_bowled']   = $statistics->overs_bowled;
                $stats['runs_conceded']  = $statistics->runs_conceded;
                $stats['wickets']        = $statistics->wickets;
                $stats['catches']        = $statistics->catches;
                $stats['runouts']        = $statistics->runouts;
                $stats['stumpings']      = $statistics->stumpings;

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

            if ($player->matches()->exists()) {
                $recentMatches = $player->matches()
                    ->where('cricket_matches.status', 'completed')
                    ->latest('match_date')
                    ->take(5)
                    ->get();

                $upcomingMatches = $player->matches()
                    ->where('cricket_matches.status', 'upcoming')
                    ->orderBy('match_date', 'asc')
                    ->take(5)
                    ->get();
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

            return view('player.pages.profile', compact('user', 'roles'));
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

    public function profileUpdate(Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'Unauthorized Access'
                ]);
            }

            $user = Auth::user();
            $player = $user->player;

            // -----------------------
            // Validate the input
            // -----------------------
            $validator = Validator::make($request->all(), [
                'general.full_name'       => 'required|string|max:255',
                'general.nickname'        => 'nullable|string|max:255',
                'general.email'          => 'required|email|unique:users,email,' . $user->id,
                'general.phone'          => 'nullable|string|max:20',
                'general.national_id'    => 'nullable|string|max:50',
                'general.blood_group'    => 'nullable|string',
                'general.religion'       => 'nullable|string',
                'general.gender'         => 'nullable|string',
                'general.date_of_birth'  => 'nullable|date',
                'general.address'        => 'nullable|string|max:500',
                'general.jursey_name'    => 'nullable|string|max:50',
                'general.jursey_number'  => 'nullable|string|max:10',
                'general.jursey_size'    => 'nullable|string|in:S,M,L,XL',
                'general.chest_measurement' => 'nullable|string|max:10',
                'general.batting_style'  => 'nullable|string|in:right-handed,left-handed,switch hitter',
                'general.bowling_style'  => 'nullable|string|in:fast,medium,spin,none',
                'profile_image'           => 'nullable|string',
                'password'                => 'nullable|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'Validation Failed.'
                ]);
            }

            // -----------------------
            // Update User general info
            // -----------------------
            $general = $request->input('general', []);
            $user->full_name = $general['full_name'] ?? $user->full_name;
            $user->nickname  = $general['nickname'] ?? $user->nickname;
            $user->email     = $general['email'] ?? $user->email;
            $user->phone     = $general['phone'] ?? $user->phone;
            $user->national_id = $general['national_id'] ?? $user->national_id;
            $user->blood_group = $general['blood_group'] ?? $user->blood_group;
            $user->religion  = $general['religion'] ?? $user->religion;
            $user->gender    = $general['gender'] ?? $user->gender;
            $user->date_of_birth = $general['date_of_birth'] ?? $user->date_of_birth;
            $user->address   = $general['address'] ?? $user->address;

            // -----------------------
            // Update Player info
            // -----------------------
            if ($player) {
                $player->jursey_name       = $general['jursey_name'] ?? $player->jursey_name;
                $player->jursey_number     = $general['jursey_number'] ?? $player->jursey_number;
                $player->jursey_size       = $general['jursey_size'] ?? $player->jursey_size;
                $player->chest_measurement = $general['chest_measurement'] ?? $player->chest_measurement;
                $player->batting_style     = $general['batting_style'] ?? $player->batting_style;
                $player->bowling_style     = $general['bowling_style'] ?? $player->bowling_style;
                $player->save();
            }

            $profilePicture = $request->input('profile_image');
            
            if ($profilePicture) {
                if (preg_match('/^data:image\/(\w+);base64,/', $profilePicture, $type)) {
                    $imageData = substr($profilePicture, strpos($profilePicture, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $extension = strtolower($type[1]);

                    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        return redirect()->back()->with([
                            'success' => false,
                            'message' => 'Invalid image type. Allowed types: jpg, jpeg, png, gif.'
                        ]);
                    }

                    $filename = time() . '_' . $user->id . '.' . $extension;
                    $basePath = storage_path('app/public/uploads');
                    $uploadPath = $user->hasRole('player') ? $basePath . '/players' : $basePath . '/users';

                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0775, true);
                    }

                    file_put_contents($uploadPath . '/' . $filename, $imageData);

                    $user->image = $filename;
                } else {
                    return redirect()->back()->with([
                        'success' => false,
                        'message' => 'Invalid image data.'
                    ]);
                }
            }

            // -----------------------
            // Handle Password Change
            // -----------------------
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }

            $user->save();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Profile updated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile Update Error: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ]);
        }
    }


    public function matches()
    {
        try {
            $user = Auth::user();

            $player = $user->player;

            if (!$player) {
                return redirect()->back()->with('error', 'No player profile found.');
            }

            $recentMatches = collect();

            if ($player->matches()->exists()) {
                $recentMatches = $player->matches()
                    ->latest('match_date')
                    ->paginate(10);

                return view('player.pages.cricket-matches.index', compact('recentMatches'));
            }

            return redirect()->back()->with([
                'success' => false,
                'message' => 'No Matches Found.'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function payments()
    {
        try {
            $user = Auth::user();
            $player = $user->player;

            if (!$player) {
                return redirect()->back()->with('error', 'No player profile found.');
            }

            $currentYear = now()->year;

            // Get all payments of current year
            $payments = $player->payments()
                ->whereYear('payment_date', $currentYear)
                ->orderBy('payment_date', 'asc')
                ->get();

            // -------------------------
            // YEAR SUMMARY (type wise)
            // -------------------------
            $yearSummary = [
                'donation'   => $payments->where('type', 'donation')->sum('amount'),
                'tournament' => $payments->where('type', 'tournament')->sum('amount'),
                'jersey'     => $payments->where('type', 'jersey')->sum('amount'),
                'other'      => $payments->where('type', 'other')->sum('amount'),
                'monthly'    => $payments->where('type', 'monthly')->sum('amount'),
                'total'      => $payments->sum('amount'),
            ];

            // -------------------------
            // MONTH LIST
            // ---------------------------
            $months = collect(range(1, 12))->mapWithKeys(function ($m) {
                return [$m => \Carbon\Carbon::create()->month($m)->format('F')];
            });

            // -------------------------
            // MONTH & TYPE WISE SUMMARY
            // ---------------------------
            $monthWise = [];

            foreach ($months as $monthNumber => $monthName) {
                $monthPayments = $payments->where('payment_date', '>=', now()->setMonth($monthNumber)->startOfMonth())
                    ->where('payment_date', '<=', now()->setMonth($monthNumber)->endOfMonth());

                $monthWise[$monthName] = [
                    'donation'   => $monthPayments->where('type', 'donation')->sum('amount'),
                    'tournament' => $monthPayments->where('type', 'tournament')->sum('amount'),
                    'jersey'     => $monthPayments->where('type', 'jersey')->sum('amount'),
                    'other'      => $monthPayments->where('type', 'other')->sum('amount'),
                    'total'      => $monthPayments->sum('amount'),
                ];
            }

            return view('player.pages.payments.index', compact('monthWise', 'yearSummary'));
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Something went wrong.'
            ]);
        }
    }
}
