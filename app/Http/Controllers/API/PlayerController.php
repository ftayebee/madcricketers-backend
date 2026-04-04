<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use App\Models\PlayerStat;
use App\Models\MatchPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    protected function generateUid(): string
    {
        $prefix = 'MC00';
        $maxAttempts = 100;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Generate 5 random digits
            $randomDigits = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $uid = $prefix . $randomDigits;

            // Check if UID already exists in players table
            $exists = \DB::table('players')->where('player_uid', $uid)->exists();

            if (!$exists) {
                return $uid;
            }
        }

        return $prefix . substr(time(), -5);
    }

    protected function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);

        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;

        while (DB::table('users')->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function boot(Request $request)
    {
        try {
            $users = User::with(['player'])->get();

            foreach ($users as $user) {
                if ($user->player) {
                    if (empty($user->player->player_uid)) {
                        $user->player->player_uid = $this->generateUid();
                        $user->player->update();
                    }
                }

                if (empty($user->slug)) {
                    $user->slug = $this->generateSlug($user->full_name);
                    $user->update();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Boot data processed successfully.',
            ], 200);
        } catch (Exception $e) {
            Log::error("Error fetching boot data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching boot data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image'           => 'nullable|mimes:jpg,jpeg,png|max:1024',
                'full_name'       => 'required|string|max:255',
                'nickname'        => 'nullable|string|max:255',
                'username'        => 'nullable|string|max:255',
                'email'           => 'required|email|unique:users,email|max:255',
                'phone'           => 'required|string|max:15',
                'blood_group'     => 'nullable|string|max:3',
                'password'        => 'required|string|min:8',
                'gender'          => 'nullable|in:male,female,other',
                'date_of_birth'   => 'nullable|date|before:today',
                'religion'        => 'nullable|string|max:255',
                'national_id'     => 'nullable|digits_between:10,17|unique:users,national_id',
                'address'         => 'nullable|string|max:500',
                'player_type'     => 'required|in:guest,registered',
                'player_role'     => 'required|string|max:50',
                'batting_style'   => 'required|string|max:50',
                'bowling_style'   => 'required|string|max:50',
                'jursey_number'      => 'required|string|max:10',
                'jursey_name'        => 'required|string|max:50',
                'jursey_size'        => 'required|in:s,m,l,xl,2xl,3xl',
                'chest_measurement'  => 'nullable|string|max:10',
                'favourite_football_country' => 'nullable|string|max:255',
                'favourite_cricket_country' => 'nullable|string|max:255',
                'favourite_football_league_team' => 'nullable|string|max:255',
                'married_status'     => 'nullable|string|max:255',
                'education_batch'    => 'nullable|string|max:255',
                'ssc_batch'          => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $email = $request->input('email');
            $username = empty($request->input('username')) ? explode('@', $email)[0] : $request->input('username');

            $user = new User();
            $user->full_name    = $request->input('full_name');
            $user->nickname     = $request->input('nickname');
            $user->username     = $username;
            $user->email        = $email;
            $user->phone        = $request->input('phone');
            $user->blood_group  = $request->input('blood_group');
            $user->status       = 'active';

            $password           = $request->input('password');
            if (!empty($password)) {
                $user->password     = bcrypt($password);
                $user->visible_pass = $password;
            }

            $user->gender       = $request->input('gender');
            $user->date_of_birth = $request->input('date_of_birth');
            $user->religion     = $request->input('religion');
            $user->national_id  = $request->input('national_id');
            $user->address      = $request->input('address');
            $user->role_id      = 3;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'user_' . time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);

                // Resize if large
                if ($image->filesize() > 200 * 1024) {
                    $image->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->encode($file->getClientOriginalExtension(), 75);
                }

                $quality = 75;
                while (strlen((string) $image) > 200 * 1024 && $quality > 10) {
                    $image->encode($file->getClientOriginalExtension(), $quality);
                    $quality -= 5;
                }

                $uploadPath = storage_path('app/public/uploads/players');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }
                $image->save($uploadPath . '/' . $filename);
                $user->image = $filename;
            }

            $user->save();
            $role = Role::findOrFail($user->role_id);
            $user->syncRoles([$role]);

            $player                 = new Player();
            $player->user_id        = $user->id;
            $player->player_type    = $request->input('player_type');
            $player->player_role    = $request->input('player_role');
            $player->batting_style  = $request->input('batting_style');
            $player->bowling_style  = $request->input('bowling_style');
            $player->jursey_number     = $request->input('jursey_number');
            $player->jursey_name       = $request->input('jursey_name');
            $player->jursey_size       = $request->input('jursey_size');
            $player->chest_measurement = $request->input('chest_measurement');
            $player->favourite_football_country = $request->input('favourite_football_country');
            $player->favourite_cricket_country = $request->input('favourite_cricket_country');
            $player->favourite_football_league_team = $request->input('favourite_football_league_team');
            $player->married_status    = $request->input('married_status');
            $player->education_batch   = $request->input('education_batch');
            $player->ssc_batch         = $request->input('ssc_batch');
            $player->save();

            DB::commit();

            Log::info('Player registration successful', [
                'user_id' => $user->id,
                'player_id' => $player->id
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Player registered successfully.',
                'user' => $user,
                'player' => $player
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error Saving User", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllPlayers(Request $request)
    {
        try {
            $players = Player::with(['user', 'teams'])->get();
            return response()->json([
                'success' => true,
                'data' => $players
            ], 200);
        } catch (Exception $e) {
            Log::error("Error fetching players", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching players.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getKeyStats(Request $request)
    {
        try {
            // 1. Most Runs
            $mostRuns = PlayerStat::with(['player.user', 'player.teams'])
                ->where('total_runs', '>', 0)
                ->orderBy('total_runs', 'desc')
                ->first();

            // 2. Most Wickets
            $mostWickets = PlayerStat::with(['player.user', 'player.teams'])
                ->where('wickets', '>', 0)
                ->orderBy('wickets', 'desc')
                ->first();

            // 3. Best Strike Rate (minimum 50 balls faced)
            $bestStrikeRate = PlayerStat::with(['player.user', 'player.teams'])
                ->where('balls_faced', '>=', 50)
                ->where('strike_rate', '>', 0)
                ->orderBy('strike_rate', 'desc')
                ->first();

            // 4. Best Economy Rate (minimum 10 overs bowled)
            $bestEconomyRate = PlayerStat::with(['player.user', 'player.teams'])
                ->where('overs_bowled', '>=', 10)
                ->where('economy_rate', '>', 0)
                ->orderBy('economy_rate', 'asc') // Lower is better
                ->first();

            // 5. Highest Batting Average (minimum 5 innings)
            $highestBattingAverage = PlayerStat::with(['player.user', 'player.teams'])
                ->where('innings_batted', '>=', 5)
                ->where('average', '>', 0)
                ->orderBy('average', 'desc')
                ->first();

            // 6. Best Bowling Average (minimum 5 wickets)
            $bestBowlingAverage = PlayerStat::with(['player.user', 'player.teams'])
                ->where('wickets', '>=', 5)
                ->where('bowling_average', '>', 0)
                ->orderBy('bowling_average', 'asc') // Lower is better
                ->first();

            // 7. Most Fifties
            $mostFifties = PlayerStat::with(['player.user', 'player.teams'])
                ->where('fifties', '>', 0)
                ->orderBy('fifties', 'desc')
                ->first();

            // 8. Most Hundreds
            $mostHundreds = PlayerStat::with(['player.user', 'player.teams'])
                ->where('hundreds', '>', 0)
                ->orderBy('hundreds', 'desc')
                ->first();

            // 9. Most Sixes
            $mostSixes = PlayerStat::with(['player.user', 'player.teams'])
                ->where('sixes', '>', 0)
                ->orderBy('sixes', 'desc')
                ->first();

            // 10. Most Fours
            $mostFours = PlayerStat::with(['player.user', 'player.teams'])
                ->where('fours', '>', 0)
                ->orderBy('fours', 'desc')
                ->first();

            $bestBowlingFigure = null;

            $bestMatchBowling = MatchPlayer::with(['player.user', 'player.teams', 'match'])
                ->where('wickets_taken', '>', 0)
                ->select(
                    '*',
                    DB::raw('wickets_taken as wickets'),
                    DB::raw('runs_conceded as runs'),
                    DB::raw('CONCAT(wickets_taken, "/", runs_conceded) as bowling_figure')
                )
                ->orderBy('wickets_taken', 'desc')
                ->orderBy('runs_conceded', 'asc')
                ->first();


            if ($bestMatchBowling) {
                $bestBowlingFigure = collect([
                    'id' => $bestMatchBowling->id,
                    'player_id' => $bestMatchBowling->player_id,
                    'wickets' => $bestMatchBowling->wickets_taken,
                    'runs_conceded' => $bestMatchBowling->runs_conceded,
                    'bowling_figure' => $bestMatchBowling->wickets_taken . '/' . $bestMatchBowling->runs_conceded,
                    'player' => $bestMatchBowling->player,
                    'match_context' => [
                        'match_id' => $bestMatchBowling->match_id,
                        'match_title' => $bestMatchBowling->match ? $bestMatchBowling->match->title : null,
                        'wickets_taken' => $bestMatchBowling->wickets_taken,
                        'runs_conceded' => $bestMatchBowling->runs_conceded,
                        'against_team' => $bestMatchBowling->match ? $bestMatchBowling->match->opponent_team : null,
                    ]
                ]);
            } else {
                // Fallback
                $bestBowlingFigure = PlayerStat::with(['player.user', 'player.teams'])
                    ->where('wickets', '>', 0)
                    ->orderBy('wickets', 'desc')
                    ->orderBy('runs_conceded', 'asc')
                    ->first();
            }

            Log::info('Best Bowling Figure: ', ['figure' => $bestBowlingFigure->toArray()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'most_runs' => $mostRuns,
                    'most_wickets' => $mostWickets,
                    'bestStrikeRate' => $bestStrikeRate,
                    'bestEconomyRate' => $bestEconomyRate,
                    'highest_batting_average' => $highestBattingAverage,
                    'best_bowling_average' => $bestBowlingAverage,
                    'most_fifties' => $mostFifties,
                    'most_hundreds' => $mostHundreds,
                    'most_sixes' => $mostSixes,
                    'most_fours' => $mostFours,
                    'best_bowling_figure' => $bestBowlingFigure, // Or null if not available
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error("Error fetching player key stats", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching player key stats.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPlayerBySlug(Request $request, $slug)
    {
        try {
            $player = Player::with([
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'email', 'slug', 'image', 'role_id', 'created_at');
                },
                'teams' => function ($query) {
                    $query->select('teams.id', 'teams.name', 'teams.slug', 'teams.logo');
                },
                'statistics' => function ($query) {
                    $query->select('*');
                },
                'matchPlayers' => function ($query) {
                    $query->with(['match' => function ($q) {
                        $q->select('cricket_matches.id', 'cricket_matches.title', 'cricket_matches.match_date', 'cricket_matches.venue', 'cricket_matches.team_a_id', 'cricket_matches.team_b_id')
                            ->with([
                                'teamA:teams.id,teams.name,teams.slug',
                                'teamB:teams.id,teams.name,teams.slug'
                            ]);
                    }])
                        ->select(
                            'id',
                            'player_id',
                            'match_id',
                            'runs_scored',
                            'wickets_taken',
                            'balls_faced',
                            'runs_conceded',
                            'overs_bowled'
                        )
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                },
                'tournamentStatistic' => function ($query) {
                    $query->with(['tournament:id,name,season'])
                        ->select('*')
                        ->orderBy('total_runs', 'desc');
                }
            ])
                ->whereHas('user', function ($query) use ($slug) {
                    $query->where('slug', $slug);
                })
                ->first();

            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found.',
                ], 404);
            }

            $formattedPlayer = $this->formatPlayerProfileData($player);

            return response()->json([
                'success' => true,
                'data' => $formattedPlayer
            ], 200);
        } catch (Exception $e) {
            Log::error("Error fetching player by slug", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the player.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatPlayerProfileData($player)
    {
        $careerStats = $this->calculateCareerStats($player);

        return [
            'player' => [
                'id' => $player->id,
                'player_uid' => $player->player_uid,
                'role' => $player->role,
                'batting_style' => $player->batting_style,
                'bowling_style' => $player->bowling_style,
                'is_active' => $player->is_active,
                'debut_date' => $player->created_at,
                'created_at' => $player->created_at,
            ],
            'user' => $player->user ? [
                'id' => $player->user->id,
                'full_name' => $player->user->full_name,
                'slug' => $player->user->slug,
                'image' => $player->user->image,
                'email' => $player->user->email,
                'role' => $player->user->role,
            ] : null,
            'teams' => $player->teams->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'logo' => $team->logo,
                ];
            }),
            'statistics' => $player->statistics ? [
                'matches_played' => $player->statistics->matches_played,
                'innings_batted' => $player->statistics->innings_batted,
                'total_runs' => $player->statistics->total_runs,
                'fifties' => $player->statistics->fifties,
                'hundreds' => $player->statistics->hundreds,
                'sixes' => $player->statistics->sixes,
                'fours' => $player->statistics->fours,
                'strike_rate' => $player->statistics->strike_rate,
                'average' => $player->statistics->average,
                'innings_bowled' => $player->statistics->innings_bowled,
                'overs_bowled' => $player->statistics->overs_bowled,
                'runs_conceded' => $player->statistics->runs_conceded,
                'wickets' => $player->statistics->wickets,
                'bowling_average' => $player->statistics->bowling_average,
                'economy_rate' => $player->statistics->economy_rate,
                'catches' => $player->statistics->catches,
                'runouts' => $player->statistics->runouts,
                'stumpings' => $player->statistics->stumpings,
            ] : null,
            'recent_matches' => $player->matchPlayers->map(function ($matchPlayer) {
                return [
                    'match_id' => $matchPlayer->match_id,
                    'title' => $matchPlayer->match->title,
                    'match_date' => $matchPlayer->match->match_date,
                    'venue' => $matchPlayer->match->venue,
                    'teams' => [
                        'team_a' => $matchPlayer->match->teamA,
                        'team_b' => $matchPlayer->match->teamB,
                    ],
                    'performance' => [
                        'runs_scored' => $matchPlayer->runs_scored,
                        'wickets_taken' => $matchPlayer->wickets_taken,
                        'balls_faced' => $matchPlayer->balls_faced,
                        'runs_conceded' => $matchPlayer->runs_conceded,
                        'overs_bowled' => $matchPlayer->overs_bowled,
                    ],
                ];
            }),
            'career_stats_by_format' => $careerStats,
            'player_info' => [
                'age' => $this->calculateAge($player->created_at),
                'country' => $player->country ?? 'Unknown',
                'playing_since' => $player->created_at ? date('Y', strtotime($player->created_at)) : 'N/A',
                'full_bio' => $player->bio ?? 'No biography available.',
            ],
        ];
    }

    private function calculateCareerStats($player)
    {
        return [
            'odi' => [
                'matches' => $player->statistics ? $player->statistics->matches_played : 0,
                'runs' => $player->statistics ? $player->statistics->total_runs : 0,
                'average' => $player->statistics ? $player->statistics->average : 0,
                'strike_rate' => $player->statistics ? $player->statistics->strike_rate : 0,
                'wickets' => $player->statistics ? $player->statistics->wickets : 0,
                'bowling_average' => $player->statistics ? $player->statistics->bowling_average : 0,
                'economy' => $player->statistics ? $player->statistics->economy_rate : 0,
            ],
            't20' => [
                'matches' => 0, // You'll need to calculate this from match data
                'runs' => 0,
                'average' => 0,
                'strike_rate' => 0,
                'wickets' => 0,
                'bowling_average' => 0,
                'economy' => 0,
            ],
            'test' => [
                'matches' => 0,
                'runs' => 0,
                'average' => 0,
                'strike_rate' => 0,
                'wickets' => 0,
                'bowling_average' => 0,
                'economy' => 0,
            ],
            'ipl' => [
                'matches' => 0,
                'runs' => 0,
                'average' => 0,
                'strike_rate' => 0,
                'wickets' => 0,
                'bowling_average' => 0,
                'economy' => 0,
            ],
        ];
    }

    private function calculateAge($dateOfBirth)
    {
        if (!$dateOfBirth) return 'N/A';

        try {
            $birthDate = Carbon::parse($dateOfBirth);
            $today = Carbon::now();

            return $today->diffInYears($birthDate);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}
