<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\Tournament;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class CricketMatchController extends Controller
{
    public function recentMatches(Request $request)
    {
        try {
            $matches = CricketMatch::with([
                'teamA:id,name,logo',
                'teamB:id,name,logo',
                'winningTeam:id,name',
                'tournament:id,name',
            ])
                ->whereIn('status', ['upcoming', 'live'])
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
}
