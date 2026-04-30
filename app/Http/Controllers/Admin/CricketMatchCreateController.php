<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Team;
use App\Models\Player;
use App\Models\CricketMatch;
use App\Models\MatchPlayer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CricketMatchCreateController extends Controller
{
    protected string $module = 'cricket-matches';

    protected array $categories = [
        'favourite_football_country'     => 'Favourite Football Country',
        'favourite_cricket_country'      => 'Favourite Cricket Country',
        'favourite_football_league_team' => 'Favourite Football League Team',
        'married_status'                 => 'Married Status',
        'education_batch'                => 'Education Batch',
        'ssc_batch'                      => 'SSC Batch',
    ];

    // ----------------------------------------------------------------
    // create() – show the full-page creation form
    // ----------------------------------------------------------------
    public function create()
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Create Casual Match',
                'breadcrumbs' => [
                    'home'           => ['url' => route('admin.dashboard'),               'name' => 'Dashboard'],
                    'matches'        => ['url' => route('admin.cricket-matches.index'),   'name' => 'Cricket Matches'],
                    'create-casual'  => ['url' => route('admin.cricket-matches.create-casual'), 'name' => 'Create Casual Match'],
                ],
            ]);

            return view('admin.pages.cricket-matches.create-casual', [
                'categories' => $this->categories,
            ]);
        } catch (Exception $e) {
            Log::error('Error loading casual match create form', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);
            return redirect()->back()->with(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ----------------------------------------------------------------
    // filterPlayers() – AJAX: return players matching each team's criteria
    // ----------------------------------------------------------------
    public function filterPlayers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category'     => 'required|in:' . implode(',', array_keys($this->categories)),
            'team_a_value' => 'required|string|max:100',
            'team_b_value' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $category   = $request->category;
        $teamAValue = trim($request->team_a_value);
        $teamBValue = trim($request->team_b_value);

        $teamAPlayers = $this->filterPlayersByCategory($category, $teamAValue);
        $teamBPlayers = $this->filterPlayersByCategory($category, $teamBValue);

        return response()->json([
            'success'        => true,
            'team_a_players' => $this->formatPlayers($teamAPlayers),
            'team_b_players' => $this->formatPlayers($teamBPlayers),
            'team_a_name'    => $this->generateTeamName($category, $teamAValue),
            'team_b_name'    => $this->generateTeamName($category, $teamBValue),
        ]);
    }

    // ----------------------------------------------------------------
    // store() – create the match, teams, and assign players
    // ----------------------------------------------------------------
    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            $validator = Validator::make($request->all(), [
                'category'          => 'required|in:' . implode(',', array_keys($this->categories)),
                'team_a_value'      => 'required|string|max:100',
                'team_b_value'      => 'required|string|max:100',
                'team_a_players'    => 'required|array|min:1',
                'team_a_players.*'  => 'required|integer|exists:players,id',
                'team_b_players'    => 'required|array|min:1',
                'team_b_players.*'  => 'required|integer|exists:players,id',
                'title'             => 'nullable|string|max:255',
                'match_date'        => 'nullable|date',
                'venue'             => 'nullable|string|max:255',
                'max_overs'         => 'required|integer|min:1',
                'bowler_max_overs'  => 'nullable|integer|min:1',
                'status'            => 'required|in:upcoming,live,completed',
            ], [
                'team_a_players.required' => 'Please select at least one player for Team A.',
                'team_b_players.required' => 'Please select at least one player for Team B.',
                'team_a_players.min'      => 'Team A must have at least 1 player.',
                'team_b_players.min'      => 'Team B must have at least 1 player.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // No player may appear in both teams
            $overlap = array_intersect(
                array_map('intval', $request->team_a_players),
                array_map('intval', $request->team_b_players)
            );
            if (!empty($overlap)) {
                return redirect()->back()->withInput()->withErrors([
                    'team_a_players' => 'The same player cannot appear in both teams.',
                ]);
            }

            DB::beginTransaction();

            $category   = $request->category;
            $teamAValue = trim($request->team_a_value);
            $teamBValue = trim($request->team_b_value);

            // Generate team names
            $teamAName = $this->generateTeamName($category, $teamAValue);
            $teamBName = $this->generateTeamName($category, $teamBValue);

            $catLabel = $this->categories[$category] ?? $category;

            $teamA = Team::create([
                'name'        => $teamAName,
                'slug'        => $this->uniqueTeamSlug($teamAName),
                'description' => "Generated team – {$catLabel}: {$teamAValue}",
            ]);

            $teamB = Team::create([
                'name'        => $teamBName,
                'slug'        => $this->uniqueTeamSlug($teamBName),
                'description' => "Generated team – {$catLabel}: {$teamBValue}",
            ]);

            // Create the match
            $title = $request->filled('title')
                ? $request->title
                : "{$teamAName} vs {$teamBName}";

            $match = CricketMatch::create([
                'title'            => $title,
                'team_a_id'        => $teamA->id,
                'team_b_id'        => $teamB->id,
                'tournament_id'    => null,
                'match_date'       => $request->match_date,
                'venue'            => $request->venue,
                'match_type'       => 'regular',
                'status'           => $request->status,
                'max_overs'        => $request->max_overs,
                'bowler_max_overs' => $request->bowler_max_overs,
            ]);

            // Assign players to both teams
            $this->assignPlayersToMatch($match, $teamA, array_map('intval', $request->team_a_players));
            $this->assignPlayersToMatch($match, $teamB, array_map('intval', $request->team_b_players));

            DB::commit();

            Log::info('Casual cricket match created', [
                'match_id'  => $match->id,
                'category'  => $category,
                'team_a'    => $teamAName,
                'team_b'    => $teamBName,
                'created_by' => Auth::id(),
            ]);

            return redirect()
                ->route('admin.cricket-matches.show', $match->id)
                ->with(['success' => true, 'message' => 'Casual match created successfully!']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storing casual cricket match', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);
            return redirect()->back()->withInput()->with([
                'success' => false,
                'message' => 'Error creating match: ' . $e->getMessage(),
            ]);
        }
    }

    // ----------------------------------------------------------------
    // addPlayer() – AJAX: search existing or create guest player
    // ----------------------------------------------------------------
    public function addPlayer(Request $request)
    {
        // Search mode
        if ($request->filled('search_query')) {
            $q = trim($request->search_query);

            $players = Player::with('user')
                ->whereHas('user', function ($query) use ($q) {
                    $query->where('status', 'active')
                          ->where(function ($sub) use ($q) {
                              $sub->where('full_name', 'LIKE', "%{$q}%")
                                  ->orWhere('phone', 'LIKE', "%{$q}%");
                          });
                })
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'mode'    => 'search',
                'players' => $players->map(fn($p) => $this->playerToArray($p))->values(),
            ]);
        }

        // Create mode
        $validator = Validator::make($request->all(), [
            'full_name'     => 'required|string|max:150',
            'phone'         => 'nullable|string|max:30',
            'player_role'   => 'nullable|in:batsman,bowler,all-rounder,wicketkeeper',
            'batting_style' => 'nullable|in:right-handed,left-handed,switch hitter',
            'bowling_style' => 'nullable|in:fast,medium,spin,none',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // If phone given, avoid duplicates
        if ($request->filled('phone')) {
            $existingUser = User::where('phone', trim($request->phone))->first();
            if ($existingUser && $existingUser->player) {
                $existingUser->player->load('user');
                return response()->json([
                    'success' => true,
                    'mode'    => 'existing',
                    'player'  => $this->playerToArray($existingUser->player),
                    'message' => 'Player with this phone already exists — added to list.',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $fullName = trim($request->full_name);
            $phone    = trim($request->phone ?? '');

            // Generate a unique username + email
            $rand     = Str::random(6);
            $base     = Str::slug($fullName);
            $username = $base . '-' . $rand;
            $email    = 'guest.' . $username . '@madcricketers.local';

            while (User::where('email', $email)->orWhere('username', $username)->exists()) {
                $rand     = Str::random(6);
                $username = $base . '-' . $rand;
                $email    = 'guest.' . $username . '@madcricketers.local';
            }

            $user = User::create([
                'full_name'   => $fullName,
                'username'    => $username,
                'email'       => $email,
                'phone'       => $phone ?: ('00-' . $rand),
                'blood_group' => 'Unknown',
                'password'    => Hash::make(Str::random(16)),
                'status'      => 'active',
            ]);

            $user->assignRole('player');

            $player = Player::create([
                'user_id'       => $user->id,
                'player_type'   => 'guest',
                'player_role'   => $request->player_role,
                'batting_style' => $request->batting_style,
                'bowling_style' => $request->bowling_style,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mode'    => 'created',
                'player'  => $this->playerToArray($player->load('user')),
                'message' => 'Guest player created and added to team.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating guest player in casual match flow', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating player: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ================================================================
    // Private helpers
    // ================================================================

    private function filterPlayersByCategory(string $category, string $value): \Illuminate\Support\Collection
    {
        $query = Player::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'));

        switch ($category) {
            case 'favourite_football_country':
            case 'favourite_cricket_country':
            case 'favourite_football_league_team':
                $query->where($category, 'LIKE', '%' . trim($value) . '%');
                break;

            case 'married_status':
                $normalized = $this->normalizeMarriedStatus($value);
                $query->whereRaw('LOWER(TRIM(married_status)) IN (' . implode(',', array_fill(0, count($normalized), '?')) . ')', $normalized);
                break;

            case 'education_batch':
                [$start, $end] = $this->parseBatchRange($value);
                if ($start && $end) {
                    $query->whereRaw('CAST(education_batch AS UNSIGNED) BETWEEN ? AND ?', [$start, $end]);
                } elseif ($start) {
                    $query->whereRaw('CAST(education_batch AS UNSIGNED) = ?', [$start]);
                }
                break;

            case 'ssc_batch':
                $year = preg_replace('/\D/', '', $value);
                $query->where('ssc_batch', $year);
                break;
        }

        return $query->orderBy('id')->get();
    }

    private function formatPlayers(\Illuminate\Support\Collection $players): array
    {
        return $players->map(fn($p) => $this->playerToArray($p))->values()->toArray();
    }

    private function playerToArray(Player $player): array
    {
        $user = $player->relationLoaded('user') ? $player->user : $player->user()->first();
        return [
            'id'            => $player->id,
            'full_name'     => $user?->full_name ?? 'Unknown',
            'image'         => $user?->image,
            'phone'         => $user?->phone,
            'player_role'   => $player->player_role,
            'batting_style' => $player->batting_style,
            'bowling_style' => $player->bowling_style,
            'player_type'   => $player->player_type,
        ];
    }

    private function generateTeamName(string $category, string $value): string
    {
        $value = trim($value);
        switch ($category) {
            case 'favourite_football_country':
            case 'favourite_cricket_country':
            case 'favourite_football_league_team':
                return strtoupper($value) . ' XI';
            case 'married_status':
                return ucfirst(strtolower($value)) . ' XI';
            case 'education_batch':
                return 'Batch ' . $value . ' XI';
            case 'ssc_batch':
                return 'SSC ' . $value . ' XI';
            default:
                return $value . ' XI';
        }
    }

    private function uniqueTeamSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;
        while (Team::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function normalizeMarriedStatus(string $value): array
    {
        $v = strtolower(trim($value));
        if (in_array($v, ['unmarried', 'single', 'bachelor'])) {
            return ['unmarried', 'single', 'bachelor'];
        }
        return ['married'];
    }

    private function parseBatchRange(string $value): array
    {
        $value = trim($value);
        if (strpos($value, '-') !== false) {
            $parts = explode('-', $value, 2);
            return [(int) trim($parts[0]), (int) trim($parts[1])];
        }
        $year = (int) preg_replace('/\D/', '', $value);
        return [$year, null];
    }

    private function assignPlayersToMatch(CricketMatch $match, Team $team, array $playerIds): void
    {
        foreach ($playerIds as $playerId) {
            // Populate match_players so the scoreboard can see available players
            MatchPlayer::create([
                'match_id'  => $match->id,
                'team_id'   => $team->id,
                'player_id' => $playerId,
                'status'    => 'fielding',
            ]);

            // Populate player_team so Team::playersForFriendlyMatch() works
            $team->players()->attach($playerId, [
                'match_id'      => $match->id,
                'tournament_id' => null,
            ]);
        }
    }
}
