<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\PlayerStat;
use App\Models\Tournament;
use App\Models\MatchPlayer;
use App\Models\Partnership;
use App\Models\CricketMatch;
use App\Models\FallOfWicket;
use Illuminate\Http\Request;
use App\Models\MatchDelivery;
use App\Models\MatchScoreBoard;
use App\Models\CricketMatchToss;
use App\Models\TournamentTeamStat;
use Illuminate\Support\Facades\DB;
use App\Models\TournamentGroupTeam;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\TournamentPlayerStat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CricketMatchController extends Controller
{
    protected $module = 'cricket-matches';

    public function edit(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-edit')) {
                throw new Exception('Unauthorized Access');
            }

            $matchId = $request->query('cricket-match');

            if (!$matchId) {
                throw new Exception('No match ID provided.');
            }

            $match = CricketMatch::with(['teamA', 'teamB', 'tournament'])->findOrFail($matchId);
            $today = Carbon::today();
            $teamsInFutureTournaments = TournamentGroupTeam::whereHas('group.tournament', function ($query) use ($today) {
                $query->where('start_date', '>', $today);
            })->pluck('team_id')->toArray();
            $tournaments = Tournament::where('start_date', '>', $today)->get();
            $teams = Team::whereNotIn('id', $teamsInFutureTournaments)->get();

            return view('admin.pages.cricket-matches.edit', compact('match', 'teams', 'tournaments'));
        } catch (ModelNotFoundException $e) {
            Log::error("Cricket match not found", [
                'message' => $e->getMessage(),
                'match_id' => $request->query('cricket-match'),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Match not found.',
            ]);
        } catch (Exception $e) {
            Log::error("Error loading cricket match edit form", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Failed to load match for editing.',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::user()->can($this->module . '-edit')) {
                throw new Exception('Unauthorized Access');
            }

            $matchId = $request->query('cricket-match');

            if (!$matchId) {
                throw new Exception('No match ID provided.');
            }

            $match = CricketMatch::with(['teamA', 'teamB', 'tournament'])->findOrFail($matchId);
            $today = Carbon::today();
            $teamsInFutureTournaments = TournamentGroupTeam::whereHas('group.tournament', function ($query) use ($today) {
                $query->where('start_date', '>', $today);
            })->pluck('team_id')->toArray();
            $tournaments = Tournament::where('start_date', '>', $today)->get();
            $teams = Team::whereNotIn('id', $teamsInFutureTournaments)->get();

            return view('admin.pages.cricket-matches.edit', compact('match', 'teams', 'tournaments'));
        } catch (ModelNotFoundException $e) {
            Log::error("Cricket match not found", [
                'message' => $e->getMessage(),
                'match_id' => $request->query('cricket-match'),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Match not found.',
            ]);
        } catch (Exception $e) {
            Log::error("Error loading cricket match edit form", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Failed to load match for editing.',
            ]);
        }
    }

    public function storeToss(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'match_id' => 'required|exists:cricket_matches,id',
                'toss_winner_team_id' => 'required|exists:teams,id',
                'toss_decision' => 'required|in:bat,bowl,BAT,BOWL',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->errors(),
                ], 422);
            }

            $matchId = $request->input('match_id');

            // Update or create toss data
            $tossData = CricketMatchToss::updateOrCreate(
                ['cricket_match_id' => $matchId],
                [
                    'toss_winner_team_id' => $request->input('toss_winner_team_id'),
                    'decision' => strtolower($request->input('toss_decision')), // 'bat' or 'bowl'
                ]
            );

            $match = CricketMatch::findOrFail($matchId);
            $teamA = $match->team_a_id;
            $teamB = $match->team_b_id;

            $tossWinner = (int) $request->input('toss_winner_team_id');
            $tossDecision = strtolower($request->input('toss_decision'));

            if ($tossDecision === 'bat') {
                $battingFirstTeam = $tossWinner;
            } else {
                $battingFirstTeam = ($tossWinner === $teamA) ? $teamB : $teamA;
            }

            $bowlingFirstTeam = ($battingFirstTeam === $teamA) ? $teamB : $teamA;

            MatchScoreBoard::where('match_id', $matchId)->delete();

            MatchScoreBoard::create([
                'match_id' => $matchId,
                'team_id' => $battingFirstTeam,
                'innings' => 1,
                'runs' => 0,
                'wickets' => 0,
                'overs' => 0,
            ]);

            MatchScoreBoard::create([
                'match_id' => $matchId,
                'team_id' => $bowlingFirstTeam,
                'innings' => 2,
                'runs' => 0,
                'wickets' => 0,
                'overs' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Toss data stored successfully.',
                'data' => $tossData,
                'matchScoreBoard' => MatchScoreBoard::where('match_id', $matchId)->get(),
                'batting_team_id' => $battingFirstTeam,
                'bowling_team_id' => $bowlingFirstTeam,
                'batting_team_name' => ($battingFirstTeam === $teamA) ? $match->teamA->name : $match->teamB->name,
                'bowling_team_name' => ($bowlingFirstTeam === $teamA) ? $match->teamA->name : $match->teamB->name,
                'toss_winner_team_name' => ($tossWinner === $teamA) ? $match->teamA->name : $match->teamB->name,
            ]);
        } catch (\Exception $e) {
            Log::error("Error storing toss data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store toss data.',
            ], 500);
        }
    }

    public function startCricketMatch($id)
    {
        try {
            if (!Auth::user()->can($this->module . '-start')) {
                throw new Exception('Unauthorized Access');
            }

            $match = CricketMatch::with(['teamA', 'teamB', 'tournament'])->findOrFail($id);

            // Mark match as live
            $match->status = 'live';
            $match->save();

            // If match belongs to a tournament, ensure tournament team stats exist
            if ($match->tournament) {
                $tournamentId = $match->tournament->id;

                // Team A
                TournamentTeamStat::firstOrCreate(
                    [
                        'tournament_id' => $tournamentId,
                        'team_id' => $match->teamA->id,
                    ],
                    [
                        'matches_played' => 0,
                        'wins' => 0,
                        'losses' => 0,
                        'draws' => 0,
                        'points' => 0,
                        'nrr' => 0,
                    ]
                );

                // Team B
                TournamentTeamStat::firstOrCreate(
                    [
                        'tournament_id' => $tournamentId,
                        'team_id' => $match->teamB->id,
                    ],
                    [
                        'matches_played' => 0,
                        'wins' => 0,
                        'losses' => 0,
                        'draws' => 0,
                        'points' => 0,
                        'nrr' => 0,
                    ]
                );
            }

            return view('admin.pages.cricket-matches.scoreboard', compact('match'))->with([
                'success' => true,
                'message' => 'Match started successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error("Cricket match not found", [
                'message' => $e->getMessage(),
                'match_id' => $id,
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Match not found.',
            ]);
        } catch (Exception $e) {
            Log::error("Error starting cricket match", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Failed to start match.',
            ]);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $match = CricketMatch::with(['teamA', 'teamB', 'tournament'])->findOrFail($id);

            return view('admin.pages.cricket-matches.show', compact('match'));
        } catch (ModelNotFoundException $e) {
            Log::error("Cricket match not found", [
                'message' => $e->getMessage(),
                'match_id' => $id,
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Match not found.',
            ]);
        } catch (Exception $e) {
            Log::error("Error loading cricket match details", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Failed to load match details.',
            ]);
        }
    }

    public function selectBatsman(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'match_id' => 'required|exists:cricket_matches,id',
                'team_id' => 'required|exists:teams,id',
                'player_id' => 'required|exists:players,id',
                'role' => 'required|in:on-strike,batting',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to select batsman.',
                    'errors'  => $validator->errors()
                ]);
            }

            $matchId = $request->match_id;
            $teamId = $request->team_id;
            $playerId = $request->player_id;
            $role = $request->role;

            // 1️⃣ Insert into match_players if not exists
            $matchPlayer = MatchPlayer::firstOrCreate(
                [
                    'match_id' => $matchId,
                    'team_id' => $teamId,
                    'player_id' => $playerId,
                ],
                [
                    'runs_scored' => 0,
                    'balls_faced' => 0,
                    'wickets_taken' => 0,
                    'overs_bowled' => 0,
                    'status' => $role === 'striker' ? 'on-strike' : 'batting',
                ]
            );

            // 2️⃣ Ensure player_statistics entry exists
            PlayerStat::firstOrCreate(
                ['player_id' => $playerId],
                [] // defaults handled in migration
            );

            // 3️⃣ Partnerships
            $activePartnership = Partnership::where('match_id', $matchId)
                ->where('team_id', $teamId)
                ->whereNull('end_over')
                ->latest()
                ->first();

            if (!$activePartnership) {
                // First batsman starting new partnership
                Partnership::create([
                    'match_id' => $matchId,
                    'team_id' => $teamId,
                    'batter_1_id' => $playerId,
                    'runs' => 0,
                    'balls' => 0,
                    'start_over' => 0.0,
                ]);
            } else {
                // Add second batsman if slot empty
                if (!$activePartnership->batter_2_id) {
                    $activePartnership->update(['batter_2_id' => $playerId]);
                }
            }

            // 4️⃣ Update tournament_player_stats if match is part of a tournament
            $cricketMatch = CricketMatch::with('tournament')->find($matchId);
            if ($cricketMatch && $cricketMatch->tournament) {
                $tournamentId = $cricketMatch->tournament->id;

                TournamentPlayerStat::firstOrCreate(
                    [
                        'tournament_id' => $tournamentId,
                        'player_id' => $playerId,
                    ],
                    [
                        'matches_played'   => 0,
                        'innings_batted'   => 0,
                        'total_runs'       => 0,
                        'balls_faced'      => 0,
                        'fifties'          => 0,
                        'hundreds'         => 0,
                        'sixes'            => 0,
                        'fours'            => 0,
                        'strike_rate'      => 0.0,
                        'average'          => 0.0,
                        'innings_bowled'   => 0,
                        'overs_bowled'     => 0,
                        'runs_conceded'    => 0,
                        'wickets'          => 0,
                        'bowling_average'  => 0.0,
                        'economy_rate'     => 0.0,
                        'catches'          => 0,
                        'runouts'          => 0,
                        'stumpings'        => 0,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Batsman selected successfully.',
                'data' => [
                    'match_player' => $matchPlayer,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error selecting batsman", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to select batsman.',
            ], 500);
        }
    }

    public function loadCurrentStats(Request $request)
    {
        try {
            $matchId = $request->match_id;
            $match = CricketMatch::findOrFail($matchId);

            // 🔹 Determine current innings
            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->orderByDesc('id')
                ->first();

            $currentInnings = $lastDelivery ? $lastDelivery->innings : 1;

            // 🔹 Determine batting & bowling teams using toss
            if ($currentInnings == 1) {
                if ($match->toss->decision === 'bat') {
                    $batting_team_id = $match->toss->toss_winner_team_id;
                    $bowling_team_id = $batting_team_id == $match->team_a_id ? $match->team_b_id : $match->team_a_id;
                } else {
                    $bowling_team_id = $match->toss->toss_winner_team_id;
                    $batting_team_id = $bowling_team_id == $match->team_a_id ? $match->team_b_id : $match->team_a_id;
                }
            } else {
                if ($match->toss->decision === 'bat') {
                    $bowling_team_id = $match->toss->toss_winner_team_id; // 2nd innings bowling is the team that batted first
                    $batting_team_id = $bowling_team_id == $match->team_a_id ? $match->team_b_id : $match->team_a_id;
                } else { // toss_choice == 'bowl'
                    $batting_team_id = $match->toss->toss_winner_team_id; // 2nd innings batting is the team that bowled first
                    $bowling_team_id = $batting_team_id == $match->team_a_id ? $match->team_b_id : $match->team_a_id;
                }
            }

            // 1️⃣ Current batting players
            $batting = MatchPlayer::with('player.user')
                ->where('match_id', $matchId)
                ->whereIn('status', ['batting', 'on-strike'])
                ->get()
                ->map(function ($mp) {
                    return [
                        'id' => $mp->player_id,
                        'name' => $mp->player->user->full_name,
                        'runs' => $mp->runs_scored ?? 0,
                        'balls' => $mp->balls_faced ?? 0,
                        'fours' => $mp->fours ?? 0,
                        'sixes' => $mp->sixes ?? 0,
                        'strike_rate' => $mp->balls_faced ? round(($mp->runs_scored / $mp->balls_faced) * 100, 2) : 0
                    ];
                });


            // 2️⃣ Current bowling players
            $bowling = MatchPlayer::with('player.user')
                ->where('match_id', $matchId)
                ->where('overs_bowled', '>', 0)
                ->get()
                ->map(function ($mp) {
                    return [
                        'id' => $mp->player_id,
                        'name' => $mp->player->user->full_name,
                        'overs' => $mp->overs_bowled,
                        'runs_conceded' => $mp->runs_conceded,
                        'wickets' => $mp->wickets_taken,
                        'economy_rate' => $mp->overs_bowled ? round($mp->runs_conceded / $mp->overs_bowled, 2) : 0
                    ];
                });


            // 3️⃣ Partnerships
            $partnerships = Partnership::with(['batter1.user', 'batter2.user'])
                ->where('match_id', $matchId)
                ->get()
                ->map(function ($p) {
                    // Calculate runs percentage for progress bar (optional)
                    $totalRuns = max(1, $p->runs); // avoid division by zero
                    $batter1Percent = $totalRuns ? round(($p->batter1_runs ?? 0) / $totalRuns * 100) : 0;
                    $batter2Percent = 100 - $batter1Percent;

                    return [
                        'batter1' => [
                            'id' => $p->batter1_id,
                            'name' => $p->batter1->user->full_name,
                            'img' => $p->batter1->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                            'role' => ucfirst(str_replace('-', ' ', $p->batter1->player_role)),
                            'runs' => $p->batter1_runs ?? 0,
                            'balls' => $p->batter1_balls ?? 0,
                            'percent' => $batter1Percent,
                        ],
                        'batter2' => $p->batter2 ? [
                            'id' => $p->batter2_id,
                            'name' => $p->batter2->user->full_name,
                            'img' => $p->batter2->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                            'role' => ucfirst(str_replace('-', ' ', $p->batter2->player_role)),
                            'runs' => $p->batter2_runs ?? 0,
                            'balls' => $p->batter2_balls ?? 0,
                            'percent' => $batter2Percent,
                        ] : null,
                        'start_over' => $p->start_over,
                        'end_over' => $p->end_over,
                        'runs' => $p->runs,
                        'balls' => $p->balls,
                    ];
                });


            // 4️⃣ Fall of wickets
            $fallOfWickets = FallOfWicket::with(['batter.user'])
                ->where('match_id', $matchId)
                ->orderBy('wicket_number')
                ->get()
                ->map(function ($w) {
                    return [
                        'player_name' => $w->batter ? $w->batter->user->full_name : 'Unknown',
                        'runs' => $w->runs,
                        'balls' => $w->balls ?? 0, // if you store balls separately
                        'over' => $w->overs, // from your table
                        'wicket_number' => $w->wicket_number,
                        'dismissal_type' => $w->dismissal_type,
                    ];
                });

            

            return response()->json([
                'success' => true,
                'batting' => $batting,
                'bowling' => $bowling,
                'partnerships' => $partnerships,
                'fall_of_wickets' => $fallOfWickets,
                'innings' => $currentInnings,
                'bowling_team_id' => $bowling_team_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading current stats', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load current stats.'
            ]);
        }
    }

    public function getTeamBPlayers($matchId)
    {
        $match = CricketMatch::with(['teamB.players.user'])->findOrFail($matchId);

        $players = $match->teamB->players->map(function ($player) {
            return [
                'id' => $player->id,
                'name' => $player->user->full_name,
                'style' => $player->bowling_style ?? 'N/A'
            ];
        });

        return response()->json($players);
    }

    public function chooseBowler(Request $request)
    {
        try {
            Log::info("Request Received: ", ['request'=> $request->all()]);
            $validator = Validator::make($request->all(), [
                'match_id' => 'required|exists:cricket_matches,id',
                'bowler_id' => 'required|exists:players,id',
                'team_id' => 'required|exists:teams,id',
                'style' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            $matchId = $request->match_id;
            $bowlerId = $request->bowler_id;
            $teamId = $request->team_id;
            
            // Check if bowler already exists in this match
            $bowler = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $bowlerId],
                [
                    'team_id' => $teamId,
                    'status' => 'bowling',
                    'overs_bowled' => 0,
                    'runs_conceded' => 0,
                    'wickets_taken' => 0
                ]
            );

            // Return current bowling list for this match
            $bowling = MatchPlayer::with('player.user')
                ->where('match_id', $matchId)
                ->where('status', 'bowling')
                ->get()
                ->map(function ($mp) {
                    return [
                        'id' => $mp->player_id,
                        'name' => $mp->player->user->full_name,
                        'overs' => $mp->overs_bowled,
                        'runs_conceded' => $mp->runs_conceded,
                        'wickets' => $mp->wickets_taken,
                        'economy_rate' => $mp->overs_bowled ? round($mp->runs_conceded / $mp->overs_bowled, 2) : 0,
                        'maidens' => $mp->maidens ?? 0
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Bowler added/updated successfully',
                'bowling' => $bowling
            ]);
        } catch (\Exception $e) {
            Log::error("Error Choosing Bowler: " . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add/update bowler',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getMatchInfo(Request $request)
    {
        try {
            $matchId = $request->match_id;

            // Find the match
            $match = CricketMatch::findOrFail($matchId);

            // Get latest scoreboard
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->latest('innings')
                ->first();

            $battingTeam = null;
            if ($scoreboard) {
                $battingTeam = Team::find($scoreboard->team_id);
            }

            // Get striker and non-striker
            $striker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'on-strike')
                ->first();

            $nonStriker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'batting')
                ->first();

            $matchState = [
                'striker' => $striker ? [
                    'id' => $striker->player_id,
                    'name' => $striker->player->user->full_name ?? 'Unknown',
                    'img' => $striker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg')
                ] : null,
                'nonStriker' => $nonStriker ? [
                    'id' => $nonStriker->player_id,
                    'name' => $nonStriker->player->user->full_name ?? 'Unknown',
                    'img' => $nonStriker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg')
                ] : null,
                'team' => $battingTeam ? [
                    'id' => $battingTeam->id,
                    'name' => $battingTeam->name,
                    'score' => $scoreboard ? $scoreboard->runs : 0,
                    'overs' => $scoreboard ? $scoreboard->overs : 0,
                    'crr' => $scoreboard ? round($scoreboard->runs / max(1, $scoreboard->overs), 2) : 0,
                    'projected' => $scoreboard ? round(($scoreboard->runs / max(1, $scoreboard->overs)) * 50, 0) : 0
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'match_state' => $matchState
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading match state', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load current stats.'
            ]);
        }
    }

    public function switchStrike(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'match_id' => 'required|exists:cricket_matches,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $matchId = $request->match_id;

            // Get current striker and non-striker
            $striker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'on-strike')
                ->first();

            $nonStriker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'batting')
                ->first();

            if (!$striker || !$nonStriker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot switch strike. Both striker and non-striker must be selected.',
                ], 400);
            }

            // Swap statuses
            $striker->status = 'batting';
            $nonStriker->status = 'on-strike';

            $striker->save();
            $nonStriker->save();

            return response()->json([
                'success' => true,
                'message' => 'Strike switched successfully.',
                'data' => [
                    'striker' => [
                        'id' => $nonStriker->player_id,
                        'name' => $nonStriker->player->user->full_name,
                        'img' => $nonStriker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                        'runs' => $nonStriker->runs_scored ?? 0,
                        'balls' => $nonStriker->balls_faced ?? 0,
                    ],
                    'nonStriker' => [
                        'id' => $striker->player_id,
                        'name' => $striker->player->user->full_name,
                        'img' => $striker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                        'runs' => $striker->runs_scored ?? 0,
                        'balls' => $striker->balls_faced ?? 0,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error switching strike", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to switch strike.',
            ], 500);
        }
    }

    public function storeRuns(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'match_id'      => 'required|exists:cricket_matches,id',
                'striker_id'    => 'required|exists:players,id',
                'non_striker_id' => 'required|exists:players,id',
                'runs'          => 'required|integer|min:0',
                'extra_type'    => 'nullable|string',
                'bowler_id'     => 'required|exists:players,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $matchId      = $request->match_id;
            $strikerId    = $request->striker_id;
            $nonStrikerId = $request->non_striker_id;
            $bowlerId     = $request->bowler_id;
            $runs         = $request->runs;
            $extra        = $request->extra_type;

            // 🔹 Fetch/Create striker & non-striker
            $striker = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $strikerId],
                ['runs_scored' => 0, 'balls_faced' => 0, 'status' => 'on-strike']
            );

            $nonStriker = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $nonStrikerId],
                ['runs_scored' => 0, 'balls_faced' => 0, 'status' => 'batting']
            );

            // 🔹 Determine if this is a legal ball
            $legalBall = true;
            if ($extra && in_array($extra, ['NB', 'WD'])) {
                $legalBall = false;
            }

            // 🔹 Get last delivery
            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->latest('id')
                ->first();

            if (!$lastDelivery) {
                $over = 1;
                $ball = 1;
            } else {
                $over = $lastDelivery->over_number;
                $ball = $lastDelivery->ball_in_over;

                if ($legalBall) {
                    if ($ball == 6) {
                        $over++;
                        $ball = 1;
                    } else {
                        $ball++;
                    }
                }
                // Extras: keep same over and ball
            }

            // 🔹 Insert delivery
            $delivery = MatchDelivery::create([
                'match_id'        => $matchId,
                'innings'         => 1,
                'over_number'     => $over,
                'ball_in_over'    => $ball,
                'bowler_id'       => $bowlerId,
                'batsman_id'      => $strikerId,
                'non_striker_id'  => $nonStrikerId,
                'batting_team_id' => $striker->team_id,
                'bowling_team_id' => $bowlerId ? Player::find($bowlerId)->team_id : null,
                'runs_batsman'    => $runs,
                'runs_extras'     => $legalBall ? 0 : $runs,
                'delivery_type'   => $extra ?? 'normal',
                'is_wicket'       => $extra === 'W',
                'wicket_type'     => $extra === 'W' ? 'bowled' : null,
                'wicket_player_id' => $extra === 'W' ? $strikerId : null,
            ]);

            // 🔹 Update striker stats
            if ($legalBall) {
                $striker->balls_faced += 1;
            }
            $striker->runs_scored += $runs;
            $striker->save();

            // 🔹 Update scoreboard (calculate overs & runs fresh)
            $totalRuns = MatchDelivery::where('match_id', $matchId)
                ->sum(DB::raw('runs_batsman + runs_extras'));

            $legalBalls = MatchDelivery::where('match_id', $matchId)
                ->where('delivery_type', 'normal')
                ->count();

            $overs = intdiv($legalBalls, 6) . "." . ($legalBalls % 6);

            $scoreboard = MatchScoreBoard::updateOrCreate(
                ['match_id' => $matchId, 'team_id' => $striker->team_id, 'innings' => 1],
                ['runs' => $totalRuns, 'overs' => $overs]
            );

            // 🔹 Handle wicket
            if ($extra === 'W') {
                $scoreboard->wickets += 1;
                $scoreboard->save();

                FallOfWicket::create([
                    'match_id' => $matchId,
                    'team_id'  => $striker->team_id,
                    'wicket_number' => $scoreboard->wickets,
                    'runs' => $scoreboard->runs,
                    'overs' => $scoreboard->overs,
                    'batter_id' => $strikerId,
                    'bowler_id' => $bowlerId,
                    'dismissal_type' => 'bowled', // TODO: make dynamic
                ]);

                $striker->status = 'out';
                $striker->save();
            }

            // 🔹 Partnership
            $partnership = Partnership::where('match_id', $matchId)
                ->where('team_id', $striker->team_id)
                ->latest('id')
                ->first();

            if ($partnership) {
                $partnership->runs += $runs;
                $partnership->balls += $legalBall ? 1 : 0;
                $partnership->save();
            } else {
                Partnership::create([
                    'match_id' => $matchId,
                    'team_id'  => $striker->team_id,
                    'batter_1_id' => $strikerId,
                    'batter_2_id' => $nonStrikerId,
                    'runs' => $runs,
                    'balls' => $legalBall ? 1 : 0,
                    'start_over' => $over,
                    'end_over' => $over,
                ]);
            }

            // 🔹 Update PlayerStat
            $playerStat = PlayerStat::firstOrCreate(['player_id' => $strikerId]);
            $playerStat->total_runs += $runs;
            $playerStat->balls_faced += $legalBall ? 1 : 0;
            $playerStat->save();

            // 🔹 TournamentPlayerStat
            $tournamentPlayerStat = TournamentPlayerStat::firstOrCreate([
                'tournament_id' => $striker->tournament_id ?? 1,
                'player_id' => $strikerId
            ]);
            $tournamentPlayerStat->total_runs += $runs;
            $tournamentPlayerStat->balls_faced += $legalBall ? 1 : 0;
            $tournamentPlayerStat->save();

            // 🔹 TournamentTeamStat
            $teamStat = TournamentTeamStat::firstOrCreate([
                'tournament_id' => $striker->tournament_id ?? 1,
                'team_id' => $striker->team_id
            ]);
            $teamStat->save();

            // 🔹 Swap strike if odd runs (legal balls only)
            if ($legalBall && $runs % 2 !== 0) {
                $striker->status = 'batting';
                $nonStriker->status = 'on-strike';
                $striker->save();
                $nonStriker->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Run recorded successfully',
                'updated_state' => [
                    'striker' => [
                        'id' => $striker->player_id,
                        'name' => $striker->player->user->full_name,
                        'runs' => $striker->runs_scored,
                        'balls' => $striker->balls_faced,
                        'status' => $striker->status,
                    ],
                    'nonStriker' => [
                        'id' => $nonStriker->player_id,
                        'name' => $nonStriker->player->user->full_name,
                        'status' => $nonStriker->status,
                    ],
                    'team' => [
                        'score' => $scoreboard->runs,
                        'overs' => $scoreboard->overs,
                        'wickets' => $scoreboard->wickets,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing run', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record run'
            ], 500);
        }
    }
}
