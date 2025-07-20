<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\Tournament;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CricketMatchController extends Controller
{
    public function recentMatches(Request $request)
    {
        try {
            $matches = CricketMatch::with([
                                    'teamA:id,name,logo',
                                    'teamB:id,name,logo',
                                    'winningTeam:id,name',
                                    'tournament:id,name,slug,start_date,end_date,status',
                                ])
                                    ->whereIn('status', ['upcoming', 'live'])
                                    ->orderByRaw("
                            CASE
                                WHEN status = 'live' THEN 1
                                WHEN status = 'upcoming' THEN 2
                                ELSE 3
                            END
                        ")
                        ->orderByDesc('match_date')
                        ->limit(10)
                        ->get();

            $data = $matches->map(function ($match) {
                $groupA = optional($match->teamA->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $groupB = optional($match->teamB->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                return [
                    'id' => $match->id,
                    'title' => $match->title,
                    'match_date' => Carbon::parse($match->match_date)->format('d M, Y'),
                    'match_time' => Carbon::parse($match->match_date)->format('h:i A'),
                    'venue' => $match->venue,
                    'result_summary' => $match->result_summary,
                    'status' => $match->status,
                    'tournament' => [
                        'slug' => $match->tournament->slug ?? null,
                        'name' => $match->tournament->name ?? null,
                        'id' => $match->tournament->id ?? null,
                        'start_date' => Carbon::parse($match->tournament->start_date)->format('Y-m-d'),
                        'end_date' => Carbon::parse($match->tournament->end_date)->format('Y-m-d'),
                        'status' => $match->tournament->status ?? null,
                    ],
                    'team_a' => [
                        'id' => $match->teamA->id,
                        'name' => $match->teamA->name,
                        'logo' => $match->teamA->logo,
                        'group' => $groupA,
                    ],
                    'team_b' => [
                        'id' => $match->teamB->id,
                        'name' => $match->teamB->name,
                        'logo' => $match->teamB->logo,
                        'group' => $groupB,
                    ],
                    'winning_team' => $match->winningTeam->name ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Recent & upcoming matches fetched successfully.',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching recent matches', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent matches.',
            ], 500);
        }
    }

    public function completedMatches(Request $request)
    {
        try {
            $matches = CricketMatch::with([
                'teamA:id,name,logo',
                'teamB:id,name,logo',
                'winningTeam:id,name',
                'tournament:id,name',
            ])
                ->whereIn('status', ['completed'])
                ->orderByDesc('match_date')
                ->limit(10)
                ->get();

            $data = $matches->map(function ($match) {
                $groupA = optional($match->teamA->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $groupB = optional($match->teamB->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                return [
                    'id' => $match->id,
                    'title' => $match->title,
                    'match_date' => Carbon::parse($match->match_date)->format('d M, Y'),
                    'match_time' => Carbon::parse($match->match_date)->format('h:i A'),
                    'venue' => $match->venue,
                    'result_summary' => $match->result_summary,
                    'status' => $match->status,
                    'tournament' => $match->tournament->name ?? null,
                    'team_a' => [
                        'id' => $match->teamA->id,
                        'name' => $match->teamA->name,
                        'logo' => $match->teamA->logo,
                        'group' => $groupA,
                    ],
                    'team_b' => [
                        'id' => $match->teamB->id,
                        'name' => $match->teamB->name,
                        'logo' => $match->teamB->logo,
                        'group' => $groupB,
                    ],
                    'winning_team' => $match->winningTeam->name ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Recent & upcoming matches fetched successfully.',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching recent matches', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent matches.',
            ], 500);
        }
    }

    public function getMatchDetailBySlug(Request $request, $id)
    {
        try {
            $match = CricketMatch::with([
                'teamA:id,name,logo',
                'teamB:id,name,logo',
                'winningTeam:id,name',
                'tournament:id,name,slug,start_date,end_date,status',

                'players.player:id,name,role,team_id',
                'players.battingStats',
                'players.bowlingStats',

                // Deliveries (ball by ball)
                'deliveries.batsman:id,name',
                'deliveries.bowler:id,name',
                'deliveries.fielder:id,name',

                // Fall of wickets
                'wickets.player:id,name',
                'wickets.bowler:id,name',

                // Partnerships
                'partnerships.batsman1:id,name',
                'partnerships.batsman2:id,name',

                'scoreboard.team:id,name,logo',
            ])
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Match details fetched successfully.',
                'data' => $match,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error("Cricket match not found", [
                'message' => $e->getMessage(),
                'slug' => $slug,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Match details not found.',
            ]);
        } catch (Exception $e) {
            Log::error("Error fetching match details", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch match details.',
            ], 500);
        }
    }
}
