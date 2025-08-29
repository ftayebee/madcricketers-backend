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

    public function index()
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Daily Cricket Matches',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.tournaments.index'),
                        'name' => 'Daily Cricket Matches Management'
                    ]
                ]
            ]);

            $today = Carbon::today();
            $validTeams = Team::all();
            $cricketMatchesList = CricketMatch::orderBy('created_at', 'desc')->get();
            return view('admin.pages.cricket-matches.index', compact('cricketMatchesList', 'validTeams'));
        } catch (Exception $e) {
            Log::error("Error Loading cricket-matches Management", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading cricket-matches Management.'
            ]);
        }
    }

    public function tableLoader(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $matches = CricketMatch::with(['teamA', 'teamB', 'tournament', 'winningTeam'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedData = $matches->map(function ($item) {
                $viewUrl   = route('admin.cricket-matches.show', $item->id);
                $editUrl   = route('admin.cricket-matches.edit', $item->id);
                $startUrl  = route('admin.cricket-matches.start', $item->id);
                $deleteUrl = route('admin.cricket-matches.destroy', $item->id);

                return [
                    'id'             => $item->id,
                    'title'          => $item->title,
                    'team_a'         => $item->teamA ? $item->teamA->name : null,
                    'team_b'         => $item->teamB ? $item->teamB->name : null,
                    'tournament'     => $item->tournament ? $item->tournament->name : null,
                    'match_date'     => Carbon::parse($item->match_date)->format('d M, Y'),
                    'venue'          => $item->venue,
                    'match_type'     => ucfirst($item->match_type),
                    'status'         => ucfirst($item->status),
                    'max_overs'      => $item->max_overs,
                    'winning_team'   => $item->winningTeam ? $item->winningTeam->name : null,
                    'result_summary' => $item->result_summary,

                    // Permissions
                    'canView'   => Auth::user()->can($this->module . '-view'),
                    'canScore'  => Auth::user()->can($this->module . '-edit'),
                    'canEdit'   => Auth::user()->can($this->module . '-edit'),
                    'canDelete' => Auth::user()->can($this->module . '-delete'),

                    // URLs
                    'viewUrl'   => $viewUrl,
                    'startUrl'  => $startUrl,
                    'editUrl'   => $editUrl,
                    'deleteUrl' => $deleteUrl,
                ];
            });

            return response()->json(['data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Error Loading cricket matches table", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading matches table.'
            ]);
        }
    }

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

            PlayerStat::firstOrCreate(
                ['player_id' => $playerId],
                []
            );

            $activePartnership = Partnership::where('match_id', $matchId)
                ->where('team_id', $teamId)
                ->whereNull('wicket_id')
                ->latest()
                ->first();

            if (!$activePartnership) {
                Partnership::create([
                    'match_id' => $matchId,
                    'team_id' => $teamId,
                    'batter_1_id' => $playerId,
                    'runs' => 0,
                    'balls' => 0,
                    'start_over' => 0.0,
                ]);
            } else {
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
                            $user = $mp->player?->user;

                            return [
                                'id' => $mp->player_id,
                                'name' => $user?->full_name ?? 'Unknown',
                                'runs' => $mp->runs_scored ?? 0,
                                'balls' => $mp->balls_faced ?? 0,
                                'fours' => $mp->fours ?? 0,
                                'sixes' => $mp->sixes ?? 0,
                                'strike_rate' => $mp->balls_faced ? round(($mp->runs_scored / $mp->balls_faced) * 100, 2) : 0
                            ];
                        });

            // 2️⃣ Current bowling players
            $bowling = MatchPlayer::with(['player.user'])
                    ->where('match_id', $matchId)
                    ->where('team_id', $bowling_team_id)
                    ->where('overs_bowled', '>=', 0)
                    ->get()
                    ->map(function ($mp) {
                        $player = $mp->player; 
                        $user = $player?->user;

                        $oversBowled = $mp->overs_bowled ?? 0; // e.g., 1.2
                        $overPart = floor($oversBowled);
                        $ballPart = round(($oversBowled - $overPart) * 10); // extract ball part
                        $decimalOvers = $overPart + ($ballPart / 6);

                        return [
                            'id' => $mp->player_id,
                            'name' => $user?->full_name ?? 'Unknown',
                            'overs' => $mp->overs_bowled,
                            'runs_conceded' => $mp->runs_conceded,
                            'wickets' => $mp->wickets_taken,
                            'economy_rate' => $decimalOvers > 0 ? round($mp->runs_conceded / $decimalOvers, 2) : 0
                        ];
                    });

            $partnerships = Partnership::with(['batter1.user', 'batter2.user'])
                            ->where('match_id', $matchId)
                            ->get()
                            ->map(function ($p) {
                                $totalRuns = max(1, $p->player1_runs + $p->player2_runs);

                                $batter1Percent = round(($p->player1_runs / $totalRuns) * 100);
                                $batter2Percent = 100 - $batter1Percent;

                                $batter1User = $p->batter1?->user;
                                $batter2User = $p->batter2?->user;

                                return [
                                    'batter1' => [
                                        'id' => $p->batter1_id,
                                        'name' => $batter1User?->full_name ?? 'Unknown',
                                        'role' => $p->batter1 ? ucwords(str_replace('-', ' ', $p->batter1->batting_style)) : 'Unknown',
                                        'img' => $p->batter1->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                                        'runs' => $p->player1_runs,
                                        'balls' => $p->batter_1_balls ?? 0,
                                        'percent' => $batter1Percent,
                                    ],
                                    'batter2' => $p->batter2 ? [
                                        'id' => $p->batter2_id,
                                        'name' => $batter2User?->full_name ?? 'Unknown',
                                        'role' => $p->batter2 ? ucwords(str_replace('-', ' ', $p->batter2->batting_style)) : 'Unknown',
                                        'img' => $p->batter2->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                                        'runs' => $p->player2_runs,
                                        'balls' => $p->batter_2_balls ?? 0,
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
                                $batterUser = $w->batter?->user;

                                return [
                                    'player_name' => $batterUser?->full_name ?? 'Unknown',
                                    'runs' => $w->runs,
                                    'balls' => $w->balls ?? 0,
                                    'over' => $w->overs,
                                    'wicket_number' => $w->wicket_number,
                                    'dismissal_type' => $w->dismissal_type,
                                ];
                            });


            Log::info("FallOfWicket: ", ['data'=> $fallOfWickets]);

            // 5️⃣ ScoreBoard
            $matchScoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('team_id', $batting_team_id)
                ->first();

            $totalOvers = $match->max_overs;

            // Parse overs: overs may be in format 5.3 (5 overs, 3 balls)
            $oversParts = explode('.', $matchScoreboard->overs);
            $completedOvers = intval($oversParts[0]);
            $balls = isset($oversParts[1]) ? intval($oversParts[1]) : 0;
            $oversBowled = $completedOvers + ($balls / 6); // Convert balls to fractional overs

            // Current Run Rate
            $currentRate = $oversBowled > 0 ? round($matchScoreboard->runs / $oversBowled, 2) : 0;

            // Projected Score
            $projected = round($currentRate * $totalOvers);

            $finalScoreBoard = [
                'runs' => $matchScoreboard->runs,
                'wickets' => $matchScoreboard->wickets,
                'overs' => $matchScoreboard->overs,
                'totalOvers' => $totalOvers,
                'currentCRR' => $currentRate,
                'projected' => $projected,
            ];

            $currentBowler = MatchPlayer::where('status', 'bowling')
                ->where('match_id', $matchId)
                ->whereRaw('overs_bowled != FLOOR(overs_bowled)') // only partial overs
                ->first();

            $bowlerId = $currentBowler ? $currentBowler->player_id : null;

            return response()->json([
                'success' => true,
                'batting' => $batting,
                'bowling' => $bowling,
                'partnerships' => $partnerships,
                'fall_of_wickets' => $fallOfWickets,
                'innings' => $currentInnings,
                'bowling_team_id' => $bowling_team_id,
                'scoreboard' => $finalScoreBoard,
                'bowlerId' => $bowlerId
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
            Log::info("Request Received: ", ['request' => $request->all()]);

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

            // ✅ Update or create bowler in MatchPlayer
            $bowler = MatchPlayer::updateOrCreate(
                [
                    'match_id'  => $matchId,
                    'player_id' => $bowlerId
                ],
                [
                    'team_id'         => $teamId,
                    'status'          => 'bowling',
                    'overs_bowled'    => 0,
                    'runs_conceded'   => 0,
                    'wickets_taken'   => 0,
                    'maidens'         => 0,
                ]
            );

            // ✅ Ensure PlayerStat record exists
            $playerStat = PlayerStat::firstOrCreate(
                [
                    'player_id' => $bowlerId,
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
                    'strike_rate'      => 0,
                    'average'          => 0,
                    'innings_bowled'   => 0,
                    'overs_bowled'     => 0,
                    'runs_conceded'    => 0,
                    'wickets'          => 0,
                    'bowling_average'  => 0,
                    'economy_rate'     => 0,
                    'catches'          => 0,
                    'runouts'          => 0,
                    'stumpings'        => 0,
                ]
            );

            // ✅ Return current bowling lineup
            $bowling = MatchPlayer::with(['player.user'])
                ->where('match_id', $matchId)
                ->where('status', 'bowling')
                ->get()
                ->map(function ($mp) {
                    return [
                        'id'            => $mp->player_id,
                        'name'          => optional(optional($mp->player)->user)->full_name ?? 'Unknown',
                        'style'         => optional($mp->player)->bowling_style ?? 'Unknown',
                        'overs'         => $mp->overs_bowled ?? 0,
                        'runs_conceded' => $mp->runs_conceded ?? 0,
                        'wickets'       => $mp->wickets_taken ?? 0,
                        'economy_rate'  => $mp->overs_bowled > 0 
                                            ? round($mp->runs_conceded / $mp->overs_bowled, 2) 
                                            : 0,
                        'maidens'       => $mp->maidens ?? 0
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
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function getMatchInfo(Request $request)
    {
        try {
            $matchId = $request->match_id;
            $match = CricketMatch::findOrFail($matchId);

            // 🔹 Determine current innings
            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->orderByDesc('id')
                ->first();
            $currentInnings = $lastDelivery ? $lastDelivery->innings : 1;

            // 🔹 Determine batting team using toss and innings
            if ($currentInnings == 1) {
                if ($match->toss->decision === 'bat') {
                    $batting_team_id = $match->toss->toss_winner_team_id;
                } else {
                    $batting_team_id = $match->toss->toss_winner_team_id == $match->team_a_id ? $match->team_b_id : $match->team_a_id;
                }
            } else {
                if ($match->toss->decision === 'bat') {
                    $batting_team_id = $match->toss->toss_winner_team_id == $match->team_a_id ? $match->team_b_id : $match->team_a_id;
                } else {
                    $batting_team_id = $match->toss->toss_winner_team_id;
                }
            }

            // ✅ Get current scoreboard for batting team
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('team_id', $batting_team_id)
                ->first();

            $totalOvers = $match->max_overs ?? 50;

            $runs = $scoreboard->runs ?? 0;
            $wickets = $scoreboard->wickets ?? 0;
            $overs = $scoreboard->overs ?? '0.0';

            // Parse overs like 5.3 → 5 + 3/6 = 5.5
            $oversParts = explode('.', $overs);
            $completedOvers = intval($oversParts[0]);
            $balls = isset($oversParts[1]) ? intval($oversParts[1]) : 0;
            $oversBowled = $completedOvers + ($balls / 6);

            $currentCRR = $oversBowled > 0 ? round($runs / $oversBowled, 2) : 0;
            $projected = round($currentCRR * $totalOvers);

            $battingTeam = Team::find($batting_team_id);

            // Get striker and non-striker
            $striker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'on-strike')
                ->first();

            $nonStriker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'batting')
                ->first();

            $currentBowler = MatchPlayer::where('status', 'bowling')
                ->where('match_id', $matchId)
                ->whereRaw('overs_bowled != FLOOR(overs_bowled)') // only partial overs
                ->first();

            $bowlerId = $currentBowler ? $currentBowler->player_id : null;

            $matchState = [
                'striker' => $striker ? [
                    'id' => $striker->player_id,
                    'name' => $striker->player->user->full_name ?? 'Unknown',
                    'img' => $striker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                    'runs' => $striker->runs_scored ?? 0,
                    'balls' => $striker->balls_faced ?? 0,
                ] : null,
                'nonStriker' => $nonStriker ? [
                    'id' => $nonStriker->player_id,
                    'name' => $nonStriker->player->user->full_name ?? 'Unknown',
                    'img' => $nonStriker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                    'runs' => $nonStriker->runs_scored ?? 0,
                    'balls' => $nonStriker->balls_faced ?? 0,
                ] : null,
                'team' => $battingTeam ? [
                    'id' => $battingTeam->id,
                    'name' => $battingTeam->name,
                    'score' => $runs,
                    'wickets' => $wickets,
                    'overs' => $overs,
                    'totalOvers' => $totalOvers,
                    'crr' => $currentCRR,
                    'projected' => $projected,
                ] : null,
                'currentBowler' => $bowlerId
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
            Log::info("Striker Switched...");
            
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

    public function getCurrentOver($matchId)
    {
        try {
            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->orderByDesc('id')
                ->first();

            $currentOverNumber = $lastDelivery ? $lastDelivery->over_number : 1;

            $deliveries = MatchDelivery::where('match_id', $matchId)
                ->where('over_number', $currentOverNumber)
                ->orderBy('ball_in_over')
                ->get();

            $overBalls = $deliveries->map(function($d) {
                $ballLabel = '';
                $class = 'ball'; // base class

                // Determine delivery type
                switch ($d->delivery_type) {
                    case 'no-ball':
                        $class .= ' extra-ball'; // visually mark NB
                        // If batsman scored runs on NB, show them
                        if ($d->runs_batsman > 0) {
                            $ballLabel = (string) $d->runs_batsman;
                        } elseif ($d->runs_extras > 0) {
                            $ballLabel = (string) $d->runs_extras;
                        } else {
                            $ballLabel = 'NB'; // pure no-ball, no runs
                        }

                        // Append wicket if needed
                        if ($d->is_wicket) {
                            $ballLabel = $ballLabel && $ballLabel !== '0'
                                ? $ballLabel . 'W'
                                : 'W';
                        }
                        break;

                    case 'wide':
                        $class .= ' run-ball'; // WD extra-ball
                        $ballLabel = 'WD';
                        if ($d->runs_extras) $ballLabel .= $d->runs_extras;
                        if ($d->is_wicket) $ballLabel .= 'W';
                        break;

                    case 'bye':
                        $class .= ' run-ball extra-ball';
                        $ballLabel = 'B';
                        if ($d->runs_extras) $ballLabel .= $d->runs_extras;
                        if ($d->is_wicket) $ballLabel .= 'W';
                        break;

                    case 'leg-bye':
                        $class .= ' run-ball extra-ball';
                        $ballLabel = 'LB';
                        if ($d->runs_extras) $ballLabel .= $d->runs_extras;
                        if ($d->is_wicket) $ballLabel .= 'W';
                        break;

                    default:
                        // Start with runs first
                        if ($d->runs_batsman > 0) {
                            $ballLabel = (string) $d->runs_batsman;
                            $class .= $d->runs_batsman == 4 ? ' four-ball'
                                : ($d->runs_batsman == 6 ? ' six-ball' : ' run-ball');
                        } elseif ($d->runs_batsman == 0 && $d->runs_extras == 0) {
                            $ballLabel = '0';
                            $class .= ' dot-ball';
                        } else {
                            $ballLabel = (string) $d->runs_batsman;
                        }

                        // Now append wicket (if any) **after deciding runs**
                        if ($d->is_wicket) {
                            // If no runs, just "W"
                            $ballLabel = $ballLabel && $ballLabel !== '0'
                                ? $ballLabel . 'W'
                                : 'W';
                        }
                        break;
                }

                return [
                    'ball' => $ballLabel,
                    'class' => $class
                ];
            });

            return response()->json([
                'success' => true,
                'current_over' => $currentOverNumber,
                'balls' => $overBalls
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching current over', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch current over.']);
        }
    }


    public function storeDelivery(Request $request)
    {
        try {
            Log::info('Request Received: ', ['request' => $request->all()]);
            $validator = Validator::make($request->all(), [
                'match_id'       => 'required|exists:cricket_matches,id',
                'striker_id'     => 'nullable|exists:players,id',
                'non_striker_id' => 'nullable|exists:players,id',
                'bowler_id'      => 'required|exists:players,id',
                'runs'           => 'required|integer|min:0',
                'extras'         => 'nullable|array',
                'extras.type'    => 'required_with:extras|string',
                'extras.runs'    => 'required_with:extras|integer|min:0',
                'extras.run_out' => 'required_with:extras|boolean',
                'wicket'         => 'nullable|string',
                'legal_ball'     => 'nullable|boolean'
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
            $extras       = $request->extras ?? [];
            $wicketType   = $request->wicket ?? null;
            $legalBall    = $request->has('legal_ball') ? $request->legal_ball : true;

            $match = CricketMatch::findOrFail($matchId);

            // 🔹 Fetch/Create striker & non-striker
            $striker = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $strikerId],
                ['runs_scored' => 0, 'balls_faced' => 0, 'status' => 'on-strike']
            );

            $nonStriker = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $nonStrikerId],
                ['runs_scored' => 0, 'balls_faced' => 0, 'status' => 'batting']
            );

            $bowlerInfo = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $bowlerId],
                ['wickets_taken' => 0, 'overs_bowled' => 0, 'runs_conceded' => 0]
            );

            // 🔹 Determine delivery type
            $deliveryType = 'normal';
            $extraRuns = 0;
            $runOut = false;
            $wicketPlayerId = null;

            if (!empty($extras)) {
                $type = strtoupper($extras['type'] ?? '');

                switch ($type) {
                    case 'NB':
                        $deliveryType = 'no-ball';
                        $legalBall = false;
                        if (!empty($extras['batsman_out'])) {
                            $wicketPlayerId = $extras['batsman_out'];
                        } elseif (!empty($request->batsman_out)) {
                            $wicketPlayerId = $request->batsman_out;
                        }
                        break;
                    case 'WD':
                        $deliveryType = 'wide';
                        $legalBall = false;
                        break;
                    case 'LB':
                        $deliveryType = 'leg-bye';
                        break;
                    case 'B':
                        $deliveryType = 'bye';
                        break;
                    default:
                        $deliveryType = 'normal';
                }

                $extraRuns = $extras['runs'] ?? 0;
                $runOut    = $extras['run_out'] ?? false;

            }

            $validWickets = ['bowled','caught','lbw','run_out','stumped','hit_wicket','retired_hurt','none'];
            $wicketType = 'none';

            // Determine wicket type
            if ($runOut && !empty($extras['batsman_out'])) {
                $wicketPlayerId = $extras['batsman_out'];
                $wicketType = 'run_out';
            } elseif (!in_array(strtolower($wicketType ?? 'none'), $validWickets)) {
                $wicketType = 'none';
            } else {
                $wicketType = strtolower($wicketType);
            }

            // Set is_wicket flag
            $isWicket = $wicketType !== 'none';

            // 🔹 Get last delivery
            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->latest('id')
                ->first();

            $over = $lastDelivery?->over_number ?? 1;
            $ball = $lastDelivery?->ball_in_over ?? 0;

            if ($legalBall) {
                if ($ball == 6) {
                    $over++;
                    $ball = 1;
                } else {
                    $ball++;
                }
            }

            // 🔹 Determine batting & bowling teams using toss
            $currentInnings = $lastDelivery ? $lastDelivery->innings : 1;
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

            // 🔹 Insert delivery
            $deliveryPayload = [
                'match_id'        => $matchId,
                'innings'         => 1,
                'over_number'     => $over,
                'ball_in_over'    => $ball,
                'bowler_id'       => $bowlerId,
                'batsman_id'      => $strikerId,
                'non_striker_id'  => $nonStrikerId,
                'batting_team_id' => $striker->team_id,
                'bowling_team_id' => $bowling_team_id,
                'runs_batsman'    => $runs,
                'runs_extras'     => $legalBall ? 0 : $extraRuns,
                'delivery_type'   => $deliveryType,
                'is_wicket'       => $isWicket,
                'wicket_type'     => $wicketType,
                'wicket_player_id'=> $isWicket ? $wicketPlayerId : null,
            ];
            
            $delivery = MatchDelivery::create($deliveryPayload);

            // 🔹 Update striker stats
            if ($legalBall) $striker->balls_faced += 1;
            $striker->runs_scored += $runs;
            $striker->save();

            // 🔹 Update bowler stats (overs in over.ball format)
            $overPart = floor($bowlerInfo->overs_bowled);       // completed overs
            $ballPart = round(($bowlerInfo->overs_bowled - $overPart) * 10); // balls in current over

            if ($legalBall) {
                $ballPart++; // increment by 1 ball only if legal

                if ($ballPart > 5) { // after 6 balls, increment over
                    $overPart++;
                    $ballPart = 0;
                }
            }

            $bowlerInfo->overs_bowled = $overPart + ($ballPart / 10);
            $bowlerInfo->runs_conceded += $runs + ((in_array('NB', $extras) || in_array('WD', $extras)) ? 1 : 0);

            // wickets taken
            if ($wicketType != 'run_out') {
                $bowlerInfo->wickets_taken += 1;
            }

            $bowlerInfo->save();

            // 🔹 Update scoreboard
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
            if ($isWicket) {
                $scoreboard->wickets += 1;
                $scoreboard->save();

                FallOfWicket::create([
                    'match_id'      => $matchId,
                    'team_id'       => $striker->team_id,
                    'wicket_number' => $scoreboard->wickets,
                    'runs'          => $scoreboard->runs,
                    'overs'         => $scoreboard->overs,
                    'batter_id'     => $strikerId,
                    'bowler_id'     => $bowlerId,
                    'dismissal_type' => $wicketType
                ]);

                $striker->status = $wicketType == 'run_out' ? 'run_out' : 'bowled';
                $striker->save();
            }

            // 🔹 Update partnership
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
                    'match_id'     => $matchId,
                    'team_id'      => $striker->team_id,
                    'batter_1_id'  => $strikerId,
                    'player1_runs' => $striker->runs_scored,
                    'batter_2_id'  => $nonStrikerId,
                    'player2_runs' => $nonStriker->runs_scored,
                    'runs'         => $runs,
                    'balls'        => $legalBall ? 1 : 0,
                    'start_over'   => $over,
                    'end_over'     => $over,
                ]);
            }

            // 🔹 Update player stats
            $playerStat = PlayerStat::firstOrCreate(['player_id' => $strikerId]);
            $playerStat->total_runs += $runs;
            $playerStat->balls_faced += $legalBall ? 1 : 0;
            $playerStat->save();

            // 🔹 Swap strike if needed
            if ($legalBall && $runs % 2 !== 0) {
                $striker->status = 'batting';
                $nonStriker->status = 'on-strike';
                $striker->save();
                $nonStriker->save();
            }

            // ✅ Over completed - Switch Strike
            $ballInOver = $delivery->ball_in_over;
            $runs = $delivery->runs_batsman;
            $isWicket = $delivery->is_wicket;

            if ($ballInOver == 6) {
                if (!in_array($runs, [1,3])) {
                    $this->switchStrike($matchId);
                }
            }

            // ✅ If wicket or dot on last ball, still switch
            if ($ballInOver == 6 && ($isWicket || $runs == 0)) {
                $this->switchStrike($matchId);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery recorded successfully',
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
            Log::error('Error storing delivery', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record delivery'
            ], 500);
        }
    }
}
