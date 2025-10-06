<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TournamentController extends Controller
{
    public function getTournaments(Request $request)
    {
        try {
            $tournaments = Tournament::orderBy('start_date', 'desc')->get();

            $data = $tournaments->map(function ($tournament) {
                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'slug' => $tournament->slug,
                    'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d'),
                    'status' => $tournament->status,
                    'matches_count' => $tournament->matches->count(),
                    'stage' => implode(", ", array_unique($tournament->matches->pluck('stage')->toArray()))
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tournaments fetched successfully.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tournaments', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tournaments.',
            ], 500);
        }
    }

    public function getTournamentBySlug($slug)
    {
        try {
            $tournament = Tournament::with([
                'matches.teamA:id,name,logo',
                'matches.teamB:id,name,logo',
                'matches.winningTeam:id,name',
                'matches' => function ($q) {
                    $q->orderBy('match_date', 'asc');
                },
                'groups.teams:id,name',
                'standings.team:id,name',
                'playerStats.player.user:id,full_name',
                'teams.players.user',
            ])->where('slug', $slug)->firstOrFail();

            $pointsTable = [];

            foreach ($tournament->groups as $group) {
                $groupTeams = $group->teams->map(function ($team) use ($tournament) {
                    $stat = $tournament->standings->firstWhere('team_id', $team->id);

                    return [
                        'team_id' => $team->id,
                        'team_name' => $team->name,
                        'played' => isset($stat) ? $stat->matches_played : 0,
                        'won' => isset($stat) ? $stat->wins : 0,
                        'lost' => isset($stat) ? $stat->losses : 0,
                        'nrr' => isset($stat) ? $stat->nrr : 0,
                        'points' => isset($stat) ? $stat->points : 0,
                    ];
                });

                $pointsTable[] = [
                    'group_name' => $group->name,
                    'teams' => $groupTeams->sortByDesc('points')->values(),
                ];
            }

            // Key Stats (top performers from playerStats relation)
            $topRunScorer = $tournament->playerStats->sortByDesc('total_runs')->first();
            Log::info($tournament->playerStats->sortByDesc('total_runs')->first());
            $topWicketTaker = $tournament->playerStats->sortByDesc('wickets')->first();
            $mostSixes = $tournament->playerStats->sortByDesc('sixes')->first();
            $bestStrikeRate = $tournament->playerStats
                ->filter(fn($s) => $s->balls_faced > 30)
                ->sortByDesc('strike_rate')
                ->first();
            $sortedMatches = $tournament->matches->sortBy(function ($match) {
                return Carbon::parse($match->match_date);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Tournament fetched successfully.',
                'data' => [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'slug' => $tournament->slug,
                    'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d'),
                    'status' => $tournament->status,
                    'matches_count' => $tournament->matches->count(),
                    'stage' => implode(", ", array_filter(array_unique($tournament->matches->pluck('stage')->toArray()))),
                    'matches' => $sortedMatches->map(function ($match) {
                        return [
                            'id' => $match->id,
                            'title' => $match->title,
                            'team_a' => [
                                'id' => $match->teamA->id,
                                'name' => $match->teamA->name,
                                'logo' => $match->teamA->logo,
                                'score' => $match->scoreboard->where('team_id', $match->teamA->id)->first()->runs . " / " . $match->scoreboard->where('team_id', $match->teamA->id)->first()->wickets,
                                'overs' => $match->scoreboard->where('team_id', $match->teamA->id)->first()->overs
                            ],
                            'team_b' => [
                                'id' => $match->teamB->id,
                                'name' => $match->teamB->name,
                                'logo' => $match->teamB->logo,
                                'score' => $match->scoreboard->where('team_id', $match->teamB->id)->first()->runs . " / " . $match->scoreboard->where('team_id', $match->teamB->id)->first()->wickets,
                                'overs' => $match->scoreboard->where('team_id', $match->teamB->id)->first()->overs
                            ],
                            'venue' => $match->venue,
                            'match_date' => Carbon::parse($match->match_date)->format('d M, Y h:i A'),
                            'status' => $match->status,
                            'result_summary' => $match->result_summary,
                            'stage' => $match->stage,
                            'winning_team' => isset($match->winningTeam) ? $match->winningTeam->name : 'N/A',
                        ];
                    }),
                    'points_table' => $pointsTable,
                    'key_stats' => [
                        'most_runs' => $topRunScorer ? [
                            'label' => 'Most Runs',
                            'player' => $topRunScorer->player->name,
                            'image' => $topRunScorer->player->image,
                            'team' => $topRunScorer->team->name,
                            'value' => $topRunScorer->total_runs,
                        ] : null,

                        'most_wickets' => $topWicketTaker ? [
                            'label' => 'Most Wickets',
                            'player' => $topWicketTaker->player->name,
                            'image' => $topWicketTaker->player->image,
                            'team' => $topWicketTaker->team->name,
                            'value' => $topWicketTaker->wickets . " X",
                        ] : null,

                        'most_sixes' => $mostSixes ? [
                            'label' => 'Most Sixes',
                            'player' => $mostSixes->player->name,
                            'image' => $mostSixes->player->image,
                            'team' => $mostSixes->team->name,
                            'value' => $mostSixes->sixes,
                        ] : null,

                        'best_strike_rate' => $bestStrikeRate ? [
                            'label' => 'Best Strike Rate',
                            'player' => $bestStrikeRate->player->name,
                            'image' => $bestStrikeRate->player ->image,
                            'team' => $bestStrikeRate->team->name,
                            'value' => $bestStrikeRate->strike_rate,
                        ] : null,
                    ],
                    'teams' => $tournament->teams->map(function ($team) {
                        return [
                            'id' => $team->id,
                            'name' => $team->name,
                            'slug' => $team->slug,
                            'logo' => $team->logo,
                            'coach_name' => $team->coach_name,
                            'manager_name' => $team->manager_name,
                            'description' => $team->description,
                            'players_count' => $team->players->count(),
                            'players' => $team->players->map(function ($player) {
                                return [
                                    'id' => $player->id,
                                    'name' => $player->user ? $player->user->full_name : null,
                                    'email' => $player->user ? $player->user->email : null,
                                    'image' => $player->user ? $player->user->image : null,
                                    'role' => $player->player_role,
                                    'batting_style' => $player->batting_style,
                                    'bowling_style' => $player->bowling_style,
                                ];
                            }),
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tournament by slug', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tournament.',
            ], 500);
        }
    }
}
