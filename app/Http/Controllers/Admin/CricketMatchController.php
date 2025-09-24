<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\Player;
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

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            $validator = Validator::make($request->all(), [
                'title'      => 'nullable|string|max:255',
                'venue'      => 'nullable|string|max:255',
                'team_a_id'  => 'required|exists:teams,id|different:team_b_id',
                'team_b_id'  => 'required|exists:teams,id|different:team_a_id',
                'match_date' => 'nullable|date',
                'max_overs'  => 'nullable|integer|min:1',
                'match_type' => 'required|in:tournament,regular',
                'status'     => 'required|in:live,upcoming,completed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();

            $teamA = Team::findOrFail($request->team_a_id);
            $teamB = Team::findOrFail($request->team_b_id);

            $matchTitle = $request->title ?: $teamA->name . ' vs ' . $teamB->name;

            $match             = new CricketMatch();
            $match->title      = $matchTitle;
            $match->venue      = $request->venue;
            $match->team_a_id  = $request->team_a_id;
            $match->team_b_id  = $request->team_b_id;
            $match->match_date = $request->match_date;
            $match->max_overs  = $request->max_overs;
            $match->match_type = $request->match_type;
            $match->status     = $request->status;
            $match->save();

            DB::commit();

            return redirect()->route('admin.cricket-matches.index')->with([
                'success' => true,
                'message' => 'Match created successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error storing cricket match", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->withInput()->with([
                'success' => false,
                'message' => 'An error occurred while creating the match.',
                'error'   => $e->getMessage(),
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

    public function viewScoreBoard($id){
        try {
            if (!Auth::user()->can($this->module . '-start')) {
                throw new Exception('Unauthorized Access');
            }

            $match = CricketMatch::with(['teamA', 'teamB', 'tournament'])->findOrFail($id);

            return view('admin.pages.cricket-matches.scoreboard', compact('match'))->with([
                'success' => true,
                'message' => 'Match Scoreboard Showing.',
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

            return response()->json([
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
            $existingBatsmen = MatchPlayer::where('match_id', $matchId)->where('team_id', $teamId)->count();
            $status = $existingBatsmen === 0 ? 'on-strike' : $role;

            $matchPlayer = MatchPlayer::firstOrCreate(
                [
                    'match_id'  => $matchId,
                    'team_id'   => $teamId,
                    'player_id' => $playerId,
                ],
                [
                    'runs_scored'    => 0,
                    'balls_faced'    => 0,
                    'wickets_taken'  => 0,
                    'overs_bowled'   => 0,
                    'status'         => $status,
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

            $player2Id = null;

            if (!$activePartnership) {
                // 🔹 Find the last partnership that ended with a wicket
                $lastPartnership = Partnership::where('match_id', $matchId)
                    ->where('team_id', $teamId)
                    ->whereNotNull('wicket_id')
                    ->latest()
                    ->first();

                if ($lastPartnership) {
                    $fallOfWicket = FallOfWicket::find($lastPartnership->wicket_id);

                    if ($fallOfWicket) {
                        // One of these survived (not equal to the wicket batter)
                        if ($lastPartnership->batter_1_id && $lastPartnership->batter_1_id != $fallOfWicket->batter_id) {
                            $player2Id = $lastPartnership->batter_1_id;
                        } elseif ($lastPartnership->batter_2_id && $lastPartnership->batter_2_id != $fallOfWicket->batter_id) {
                            $player2Id = $lastPartnership->batter_2_id;
                        }
                    }
                }

                if ($player2Id) {
                    Partnership::create([
                        'match_id'    => $matchId,
                        'team_id'     => $teamId,
                        'batter_1_id' => $player2Id,
                        'batter_2_id' => $playerId,
                        'runs'        => 0,
                        'balls'       => 0,
                        'start_over'  => 0.0,
                    ]);
                }
            } else {
                if (!$activePartnership->batter_2_id) {
                    $activePartnership->update(['batter_2_id' => $playerId]);
                }
            }

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

    public function selectBowler(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'match_id'  => 'required|exists:cricket_matches,id',
                'team_id'   => 'required|exists:teams,id', // batting team id from frontend
                'player_id' => 'required|exists:players,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to select bowler.',
                    'errors'  => $validator->errors()
                ]);
            }

            $matchId       = $request->match_id;
            $battingTeamId = $request->team_id;
            $playerId      = $request->player_id;

            // 🔹 Get the match scoreboard
            $scoreboard = MatchScoreboard::where('match_id', $matchId)->get();

            if ($scoreboard->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scoreboard not found for this match.',
                ], 404);
            }

            // 🔹 Determine bowling team (the one that is not batting and has ended)
            $bowlingTeamId = $scoreboard
                ->where('team_id', '!=', $battingTeamId)
                ->where('status', 'ended')
                ->first()?->team_id;

            if (!$bowlingTeamId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bowling team not determined.',
                ], 400);
            }

            // 🔹 Get all existing MatchPlayer records for the bowling team in this match
            $existingPlayers = MatchPlayer::where('match_id', $matchId)
                ->where('team_id', $bowlingTeamId)
                ->get();

            foreach ($existingPlayers as $player) {
                $status = $player->player_id == $playerId ? 'bowling' : 'fielding';

                $player->update([
                    'status' => $status,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bowler selected successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("Error selecting bowler", [
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to select bowler.',
            ], 500);
        }
    }

    public function getMatchInfo(Request $request)
    {
        try {
            $matchId = $request->match_id;
            $match = CricketMatch::findOrFail($matchId);

            // Try to get the running scoreboard
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('status', 'running')
                ->first();

            if (!$scoreboard) {
                // No running scoreboard → match might be completed
                // Use latest scoreboard to show final stats
                $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                    ->orderByDesc('innings')
                    ->first();

                if (!$scoreboard) {
                    // No scoreboard at all
                    return response()->json([
                        'success' => false,
                        'message' => 'No scoreboards found for this match.'
                    ], 404);
                }
            }

            $currentInnings = $scoreboard->innings ?? 1;
            $batting_team_id = $scoreboard->team_id;

            $bowling_scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('team_id', '!=', $batting_team_id)
                ->first();

            $bowling_team_id = $bowling_scoreboard ? $bowling_scoreboard->team_id : null;

            $totalOvers = $match->max_overs ?? 50;
            $runs = $scoreboard->runs ?? 0;
            $wickets = $scoreboard->wickets ?? 0;
            $overs = $scoreboard->overs ?? '0.0';

            $oversParts = explode('.', $overs);
            $completedOvers = intval($oversParts[0]);
            $balls = isset($oversParts[1]) ? intval($oversParts[1]) : 0;
            $oversBowled = $completedOvers + ($balls / 6);

            $currentCRR = $oversBowled > 0 ? round($runs / $oversBowled, 2) : 0;
            $projected = round($currentCRR * $totalOvers);

            $battingTeam = Team::find($batting_team_id);

            $striker = MatchPlayer::where('match_id', $matchId)
                ->where('team_id', $batting_team_id)
                ->where('status', 'on-strike')
                ->first();

            $nonStriker = MatchPlayer::where('match_id', $matchId)
                ->where('team_id', $batting_team_id)
                ->where('status', 'batting')
                ->first();

            $currentBowler = $bowling_team_id
                ? MatchPlayer::where('match_id', $matchId)
                ->where('status', 'bowling')
                ->where('team_id', $bowling_team_id)
                ->orderByDesc('overs_bowled')
                ->first()
                : null;

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
                'currentBowler' => $bowlerId,
                'battingTeamId' => $batting_team_id,
                'bowlingTeamId' => $bowling_team_id,
                'currentInnings' => $currentInnings
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


    public function loadCurrentStats(Request $request)
    {
        try {
            $matchId = $request->match_id;
            $match   = CricketMatch::findOrFail($matchId);

            // Load all scoreboards for the match, ordered by innings
            $scoreboards = MatchScoreBoard::where('match_id', $matchId)
                ->orderBy('innings')
                ->get();

            $allInnings = [];

            // If scoreboards exist, map their data
            if ($scoreboards->isNotEmpty()) {
                foreach ($scoreboards as $scoreboard) {
                    $inningsNo      = $scoreboard->innings;
                    $batting_team_id = $scoreboard->team_id;
                    $bowling_team_id = ($batting_team_id == $match->team_a_id) ? $match->team_b_id : $match->team_a_id;

                    // Batting
                    $batting = MatchPlayer::with('player.user')
                        ->where('match_id', $matchId)
                        ->where('team_id', $batting_team_id)
                        ->get()
                        ->map(function ($mp) {
                            $user = $mp->player?->user;
                            return [
                                'id'          => $mp->player_id,
                                'name'        => $user?->full_name ?? 'Unknown',
                                'runs'        => $mp->runs_scored ?? 0,
                                'balls'       => $mp->balls_faced ?? 0,
                                'fours'       => $mp->fours ?? 0,
                                'sixes'       => $mp->sixes ?? 0,
                                'status'      => $mp->status ?? '---',
                                'strike_rate' => $mp->balls_faced
                                    ? round(($mp->runs_scored / $mp->balls_faced) * 100, 2)
                                    : 0,
                            ];
                        });

                    // Bowling
                    $bowling = MatchPlayer::with('player.user')
                        ->where('match_id', $matchId)
                        ->where('team_id', $bowling_team_id)
                        ->where('overs_bowled', '>=', 0)
                        ->get()
                        ->map(function ($mp) {
                            $user = $mp->player?->user;
                            $oversBowled = $mp->overs_bowled ?? 0;
                            $overPart    = floor($oversBowled);
                            $ballPart    = round(($oversBowled - $overPart) * 10);
                            $decimalOvers = $overPart + ($ballPart / 6);

                            return [
                                'id'           => $mp->player_id,
                                'name'         => $user?->full_name ?? 'Unknown',
                                'overs'        => $mp->overs_bowled,
                                'runs_conceded' => $mp->runs_conceded,
                                'wickets'      => $mp->wickets_taken,
                                'economy_rate' => $decimalOvers > 0
                                    ? round($mp->runs_conceded / $decimalOvers, 2)
                                    : 0,
                            ];
                        });

                    // Partnerships
                    $partnerships = Partnership::with(['batter1.user', 'batter2.user'])
                        ->where('match_id', $matchId)
                        ->where('team_id', $batting_team_id)
                        ->where('innings', $inningsNo)
                        ->get()
                        ->map(function ($p) {
                            $totalRuns = max(1, $p->player1_runs + $p->player2_runs);
                            $batter1Percent = round(($p->player1_runs / $totalRuns) * 100);
                            $batter2Percent = 100 - $batter1Percent;

                            return [
                                'batter1' => [
                                    'id'      => $p->batter_1_id,
                                    'name'    => $p->batter1?->user?->full_name ?? 'Unknown',
                                    'role'    => strtoupper(str_replace('-', ' ', $p->batter1?->batting_style)) ?? 'Unknown',
                                    'runs'    => $p->player1_runs,
                                    'balls'   => $p->batter_1_balls ?? 0,
                                    'percent' => $batter1Percent,
                                    'img'     => $p->batter1?->user?->image ?? 'https://coffective.com/wp-content/uploads/2018/06/default-featured-image.png.jpg',
                                ],
                                'batter2' => $p->batter2 ? [
                                    'id'      => $p->batter_2_id,
                                    'name'    => $p->batter2?->user?->full_name ?? 'Unknown',
                                    'role'    => strtoupper(str_replace('-', ' ', $p->batter2?->batting_style)) ?? 'Unknown',
                                    'runs'    => $p->player2_runs,
                                    'balls'   => $p->batter_2_balls ?? 0,
                                    'percent' => $batter2Percent,
                                    'img'     => $p->batter2?->user?->image ?? 'https://coffective.com/wp-content/uploads/2018/06/default-featured-image.png.jpg',
                                ] : null,
                                'runs'      => $p->runs,
                                'balls'     => $p->balls,
                                'start_over' => $p->start_over,
                                'end_over'  => $p->end_over,
                            ];
                        });

                    // Fall of wickets
                    $fallOfWickets = FallOfWicket::with('batter.user')
                        ->where('match_id', $matchId)
                        ->where('team_id', $batting_team_id)
                        ->where('innings', $inningsNo)
                        ->orderBy('wicket_number')
                        ->get()
                        ->map(function ($w) {
                            return [
                                'player_name'   => $w->batter?->user?->full_name ?? 'Unknown',
                                'runs'          => $w->runs,
                                'over'          => $w->overs,
                                'wicket_number' => $w->wicket_number,
                                'dismissal_type' => $w->dismissal_type,
                            ];
                        });

                    // Scoreboard calculations
                    $totalOvers = $match->max_overs;
                    $oversParts = explode('.', $scoreboard->overs ?? '0.0');
                    $completedOvers = intval($oversParts[0]);
                    $balls = isset($oversParts[1]) ? intval($oversParts[1]) : 0;
                    $oversBowled = $completedOvers + ($balls / 6);

                    $currentRate = $oversBowled > 0
                        ? round($scoreboard->runs / $oversBowled, 2)
                        : 0;
                    $projected = round($currentRate * $totalOvers);

                    $targetScore = 0;
                    $requiredRunRate = 0;

                    if ($inningsNo == 2) {
                        $firstInnings = $scoreboards->where('innings', 1)->first();
                        if ($firstInnings) {
                            $targetScore = $firstInnings->runs + 1;
                            $oversLeft = $totalOvers - $oversBowled;
                            $requiredRunRate = $oversLeft > 0
                                ? round(($targetScore - $scoreboard->runs) / $oversLeft, 2)
                                : 0;
                        }
                    }

                    $allInnings[] = [
                        'innings'        => $inningsNo,
                        'batting_team_id' => $batting_team_id,
                        'bowling_team_id' => $bowling_team_id,
                        'scoreboard'     => [
                            'runs'        => $scoreboard->runs ?? 0,
                            'wickets'     => $scoreboard->wickets ?? 0,
                            'overs'       => $scoreboard->overs ?? '0.0',
                            'totalOvers'  => $totalOvers,
                            'currentCRR'  => $currentRate,
                            'projected'   => $projected,
                            'target'      => $targetScore,
                            'requiredRR'  => $requiredRunRate,
                        ],
                        'batting'        => $batting,
                        'bowling'        => $bowling,
                        'partnerships'   => $partnerships,
                        'fall_of_wickets' => $fallOfWickets,
                    ];
                }
            }

            // Always return match_result even if scoreboards are empty
            Log::info($match->status);
            return response()->json([
                'success' => true,
                'match_id' => $matchId,
                'innings' => $allInnings,
                'match_result' => $match->status === 'completed' ? [
                    'winning_team_id' => $match->winning_team_id,
                    'winning_team'    => $match->winningTeam?->name ?? 'Unknown',
                    'summary'         => $match->result_summary ?? '',
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading full match stats', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load match stats.'
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

    public function getCurrentOver($matchId)
    {
        try {
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)->where('status', 'running')->first();

            if (!$scoreboard) {
                return response()->json([
                    'success' => true,
                    'current_over' => null,
                    'balls' => []
                ]);
            }

            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->where('innings', $scoreboard->innings)
                ->orderByDesc('id')
                ->first();

            $currentOverNumber = $lastDelivery ? $lastDelivery->over_number : 1;

            $deliveries = MatchDelivery::where('match_id', $matchId)
                ->where('over_number', $currentOverNumber)
                ->where('innings', $scoreboard->innings)
                ->orderBy('ball_in_over')
                ->get();

            $overBalls = $deliveries->map(function ($d) {
                $ballLabel = '';
                $class = 'ball'; // base class

                // Determine delivery type
                switch ($d->delivery_type) {
                    case 'no-ball':
                        $class .= ' extra-ball';
                        if ($d->runs_batsman > 0) {
                            $ballLabel = (string) $d->runs_batsman;
                        } elseif ($d->runs_extras > 0) {
                            $ballLabel = (string) $d->runs_extras;
                        } else {
                            $ballLabel = 'NB'; // pure no-ball, no runs
                        }

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
            $strikeSwitched = $this->doSwitchStrike($matchId);

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

            if (!$strikeSwitched) {
                return response()->json([
                    'success' => false,
                    'message' => 'Strike switch failed.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Strike switched successfully.',
                'data' => [
                    'striker' => [
                        'id' => $striker->player_id,
                        'name' => $striker->player->user->full_name,
                        'img' => $striker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                        'runs' => $striker->runs_scored ?? 0,
                        'balls' => $striker->balls_faced ?? 0,
                    ],
                    'nonStriker' => [
                        'id' => $nonStriker->player_id,
                        'name' => $nonStriker->player->user->full_name,
                        'img' => $nonStriker->player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                        'runs' => $nonStriker->runs_scored ?? 0,
                        'balls' => $nonStriker->balls_faced ?? 0,
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

    private function doSwitchStrike($matchId)
    {
        $striker = MatchPlayer::where('match_id', $matchId)
            ->where('status', 'on-strike')
            ->first();

        $nonStriker = MatchPlayer::where('match_id', $matchId)
            ->where('status', 'batting')
            ->first();

        // 🔹 If not found in normal orientation, try reversed
        if (!$striker || !$nonStriker) {
            $striker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'batting')
                ->first();

            $nonStriker = MatchPlayer::where('match_id', $matchId)
                ->where('status', 'on-strike')
                ->first();
        }

        if (!$striker || !$nonStriker) {
            return false;
        }

        // 🔹 Swap
        $strikerStatus = $striker->status;
        $nonStrikerStatus = $nonStriker->status;

        $striker->status = $nonStrikerStatus;
        $nonStriker->status = $strikerStatus;

        $striker->save();
        $nonStriker->save();

        return true;
    }

    public function switchTeam($match, $scoreboard)
    {
        $scoreboard->update(['status' => 'ended']);

        $currentBattingTeamId = $scoreboard->team_id;
        $nextBattingTeamId = ($currentBattingTeamId == $match->team_a_id)
            ? $match->team_b_id
            : $match->team_a_id;

        $nextInnings = $scoreboard->innings + 1;

        $nextScoreboard = MatchScoreBoard::updateOrCreate(
            ['match_id' => $match->id, 'innings' => $nextInnings],
            [
                'team_id' => $nextBattingTeamId,
                'runs'    => 0,
                'overs'   => '0.0',
                'wickets' => 0,
                'status'  => 'running',
            ]
        );

        return $nextScoreboard;
    }

    public function setInningsStatus(Request $request)
    {
        try {
            $matchId = $request->match_id;
            $match   = CricketMatch::findOrFail($matchId);

            // 🔹 Find active scoreboard
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('status', 'running')
                ->first();

            if (!$scoreboard) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active innings found.'
                ], 404);
            }

            $currentInnings = $scoreboard->innings;
            $scoreboard->update(['status' => 'ended']);

            $battingTeamId = $scoreboard->team_id;
            $bowlingTeamId = ($battingTeamId == $match->team_a_id)
                ? $match->team_b_id
                : $match->team_a_id;

            $nextBattingTeamId = $bowlingTeamId;
            $nextInnings = $currentInnings + 1;

            // 🔹 Create or update next innings scoreboard
            $nextScoreboard = MatchScoreBoard::updateOrCreate(
                ['match_id' => $matchId, 'innings' => $nextInnings],
                [
                    'team_id' => $nextBattingTeamId,
                    'runs'    => 0,
                    'overs'   => '0.0',
                    'wickets' => 0,
                    'status'  => 'running'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Innings $currentInnings completed. Innings $nextInnings started.",
                'isInningsEnded' => true,
                'innings' => $nextInnings,
                'scoreboard' => $nextScoreboard
            ]);
        } catch (\Exception $e) {
            Log::error('Error completing innings', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete innings.'
            ], 500);
        }
    }

    public function storeDelivery(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'match_id'           => 'required|exists:cricket_matches,id',
                'striker_id'         => 'nullable|exists:players,id',
                'non_striker_id'     => 'nullable|exists:players,id',
                'bowler_id'          => 'required|exists:players,id',
                'runs'               => 'required|integer|min:0',
                'extras'             => 'nullable|array',
                'extras.type'        => 'required_with:extras|string',
                'extras.runs'        => 'required_with:extras|integer|min:0',
                'extras.run_out'     => 'sometimes|boolean',
                'extras.batsman_out' => 'sometimes|exists:players,id',
                'extras.caught_by'   => 'sometimes|exists:players,id',
                'extras.stumped_by'  => 'sometimes|exists:players,id',
                'wicket'             => 'nullable|string',
                'batsman_out'        => 'nullable|exists:players,id',
                'legal_ball'         => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data',
                    'errors'  => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // ---------- Extract request ----------
            $matchId      = $request->match_id;
            $strikerId    = $request->striker_id;
            $nonStrikerId = $request->non_striker_id;
            $bowlerId     = $request->bowler_id;
            $runs         = (int) $request->runs;
            $extras       = $request->extras ?? [];
            $legalBall    = $request->has('legal_ball') ? (bool) $request->legal_ball : true;

            $match = CricketMatch::findOrFail($matchId);
            $maxOvers = $match->max_overs ?? 10;

            // ---------- Find active innings scoreboard ----------
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('status', 'running')
                ->firstOrFail();

            $innings = $scoreboard->innings;

            // ---------- Ensure match players exist (if ids provided) ----------
            $striker = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $strikerId, 'team_id' => $scoreboard->team_id],
                ['runs_scored' => 0, 'balls_faced' => 0, 'status' => 'on-strike']
            );

            $nonStriker = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $nonStrikerId, 'team_id' => $scoreboard->team_id],
                ['runs_scored' => 0, 'balls_faced' => 0, 'status' => 'batting']
            );

            $bowlingTeamId = ($scoreboard->team_id == $match->team_a_id) ? $match->team_b_id : $match->team_a_id;

            $bowlerInfo = MatchPlayer::firstOrCreate(
                ['match_id' => $matchId, 'player_id' => $bowlerId, 'team_id' => $bowlingTeamId],
                ['wickets_taken' => 0, 'overs_bowled' => 0, 'runs_conceded' => 0, 'status' => 'bowling']
            );

            // ---------- Delivery type & extras ----------
            $deliveryType = 'normal';
            $extraRuns = 0;
            if (!empty($extras)) {
                $type = strtoupper($extras['type'] ?? '');
                switch ($type) {
                    case 'NB':
                        $deliveryType = 'no-ball';
                        $legalBall = false;
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
                $extraRuns = (int) ($extras['runs'] ?? 0);
            }

            // ---------- Wicket detection ----------
            $wicketType = 'none';
            $wicketPlayerId = null;
            $caughtBy = $extras['caught_by'] ?? null;
            $stumpedBy = $extras['stumped_by'] ?? null;

            if (!empty($request->wicket) && strtolower($request->wicket) !== 'none') {
                $wicketType = strtolower($request->wicket);
                $wicketPlayerId = $request->batsman_out ?? $strikerId;
            } elseif (!empty($extras['run_out']) && $extras['run_out']) {
                $wicketType = 'run_out';
                $wicketPlayerId = $request->batsman_out ?? ($extras['batsman_out'] ?? null);
            }

            $isWicket = $wicketType !== 'none';

            // ---------- Determine next over/ball (for this innings) ----------
            $lastDelivery = MatchDelivery::where('match_id', $matchId)
                ->where('innings', $innings)
                ->latest('id')
                ->first();

            // default start at over 1 ball 0 -> increment to ball 1 on first legal ball
            $over = $lastDelivery?->over_number ?? 0;
            $ball = $lastDelivery?->ball_in_over ?? 0;

            if ($legalBall) {
                if ($ball == 6) {
                    $over++;
                    $ball = 1;
                } else {
                    $ball++;
                    if ($over == 0) $over = 1;
                }
            } else {
                if ($over == 0) {
                    $over = 1;
                    $ball = 0;
                }
            }

            $legalBallsSoFar = MatchDelivery::where('match_id', $matchId)
                ->where('innings', $innings)
                ->where('delivery_type', 'normal')
                ->count();

            $legalBallsAfterThis = $legalBallsSoFar + ($legalBall ? 1 : 0);
            if ($legalBallsAfterThis > ($maxOvers * 6)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Innings is complete. No more deliveries allowed.'
                ], 400);
            }

            // ---------- Insert delivery ----------
            $deliveryPayload = [
                'match_id'         => $matchId,
                'innings'          => $innings,
                'over_number'      => $over,
                'ball_in_over'     => $ball,
                'bowler_id'        => $bowlerId,
                'batsman_id'       => $strikerId,
                'non_striker_id'   => $nonStrikerId,
                'batting_team_id'  => $scoreboard->team_id,
                'bowling_team_id'  => $bowlerInfo->team_id ?? null,
                'runs_batsman'     => $runs,
                'runs_extras'      => $extraRuns,
                'delivery_type'    => $deliveryType,
                'is_wicket'        => $isWicket,
                'wicket_type'      => $wicketType,
                'wicket_player_id' => $isWicket ? $wicketPlayerId : null,
                'caught_by'        => $caughtBy,
                'stumped_by'       => $stumpedBy,
            ];

            $delivery = MatchDelivery::create($deliveryPayload);

            // ---------- Update striker & bowler stats ----------
            if ($legalBall) $striker->balls_faced += 1;
            $striker->runs_scored += $runs;
            $striker->save();

            // update bowler overs (store as over.ball decimal)
            $overPart  = floor($bowlerInfo->overs_bowled); // completed overs
            $ballPart  = round(($bowlerInfo->overs_bowled - $overPart) * 10); // balls in current over

            if ($legalBall) {
                $ballPart++; // add this ball
                if ($ballPart >= 6) {
                    $overPart++;
                    $ballPart = 0; // reset ball to 0, NOT 1
                }
            }

            $bowlerInfo->overs_bowled = $overPart + ($ballPart / 10);
            $bowlerInfo->runs_conceded += $runs + (in_array($deliveryType, ['no-ball', 'wide']) ? 1 : 0);
            if ($isWicket && $wicketType != 'run_out') {
                $bowlerInfo->wickets_taken++;
            }
            $bowlerInfo->save();

            // ---------- Update scoreboard totals ----------
            $totalRuns = MatchDelivery::where('match_id', $matchId)
                ->where('innings', $innings)
                ->sum(DB::raw('runs_batsman + runs_extras'));

            $legalBalls = MatchDelivery::where('match_id', $matchId)
                ->where('innings', $innings)
                ->where('delivery_type', 'normal')
                ->count();

            $oversFormatted = intval(intdiv($legalBalls, 6)) . '.' . ($legalBalls % 6);

            $scoreboard->update([
                'runs'  => $totalRuns,
                'overs' => $oversFormatted,
            ]);

            // ---------- Partnership logic (update player1/player2 runs correctly) ----------
            $partnership = Partnership::where('match_id', $matchId)
                ->where('team_id', $scoreboard->team_id)
                ->whereNull('wicket_id')
                ->latest('id')
                ->first();

            if ($partnership) {
                $partnership->runs += $runs;
                $partnership->balls += $legalBall ? 1 : 0;
                $partnership->player1_runs += ($partnership->batter_1_id == $strikerId) ? $runs : 0;
                $partnership->player2_runs += ($partnership->batter_2_id == $strikerId) ? $runs : 0;
                $partnership->save();
            } else {
                Partnership::create([
                    'match_id'     => $matchId,
                    'team_id'      => $scoreboard->team_id,
                    'batter_1_id'  => $strikerId,
                    'player1_runs' => ($striker->runs_scored ?? 0),
                    'batter_2_id'  => $nonStrikerId,
                    'player2_runs' => ($nonStriker->runs_scored ?? 0),
                    'runs'         => $runs,
                    'balls'        => $legalBall ? 1 : 0,
                    'start_over'   => $over,
                    'end_over'     => $over,
                ]);
            }

            // ---------- Handle wicket & FallOfWicket ----------
            if ($isWicket && $wicketPlayerId) {
                $scoreboard->increment('wickets');

                $fow = FallOfWicket::create([
                    'match_id'       => $matchId,
                    'team_id'        => $scoreboard->team_id,
                    'wicket_number'  => $scoreboard->wickets,
                    'runs'           => $scoreboard->runs,
                    'overs'          => $scoreboard->overs,
                    'batter_id'      => $wicketPlayerId,
                    'bowler_id'      => $bowlerId,
                    'dismissal_type' => $wicketType,
                    'caught_by'      => $caughtBy,
                    'stumped_by'     => $stumpedBy,
                ]);

                // update dismissed player status
                if ($wicketPlayerId == $strikerId) {
                    $striker->status = $wicketType;
                    $striker->save();
                } elseif ($wicketPlayerId == $nonStrikerId) {
                    $nonStriker->status = $wicketType;
                    $nonStriker->save();
                }

                if ($partnership) {
                    $partnership->wicket_id = $fow->id;
                    $partnership->save();
                }
            }

            // ---------- Strike rotation ----------
            if ($legalBall && ($runs % 2 !== 0)) {
                $striker->status = 'batting';
                $nonStriker->status = 'on-strike';
                $striker->save();
                $nonStriker->save();
            }

            // If end of over (6th legal ball), call switch (this will update DB state)
            if ($legalBall && $ball == 6) {
                $this->doSwitchStrike($matchId);
            }

            // ---------- Final ball of last over => end innings & create next scoreboard ----------
            $wasLastLegalBallOfInnings = $legalBall && ($over == $maxOvers) && ($ball == 6);
            $isInningsEnded = false;

            if ($wasLastLegalBallOfInnings || ($isWicket && $scoreboard->wickets >= 10)) {
                $this->switchTeam($match, $scoreboard);
                $isInningsEnded = true;

                // 🔹 Check if match is finished (after 2 innings)
                $completedInnings = MatchScoreBoard::where('match_id', $matchId)
                    ->whereIn('status', ['ended'])
                    ->count();

                if ($completedInnings >= 2) {
                    $innings1 = MatchScoreBoard::where('match_id', $matchId)->where('innings', 1)->first();
                    $innings2 = MatchScoreBoard::where('match_id', $matchId)->where('innings', 2)->first();

                    if ($innings1 && $innings2) {
                        if ($innings2->runs > $innings1->runs) {
                            $remainingWickets = 10 - $innings2->wickets;
                            $match->winning_team_id = $innings2->team_id;
                            $match->result_summary = $match->teamB->name . " won by {$remainingWickets} wickets";
                        } elseif ($innings2->runs < $innings1->runs && $innings2->overs >= $maxOvers) {
                            $margin = $innings1->runs - $innings2->runs;
                            $match->winning_team_id = $innings1->team_id;
                            $match->result_summary = $match->teamA->name . " won by {$margin} runs";
                        } elseif ($innings2->runs == $innings1->runs) {
                            $match->winning_team_id = null;
                            $match->result_summary = "Match tied";
                        }

                        $match->status = 'completed';
                        $match->save();
                    }
                }
            }

            // ---------- Check if chasing team has already won (target reached early) ----------
            if ($innings == 2) {
                $innings1 = MatchScoreBoard::where('match_id', $matchId)->where('innings', 1)->first();
                $target   = $innings1->runs + 1;

                if ($scoreboard->runs >= $target) {
                    $remainingWickets = $scoreboard->team->players->count() - $scoreboard->wickets;

                    $match->winning_team_id = $scoreboard->team_id;
                    $match->result_summary  = $scoreboard->team->name . " won by {$remainingWickets} wickets";
                    $match->status          = 'completed';
                    $match->save();

                    // end the innings immediately
                    $scoreboard->status = 'ended';
                    $scoreboard->save();

                    $isInningsEnded = true;
                }
            }

            // ---------- Update Player Statistics ----------

            // ✅ Update striker stats
            if ($strikerId) {
                $strikerStat = PlayerStat::firstOrCreate(['player_id' => $strikerId]);

                $strikerStat->total_runs   += $runs;
                $strikerStat->balls_faced  += $legalBall ? 1 : 0;
                $strikerStat->fours        += ($runs == 4) ? 1 : 0;
                $strikerStat->sixes        += ($runs == 6) ? 1 : 0;

                if ($strikerStat->balls_faced > 0) {
                    $strikerStat->strike_rate = round(($strikerStat->total_runs / $strikerStat->balls_faced) * 100, 2);
                }

                $strikerStat->save();
            }

            // ✅ Update non-striker stats (only if he faced a ball in a run-out OR extras credited)
            if ($nonStrikerId) {
                $nonStrikerStat = PlayerStat::firstOrCreate(['player_id' => $nonStrikerId]);

                // ⚡ generally non-striker doesn’t face balls here,
                // but handle run-outs/extras if needed later
                $nonStrikerStat->save();
            }

            // ✅ Update bowler stats
            if ($bowlerId) {
                $bowlerStat = PlayerStat::firstOrCreate(['player_id' => $bowlerId]);

                // increment innings bowled if this is his first ball of the innings
                if ($legalBall && $bowlerStat->overs_bowled == 0) {
                    $bowlerStat->innings_bowled += 1;
                }

                // convert overs to balls
                $bowlerStat->overs_bowled += $legalBall ? 1 : 0;
                $bowlerStat->runs_conceded += $runs + (in_array($deliveryType, ['no-ball', 'wide']) ? 1 : 0);
                if ($isWicket && $wicketType != 'run_out') {
                    $bowlerStat->wickets += 1;
                }

                // derived bowling metrics
                if ($bowlerStat->overs_bowled > 0) {
                    $oversAsFloat = $bowlerStat->overs_bowled / 6; // convert balls back to overs
                    $bowlerStat->economy_rate = round($bowlerStat->runs_conceded / $oversAsFloat, 2);

                    if ($bowlerStat->wickets > 0) {
                        $bowlerStat->bowling_average = round($bowlerStat->runs_conceded / $bowlerStat->wickets, 2);
                    }
                }

                $bowlerStat->save();
            }

            DB::commit();

            // ---------- Prepare response ----------
            return response()->json([
                'success' => true,
                'message' => 'Delivery recorded successfully',
                'isInningsEnded' => $isInningsEnded,
                'updated_state' => [
                    'striker' => in_array($striker->status, ['batting', 'on-strike']) ? [
                        'id' => $striker->player_id,
                        'name' => $striker->player?->user?->full_name ?? '---',
                        'runs' => $striker->runs_scored,
                        'balls' => $striker->balls_faced,
                        'status' => $striker->status,
                    ] : null,
                    'nonStriker' => in_array($nonStriker->status, ['batting', 'on-strike']) ? [
                        'id' => $nonStriker->player_id,
                        'name' => $nonStriker->player?->user?->full_name ?? '---',
                        'runs' => $nonStriker->runs_scored,
                        'balls' => $nonStriker->balls_faced,
                        'status' => $nonStriker->status,
                    ] : null,
                    'team' => [
                        'score'   => $scoreboard->runs,
                        'overs'   => $scoreboard->overs,
                        'wickets' => $scoreboard->wickets,
                        'status'  => $scoreboard->status,
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
