<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MatchPlayer;
use App\Models\Player;
use App\Models\TournamentPlayerStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PlayerAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $login = trim($validated['login']);
        $user = User::with('player.teams')
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('phone', $login)
                    ->orWhere('username', $login);
            })
            ->orWhereHas('player', fn ($query) => $query->where('player_uid', $login))
            ->first();

        if (!$user || !$user->player || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid player login credentials.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your player account is inactive. Please contact MadCricketers support.',
            ], 403);
        }

        $token = $user->createToken($validated['device_name'] ?? 'player-frontend', ['player'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Player logged in successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => $this->profilePayload($user->load('player.teams')),
        ]);
    }

    public function logout(Request $request)
    {
        $this->playerUser($request)->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Player logged out successfully.',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->profilePayload($this->playerUser($request)->load('player.teams')),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->playerUser($request);
        $player = $user->player;

        $validated = $request->validate([
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'player_role' => ['nullable', 'in:batsman,bowler,all-rounder,wicketkeeper'],
            'batting_style' => ['nullable', 'in:right-handed,left-handed,switch hitter'],
            'bowling_style' => ['nullable', 'in:fast,medium,spin,none'],
            'jursey_number' => ['nullable', 'string', 'max:10'],
            'jursey_name' => ['nullable', 'string', 'max:50'],
            'jursey_size' => ['nullable', 'in:s,m,l,xl,2xl,3xl'],
            'chest_measurement' => ['nullable', 'string', 'max:10'],
            'favourite_football_country' => ['nullable', 'string', 'max:255'],
            'favourite_cricket_country' => ['nullable', 'string', 'max:255'],
            'favourite_football_league_team' => ['nullable', 'string', 'max:255'],
            'married_status' => ['nullable', 'string', 'max:255'],
            'education_batch' => ['nullable', 'string', 'max:255'],
            'ssc_batch' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['full_name', 'phone', 'email', 'address', 'date_of_birth'] as $field) {
            if (array_key_exists($field, $validated)) {
                $user->{$field} = $validated[$field];
            }
        }

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('public/uploads/players');
            $user->image = basename($path);
        }

        foreach ([
            'player_role',
            'batting_style',
            'bowling_style',
            'jursey_number',
            'jursey_name',
            'jursey_size',
            'chest_measurement',
            'favourite_football_country',
            'favourite_cricket_country',
            'favourite_football_league_team',
            'married_status',
            'education_batch',
            'ssc_batch',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $player->{$field} = $validated[$field];
            }
        }

        $user->save();
        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $this->profilePayload($user->load('player.teams')),
        ]);
    }

    public function statsSummary(Request $request)
    {
        $player = $this->playerUser($request)->player()->with('statistics')->firstOrFail();
        $stats = $player->statistics;

        $matches = MatchPlayer::where('player_id', $player->id)->count();
        $innings = MatchPlayer::where('player_id', $player->id)->where('balls_faced', '>', 0)->count();
        $highestScore = MatchPlayer::where('player_id', $player->id)->max('runs_scored') ?? 0;

        $runs = (int) ($stats->total_runs ?? MatchPlayer::where('player_id', $player->id)->sum('runs_scored'));
        $balls = (int) ($stats->balls_faced ?? MatchPlayer::where('player_id', $player->id)->sum('balls_faced'));
        $wickets = (int) ($stats->wickets ?? MatchPlayer::where('player_id', $player->id)->sum('wickets_taken'));
        $overs = (float) ($stats->overs_bowled ?? MatchPlayer::where('player_id', $player->id)->sum('overs_bowled'));
        $runsConceded = (int) ($stats->runs_conceded ?? MatchPlayer::where('player_id', $player->id)->sum('runs_conceded'));

        return response()->json([
            'success' => true,
            'data' => [
                'matches' => (int) ($stats->matches_played ?? $matches),
                'innings' => (int) ($stats->innings_batted ?? $innings),
                'runs' => $runs,
                'highest_score' => (int) $highestScore,
                'average' => round((float) ($stats->average ?? ($innings > 0 ? $runs / $innings : 0)), 2),
                'strike_rate' => round((float) ($stats->strike_rate ?? ($balls > 0 ? ($runs / $balls) * 100 : 0)), 2),
                'balls_faced' => $balls,
                'fours' => (int) ($stats->fours ?? 0),
                'sixes' => (int) ($stats->sixes ?? 0),
                'wickets' => $wickets,
                'overs_bowled' => $overs,
                'runs_conceded' => $runsConceded,
                'economy' => round((float) ($stats->economy_rate ?? ($overs > 0 ? $runsConceded / $overs : 0)), 2),
                'catches' => (int) ($stats->catches ?? 0),
                'runouts' => (int) ($stats->runouts ?? 0),
                'stumpings' => (int) ($stats->stumpings ?? 0),
            ],
        ]);
    }

    public function matchStats(Request $request)
    {
        $player = $this->playerUser($request)->player;
        $rows = MatchPlayer::with(['match.teamA', 'match.teamB', 'match.tournament', 'team'])
            ->where('player_id', $player->id)
            ->latest('id')
            ->get()
            ->map(function (MatchPlayer $row) {
                $match = $row->match;
                $opponent = null;
                if ($match && $row->team_id == $match->team_a_id) {
                    $opponent = $match->teamB;
                } elseif ($match) {
                    $opponent = $match->teamA;
                }

                $overs = (float) ($row->overs_bowled ?? 0);
                $runsConceded = (int) ($row->runs_conceded ?? 0);

                return [
                    'match_id' => $row->match_id,
                    'match_title' => $match?->title,
                    'date' => $match?->match_date ? (string) $match->match_date : null,
                    'team' => $row->team?->name,
                    'opponent' => $opponent?->name,
                    'tournament' => $match?->tournament?->name,
                    'runs' => (int) ($row->runs_scored ?? 0),
                    'balls' => (int) ($row->balls_faced ?? 0),
                    'fours' => (int) ($row->fours ?? 0),
                    'sixes' => (int) ($row->sixes ?? 0),
                    'wickets' => (int) ($row->wickets_taken ?? 0),
                    'overs' => $overs,
                    'runs_conceded' => $runsConceded,
                    'economy' => $overs > 0 ? round($runsConceded / $overs, 2) : 0,
                ];
            });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function tournamentStats(Request $request)
    {
        $player = $this->playerUser($request)->player;
        $rows = TournamentPlayerStat::with(['team', 'tournament'])
            ->where('player_id', $player->id)
            ->latest('id')
            ->get()
            ->map(function (TournamentPlayerStat $row) {
                return [
                    'tournament_id' => $row->tournament_id,
                    'tournament' => $row->tournament?->name,
                    'team' => $row->team?->name,
                    'matches' => (int) ($row->matches_played ?? 0),
                    'innings' => (int) ($row->innings_batted ?? 0),
                    'runs' => (int) ($row->total_runs ?? 0),
                    'balls' => (int) ($row->balls_faced ?? 0),
                    'average' => round((float) ($row->average ?? 0), 2),
                    'strike_rate' => round((float) ($row->strike_rate ?? 0), 2),
                    'wickets' => (int) ($row->wickets ?? 0),
                    'overs' => (float) ($row->overs_bowled ?? 0),
                    'runs_conceded' => (int) ($row->runs_conceded ?? 0),
                    'economy' => round((float) ($row->economy_rate ?? 0), 2),
                ];
            });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    private function playerUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user && $user->player, 403, 'Player account required.');

        return $user;
    }

    private function profilePayload(User $user): array
    {
        $player = $user->player;

        return [
            'id' => $player?->id,
            'player_uid' => $player?->player_uid,
            'user_id' => $user->id,
            'full_name' => $user->full_name,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'date_of_birth' => $user->date_of_birth,
            'image' => $user->image,
            'status' => $user->status,
            'player_type' => $player?->player_type,
            'player_role' => $player?->player_role,
            'batting_style' => $player?->batting_style,
            'bowling_style' => $player?->bowling_style,
            'jursey_number' => $player?->jursey_number,
            'jursey_name' => $player?->jursey_name,
            'jursey_size' => $player?->jursey_size,
            'chest_measurement' => $player?->chest_measurement,
            'teams' => $player && $player->relationLoaded('teams') ? $player->teams->map(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'logo' => $team->logo,
            ])->values() : [],
            'safe_update_fields' => [
                'full_name',
                'phone',
                'email',
                'address',
                'date_of_birth',
                'profile_image',
                'player_role',
                'batting_style',
                'bowling_style',
                'jursey_number',
                'jursey_name',
                'jursey_size',
                'chest_measurement',
            ],
        ];
    }
}
