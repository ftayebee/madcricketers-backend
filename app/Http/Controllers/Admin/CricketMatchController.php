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
use Illuminate\Support\Facades\Auth;
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
