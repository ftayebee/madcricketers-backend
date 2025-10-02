<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\MatchPlayer;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use App\Models\MatchScoreBoard;
use App\Models\CricketMatchToss;
use Illuminate\Support\Facades\DB;
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
                        ->orderBy('match_date', 'asc')
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

    public function getShortName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        $initials = '';

        // Take initials from all parts except the last
        for ($i = 0; $i < count($parts) - 1; $i++) {
            if (!empty($parts[$i])) {
                $initials .= strtoupper(substr($parts[$i], 0, 1)) . '.';
            }
        }

        // Add the last part as full
        $lastName = $parts[count($parts) - 1] ?? '';

        return $initials . ' ' . $lastName;
    }

    public function getMatchInfo(Request $request, $id)
    {
        try {
            $match = CricketMatch::with(['teamA', 'teamB', 'teamA.players', 'teamB.players'])->findOrFail($id);
            $matchDate = Carbon::parse($match->match_date);

            $teams = [
                ['team' => $match->teamA, 'id' => $match->team_a_id],
                ['team' => $match->teamB, 'id' => $match->team_b_id],
            ];

            $teamForms = [];

            foreach ($teams as $entry) {
                $team = $entry['team'];
                $teamId = $entry['id'];

                // Get last 5 completed matches before this match date
                $lastMatches = CricketMatch::where(function ($q) use ($teamId) {
                        $q->where('team_a_id', $teamId)->orWhere('team_b_id', $teamId);
                    })
                    ->where('match_date', '<', $matchDate)
                    ->whereNotNull('winning_team_id')
                    ->orderBy('match_date', 'desc')
                    ->take(5)
                    ->get();

                $form = [];

                if ($lastMatches->count() > 0) {
                    foreach ($lastMatches as $m) {
                        if ($m->winner_team_id == $teamId) {
                            $form[] = 'W';
                        } else {
                            $form[] = 'L';
                        }
                    }
                } else {
                    for ($i = 0; $i < 5; $i++) {
                        $form[] = '-';
                    }
                }

                // Prepare player list with ID, name, image
                $players = [];
                if ($team && $team->players) {
                    foreach ($team->players as $player) {
                        $players[] = [
                            'id' => $player->id,
                            'name' => $this->getShortName($player->user->full_name),
                            'image' => $player->user->image,
                            'role' => $player->player_role,
                        ];
                    }
                }

                $teamForms[] = [
                    'team_id' => $teamId,
                    'team_name' => $team->name ?? '',
                    'team_logo' => $team->logo ?? '',
                    'form' => array_reverse($form),
                    'players' => $players,
                ];
            }

            return response()->json($teamForms);
        } catch (Exception $e) {
            Log::error("Error fetching team form", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch team form.',
            ], 500);
        }
    }

    public function getYetToBat(Request $request, $matchId)
    {
        try {
            $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                ->where('status', 'running')
                ->first();

            if (!$scoreboard) {
                $scoreboard = MatchScoreBoard::where('match_id', $matchId)
                    ->orderByDesc('innings')
                    ->first();
            }

            if (!$scoreboard) {
                return response()->json([
                    'success' => true,
                    'message' => 'No scoreboard yet. Toss may not be done.',
                    'players' => [],
                    'battingTeamId' => null
                ]);
            }

            $battingTeamId = $scoreboard->team_id;

            $teamPlayerIds = DB::table('player_team')
                ->where('team_id', $battingTeamId)
                ->pluck('player_id');

            $battedPlayerIds = DB::table('match_players')
                ->where('match_id', $matchId)
                ->where('team_id', $battingTeamId)
                ->where(function ($query) {
                    $query->whereNotNull('runs_scored')
                        ->orWhereNotNull('balls_faced');
                })
                ->whereNotIn('status', ['ready', 'retired-hurt'])
                ->pluck('player_id');

            // Players yet to bat
            $yetToBatPlayers = Player::with('user')
                ->whereIn('id', $teamPlayerIds)
                ->whereNotIn('id', $battedPlayerIds)
                ->get()
                ->map(function ($player) {
                    $fullName = $player->user->full_name ?? 'Unknown';
                    $nameParts = explode(' ', trim($fullName));
                    $lastName = array_pop($nameParts);
                    $initials = implode('.', array_map(function ($n) {
                        return mb_substr($n, 0, 1);
                    }, $nameParts));

                    return [
                        'id' => $player->id,
                        'full_name' => $fullName,
                        'short_name' => $initials ? $initials . '. ' . $lastName : $lastName,
                        'role' => ucwords(str_replace('-', ' ', $player->player_role ?? '')),
                        'image' => $player->image ?? asset('storage/assets/images/users/dummy-avatar.jpg'),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Players Found.',
                'players' => $yetToBatPlayers,
                'battingTeamId' => $battingTeamId
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching yet to bat players', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data.',
                'players' => [],
                'battingTeamId' => null
            ]);
        }
    }


    public function getLiveScore(Request $request, $id){

    }
}
