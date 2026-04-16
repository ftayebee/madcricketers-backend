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
                    'format' => $tournament->format,
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
                'matches' => function ($q) {
                    $q->orderBy('match_date', 'asc');
                },
                'matches.teamA:id,name,logo',
                'matches.teamB:id,name,logo',
                'matches.winningTeam:id,name',
                'matches.scoreboard:id,match_id,team_id,innings,runs,wickets,overs',
                'groups.teams:id,name',
                'standings.team:id,name',
                'playerStats.player.user:id,full_name,image',
                'playerStats.team:id,name',
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
            $stats = $tournament->playerStats;

            $topRunScorer    = $stats->sortByDesc('total_runs')->first();
            $topWicketTaker  = $stats->sortByDesc('wickets')->first();
            $mostSixes       = $stats->sortByDesc('sixes')->first();
            $bestStrikeRate  = $stats->filter(fn($s) => ($s->balls_faced ?? 0) > 30)
                                     ->sortByDesc('strike_rate')->first();

            $buildStat = function ($statModel, string $label, string $valueStr): array {
                return [
                    'label' => $label,
                    'player' => [
                        'name'  => optional(optional($statModel->player)->user)->full_name ?? 'Unknown',
                        'image' => optional(optional($statModel->player)->user)->image ?? null,
                        'team'  => optional($statModel->team)->name ?? '',
                    ],
                    'value' => $valueStr,
                ];
            };

            $keyStats = array_values(array_filter([
                $topRunScorer   ? $buildStat($topRunScorer,   'Most Runs',          $topRunScorer->total_runs   . ' Runs') : null,
                $topWicketTaker ? $buildStat($topWicketTaker, 'Most Wickets',       $topWicketTaker->wickets    . ' Wkts') : null,
                $mostSixes      ? $buildStat($mostSixes,      'Most Sixes',         $mostSixes->sixes           . ' Sixes') : null,
                $bestStrikeRate ? $buildStat($bestStrikeRate, 'Best Strike Rate',   number_format((float)$bestStrikeRate->strike_rate, 2)) : null,
            ]));

            $sortedMatches = $tournament->matches->sortBy(fn($m) => Carbon::parse($m->match_date));

            return response()->json([
                'success' => true,
                'message' => 'Tournament fetched successfully.',
                'data' => [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'slug' => $tournament->slug,
                    'format' => $tournament->format,
                    'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d'),
                    'status' => $tournament->status,
                    'matches_count' => $tournament->matches->count(),
                    'stage' => implode(", ", array_filter(array_unique($tournament->matches->pluck('stage')->toArray()))),
                    'matches' => $sortedMatches->map(function ($match) {
                        $sbA = $match->scoreboard->where('team_id', $match->teamA->id)->sortByDesc('innings')->first();
                        $sbB = $match->scoreboard->where('team_id', $match->teamB->id)->sortByDesc('innings')->first();

                        return [
                            'id'     => $match->id,
                            'title'  => $match->title,
                            'team_a' => [
                                'id'    => $match->teamA->id,
                                'name'  => $match->teamA->name,
                                'logo'  => $match->teamA->logo,
                                'score' => $sbA ? "{$sbA->runs}/{$sbA->wickets}" : null,
                                'overs' => $sbA ? $sbA->overs : null,
                            ],
                            'team_b' => [
                                'id'    => $match->teamB->id,
                                'name'  => $match->teamB->name,
                                'logo'  => $match->teamB->logo,
                                'score' => $sbB ? "{$sbB->runs}/{$sbB->wickets}" : null,
                                'overs' => $sbB ? $sbB->overs : null,
                            ],
                            'venue'          => $match->venue,
                            'match_date'     => Carbon::parse($match->match_date)->format('d M, Y'),
                            'match_time'     => Carbon::parse($match->match_date)->format('h:i A'),
                            'status'         => $match->status,
                            'result_summary' => $match->result_summary,
                            'stage'          => $match->stage,
                            'winning_team'   => optional($match->winningTeam)->name,
                        ];
                    })->values(),
                    'points_table' => $pointsTable,
                    'key_stats'    => $keyStats,
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
