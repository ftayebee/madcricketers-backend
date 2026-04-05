<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
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
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\MatchTeamInfoRequest;
use App\Models\MatchDelivery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\CurrentOverService;

class CricketMatchController extends Controller
{
    public function getLiveMatches(Request $request)
    {
        try {
            $matches = CricketMatch::with([
                'teamA:id,name,logo',
                'teamB:id,name,logo',
                'winningTeam:id,name',
                'tournament:id,name,slug,start_date,end_date,status',
                'scoreboard:id,match_id,team_id,innings,runs,wickets,overs,status',
            ])
                ->whereIn('status', ['live'])
                ->orderByRaw("
                            CASE
                                WHEN status = 'live' THEN 1
                                WHEN status = 'upcoming' THEN 2
                                ELSE 3
                            END
                        ")
                ->orderBy('match_date', 'asc')
                ->get();

            $data = $matches->map(function ($match) {
                $groupA = optional($match->teamA->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $groupB = optional($match->teamB->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $sbA = $match->scoreboard->where('team_id', $match->team_a_id)->sortByDesc('innings')->first();
                $sbB = $match->scoreboard->where('team_id', $match->team_b_id)->sortByDesc('innings')->first();

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
                        'score' => $sbA ? "{$sbA->runs}/{$sbA->wickets}" : null,
                        'overs' => $sbA ? $sbA->overs : null,
                    ],
                    'team_b' => [
                        'id' => $match->teamB->id,
                        'name' => $match->teamB->name,
                        'logo' => $match->teamB->logo,
                        'group' => $groupB,
                        'score' => $sbB ? "{$sbB->runs}/{$sbB->wickets}" : null,
                        'overs' => $sbB ? $sbB->overs : null,
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

    public function getUpcomingMatches(Request $request)
    {
        try {
            $matches = CricketMatch::with([
                'teamA:id,name,logo',
                'teamB:id,name,logo',
                'winningTeam:id,name',
                'tournament:id,name,slug,start_date,end_date,status',
                'scoreboard:id,match_id,team_id,innings,runs,wickets,overs,status',
            ])
                ->whereIn('status', ['upcoming'])
                ->orderByRaw("
                            CASE
                                WHEN status = 'live' THEN 1
                                WHEN status = 'upcoming' THEN 2
                                ELSE 3
                            END
                        ")
                ->orderBy('match_date', 'asc')
                ->get();

            $data = $matches->map(function ($match) {
                $groupA = optional($match->teamA->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $groupB = optional($match->teamB->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $sbA = $match->scoreboard->where('team_id', $match->team_a_id)->sortByDesc('innings')->first();
                $sbB = $match->scoreboard->where('team_id', $match->team_b_id)->sortByDesc('innings')->first();

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
                        'score' => $sbA ? "{$sbA->runs}/{$sbA->wickets}" : null,
                        'overs' => $sbA ? $sbA->overs : null,
                    ],
                    'team_b' => [
                        'id' => $match->teamB->id,
                        'name' => $match->teamB->name,
                        'logo' => $match->teamB->logo,
                        'group' => $groupB,
                        'score' => $sbB ? "{$sbB->runs}/{$sbB->wickets}" : null,
                        'overs' => $sbB ? $sbB->overs : null,
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
                'scoreboard:id,match_id,team_id,innings,runs,wickets,overs,status',
            ])
                ->whereIn('status', ['completed'])
                ->orderByDesc('match_date')
                ->get();

            $data = $matches->map(function ($match) {
                $groupA = optional($match->teamA->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $groupB = optional($match->teamB->groups()
                    ->where('tournament_group_teams.tournament_id', $match->tournament_id)
                    ->first())->name;

                $sbA = $match->scoreboard->where('team_id', $match->team_a_id)->sortByDesc('innings')->first();
                $sbB = $match->scoreboard->where('team_id', $match->team_b_id)->sortByDesc('innings')->first();

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
                        'score' => $sbA ? "{$sbA->runs}/{$sbA->wickets}" : null,
                        'overs' => $sbA ? $sbA->overs : null,
                    ],
                    'team_b' => [
                        'id' => $match->teamB->id,
                        'name' => $match->teamB->name,
                        'logo' => $match->teamB->logo,
                        'group' => $groupB,
                        'score' => $sbB ? "{$sbB->runs}/{$sbB->wickets}" : null,
                        'overs' => $sbB ? $sbB->overs : null,
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

                'toss'
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
                'id' => $id,
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
                        if ($m->winning_team_id == $teamId) {
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

    public function yetToBatPlayers($teamId, $matchId)
    {
        $allPlayers = Player::whereHas('teams', function ($q) use ($teamId, $matchId) {
                $q->where('teams.id', $teamId);
            })
            ->pluck('players.id');
        $battedPlayers = MatchPlayer::where('match_id', $matchId)
            ->where('team_id', $teamId)
            ->whereIn('status', ['batting', 'on-strike', 'bowled', 'caught', 'bowling', 'run_out', 'lbw', 'retired-hurt', 'fielding', 'hit-wicket', 'closed', 'stumped', 'ready'])
            ->pluck('player_id');

        $yetToBat = $allPlayers->diff($battedPlayers);

        return Player::with('user')->whereIn('id', $yetToBat)->get();
    }

    public function getCurrentOver($matchId)
    {
        return CurrentOverService::get($matchId);
    }

    public function getStriker($matchId)
    {
        $batsman = MatchPlayer::where('match_id', $matchId)
            ->where('status', 'on-strike')
            ->first();

        return $batsman ? Player::find($batsman->player_id) : null;
    }

    public function getNonStriker($matchId)
    {
        $batsman = MatchPlayer::where('match_id', $matchId)
            ->where('status', 'batting')
            ->first();

        return $batsman ? Player::find($batsman->player_id) : null;
    }

    public function getCurrentBowler($matchId)
    {
        $bowler = MatchPlayer::where('match_id', $matchId)
            ->where('status', 'bowling')
            ->latest('id')
            ->first();

        return $bowler ? Player::find($bowler->player_id) : null;
    }

    public function calculateMatchProbability($matchId)
    {
        $match = CricketMatch::find($matchId);
        $score = MatchScoreBoard::where('match_id', $matchId)->sum('runs');
        $wickets = MatchScoreBoard::where('match_id', $matchId)->sum('wickets');

        $target = $match->target ?? 200;
        $remaining = max($target - $score, 0);
        $probability = max(0, min(100, 100 - ($remaining * 100 / $target) + $wickets * 2));

        return round($probability, 2);
    }

    public function calculateProjectedScore($currentRunRate, $match)
    {
        $runRates = [];

        $startRate = floor($currentRunRate) - 1;
        if ($startRate < 0) $startRate = 0;

        for ($i = $startRate; $i <= $startRate + 4; $i++) {
            $rate = $i;
            if ($rate == floor($currentRunRate)) {
                $runRates[] = number_format($currentRunRate, 2) . '*';
            } else {
                $runRates[] = number_format($rate, 2);
            }
        }

        $projections = [];
        foreach ($runRates as $rate) {
            $cleanRate = (float) str_replace('*', '', $rate);
            $projectedScore = $cleanRate * $match->max_overs;
            $projections[] = [
                'run_rate' => $rate,
                'projected_score' => (int) round($projectedScore)
            ];
        }

        return $projections;
    }

    public function getTeamInfo(MatchTeamInfoRequest $request)
    {
        try {
            $match = CricketMatch::findOrFail($request->match_id);

            $battingTeam = Team::select('id', 'name')->find($request->batting_team);
            $bowlingTeam = Team::select('id', 'name')->find($request->bowling_team);

            if ($match->tournament) {
                $battingPlayers = $battingTeam->playersForTournamentMatch($match->id, $match->tournament_id)->get();
                $bowlingPlayers = $bowlingTeam->playersForTournamentMatch($match->id, $match->tournament_id)->get();
            } else {
                $battingPlayers = $battingTeam->playersForFriendlyMatch($match->id, $match->tournament_id)->get();
                $bowlingPlayers = $bowlingTeam->playersForFriendlyMatch($match->id, $match->tournament_id)->get();
            }

            $currentRunRate = 0;
            $scoreboards = [];
            foreach ([$battingTeam, $bowlingTeam] as $team) {
                $teamScoreboard = MatchScoreBoard::where('match_id', $match->id)
                    ->where('team_id', $team->id)
                    ->first();

                $currentRunRate = $teamScoreboard->overs ? $teamScoreboard->runs / $teamScoreboard->overs : 0;

                if ($teamScoreboard) {
                    $scoreboards[] = [
                        'team' => $team,
                        'batting' => $teamScoreboard->battingPlayers(),
                        'bowling' => $teamScoreboard->bowlingPlayers(),
                        'fallOfWickets' => $teamScoreboard->fallOfWickets(),
                        'partnerships' => $teamScoreboard->partnerships(),
                        'yetToBat' => $teamScoreboard->yetToBatPlayers()
                    ];
                }
            }

            $currentOver = $this->getCurrentOver($match->id);
            $striker     = $this->getStriker($match->id);
            $nonStriker  = $this->getNonStriker($match->id);
            $bowler      = $this->getCurrentBowler($match->id);
            $probability = $this->calculateMatchProbability($match->id);
            $projection  = $this->calculateProjectedScore($currentRunRate, $match);
            $yetToBatList= $this->yetToBatPlayers($battingTeam->id, $match->id);

            return response()->json([
                'success' => true,
                'matchData' => [
                    'match' => [
                        'id'    => $match->id,
                        'overs' => $match->overs
                    ],
                    'battingTeam' => [
                        'team'    => $battingTeam,
                        'players' => $battingPlayers
                    ],
                    'bowlingTeam' => [
                        'team'    => $bowlingTeam,
                        'players' => $bowlingPlayers
                    ],
                    'scoreboards' => $scoreboards,
                    'striker'     => $striker,
                    'nonStriker'  => $nonStriker,
                    'bowler'      => $bowler,
                    'currentOver' => $currentOver,
                    'probability' => $probability,
                    'projection'  => $projection,
                    'yetToBatList'=> $yetToBatList
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('getTeamInfo error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch team info.'
            ], 500);
        }
    }

    public function getScorecard(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'match_id' => 'required'
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ]);
            }

            $scoreboards = MatchScoreBoard::where('match_id', $request->match_id)->orderBy('innings', 'ASC')->get();

            return response()->json([
                'success' => true,
                'data' => $scoreboards
            ]);
        } catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]); 
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

            // Exclude players who are currently batting OR have already batted
            // (keep 'retired-hurt' in the list since they may bat again)
            $battedPlayerIds = DB::table('match_players')
                ->where('match_id', $matchId)
                ->where('team_id', $battingTeamId)
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

    public function getLiveScore(Request $request, $id) {}
}
