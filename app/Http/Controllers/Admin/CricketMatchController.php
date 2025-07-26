<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use App\Models\TournamentGroupTeam;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CricketMatchToss;
use App\Models\MatchScoreBoard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

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

            // Get match to identify both teams
            $match = CricketMatch::findOrFail($matchId);
            $teamA = $match->team_a_id;
            $teamB = $match->team_b_id;

            $tossWinner = (int) $request->input('toss_winner_team_id');
            $tossDecision = strtolower($request->input('toss_decision'));

            // Determine 1st innings batting team
            if ($tossDecision === 'bat') {
                $battingFirstTeam = $tossWinner;
            } else {
                $battingFirstTeam = ($tossWinner === $teamA) ? $teamB : $teamA;
            }

            $bowlingFirstTeam = ($battingFirstTeam === $teamA) ? $teamB : $teamA;

            MatchScoreBoard::where('match_id', $matchId)->delete();

            // Create 1st innings
            MatchScoreBoard::create([
                'match_id' => $matchId,
                'team_id' => $battingFirstTeam,
                'innings' => 1,
                'runs' => 0,
                'wickets' => 0,
                'overs' => 0,
            ]);

            // Create 2nd innings (to be used later)
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
                'data' => $tossData
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

            $match = CricketMatch::findOrFail($id);
            $match->status = 'live';
            $match->save();
            $match->load(['teamA', 'teamB', 'tournament']);
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
}
