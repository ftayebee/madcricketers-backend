<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Player;
use App\Models\PlayerStat;
use App\Models\MatchPlayer;
use App\Models\Partnership;
use App\Models\CricketMatch;
use App\Models\FallOfWicket;
use App\Models\MatchDelivery;
use App\Models\MatchScoreBoard;
use Illuminate\Database\Seeder;
use App\Models\CricketMatchToss;
use App\Models\TournamentTeamStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TournamentPlayerStat;

class FullMatchSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $matches = CricketMatch::where('status', 'upcoming')->get();

            foreach ($matches as $match) {
                // --- 1. Toss ---
                $tossWinner = $match->team_a_id;
                $decision = 'bat';

                CricketMatchToss::updateOrCreate(
                    ['cricket_match_id' => $match->id],
                    ['toss_winner_team_id' => $tossWinner, 'decision' => $decision]
                );

                // --- 2. Players: initialize MatchPlayer ---
                $teamAPlayers = $match->teamA?->players()->take(11)->get();
                $teamBPlayers = $match->teamB?->players()->take(11)->get();

                if (!$teamAPlayers || !$teamBPlayers || $teamAPlayers->count() < 1 || $teamBPlayers->count() < 1) {
                    throw new \Exception("Match {$match->id} does not have enough players.");
                }

                // create MatchPlayer records
                foreach ([$match->team_a_id => $teamAPlayers, $match->team_b_id => $teamBPlayers] as $teamId => $players) {
                    foreach ($players as $player) {
                        MatchPlayer::updateOrCreate(
                            [
                                'match_id' => $match->id,
                                'team_id' => $teamId,
                                'player_id' => $player->id,
                            ],
                            [
                                'runs_scored' => 0,
                                'balls_faced' => 0,
                                'fours' => 0,
                                'sixes' => 0,
                                'wickets_taken' => 0,
                                'overs_bowled' => 0,
                                'runs_conceded' => 0,
                                'status' => 'ready',
                            ]
                        );
                    }
                }

                // --- 3. Fetch MatchPlayer collections for simulation ---
                $teamAPlayers = MatchPlayer::where('match_id', $match->id)
                    ->where('team_id', $match->team_a_id)->get()->values();
                $teamBPlayers = MatchPlayer::where('match_id', $match->id)
                    ->where('team_id', $match->team_b_id)->get()->values();

                $totalOvers = $match->max_overs ?? 20;

                // --- 4. Closure to simulate an innings ---
                $simulateInnings = function ($battingPlayers, $bowlingPlayers, $battingTeamId, $bowlingTeamId, $matchId, $innings, $totalOvers) {
                    $battingPlayers = $battingPlayers->values();
                    $bowlingPlayers = $bowlingPlayers->values();
                    $fallWickets = [];

                    for ($over = 1; $over <= $totalOvers; $over++) {
                        foreach (range(1, 6) as $ball) {
                            $batsman = $battingPlayers->first();
                            $nonStriker = $battingPlayers->count() > 1 ? $battingPlayers->skip(1)->first() : $batsman;
                            $bowler = $bowlingPlayers->first();

                            // safety check
                            if (!$batsman || !$nonStriker || !$bowler) {
                                throw new \Exception("Invalid players for match {$matchId} innings {$innings}");
                            }

                            $isWicket = ($ball == 6); // deterministic: last ball of over is a wicket
                            $wicketType = $isWicket ? 'bowled' : 'none';
                            $runsBatsman = $isWicket ? 0 : 1;

                            // create delivery
                            MatchDelivery::create([
                                'match_id' => $matchId,
                                'innings' => $innings,
                                'over_number' => $over,
                                'ball_in_over' => $ball,
                                'batting_team_id' => $battingTeamId,
                                'bowling_team_id' => $bowlingTeamId,
                                'batsman_id' => $batsman->player_id,
                                'non_striker_id' => $nonStriker->player_id,
                                'bowler_id' => $bowler->player_id,
                                'runs_batsman' => $runsBatsman,
                                'runs_extras' => 0,
                                'delivery_type' => 'normal',
                                'is_wicket' => $isWicket,
                                'wicket_type' => $wicketType,
                                'wicket_player_id' => $isWicket ? $batsman->player_id : null,
                                'fielder_id' => $isWicket ? $bowler->player_id : null,
                            ]);

                            // update batsman stats
                            $batsman->runs_scored += $runsBatsman;
                            $batsman->balls_faced += 1;
                            $batsman->status = $isWicket ? 'bowled' : 'batting';
                            $batsman->save();

                            // update bowler stats
                            if ($ball == 6) $bowler->overs_bowled += 1;
                            $bowler->wickets_taken += $isWicket ? 1 : 0;
                            $bowler->runs_conceded += $runsBatsman;
                            $bowler->status = 'bowling';
                            $bowler->save();

                            // fall of wicket
                            if ($isWicket) {
                                $fow = FallOfWicket::create([
                                    'match_id' => $matchId,
                                    'team_id' => $battingTeamId,
                                    'innings' => $innings,
                                    'wicket_number' => count($fallWickets) + 1,
                                    'runs' => $batsman->runs_scored,
                                    'overs' => $over,
                                    'batter_id' => $batsman->player_id,
                                    'bowler_id' => $bowler->player_id,
                                    'fielder_id' => $bowler->player_id,
                                    'dismissal_type' => $wicketType,
                                ]);
                                $fallWickets[] = $fow;
                            }
                        }
                    }

                    // Partnership
                    if (count($fallWickets)) {
                        $battersIds = $battingPlayers->take(2)->pluck('player_id')->toArray();
                        Partnership::create([
                            'match_id' => $matchId,
                            'team_id' => $battingTeamId,
                            'batter_1_id' => $battersIds[0],
                            'batter_2_id' => $battersIds[1],
                            'runs' => $totalOvers * 6,
                            'balls' => $totalOvers * 6,
                            'start_over' => 1,
                            'end_over' => $totalOvers,
                            'wicket_id' => $fallWickets[0]->id ?? null,
                        ]);
                    }

                    // Scoreboard
                    $totalRuns = $battingPlayers->sum('runs_scored');
                    $totalWickets = $battingPlayers->sum('wickets_taken');
                    $totalOversBowled = $battingPlayers->sum('overs_bowled');

                    MatchScoreBoard::updateOrCreate(
                        ['match_id' => $matchId, 'team_id' => $battingTeamId, 'innings' => $innings],
                        [
                            'runs' => $totalRuns,
                            'wickets' => $totalWickets,
                            'overs' => $totalOversBowled,
                            'extras' => 0,
                            'status' => 'ended',
                        ]
                    );

                    return $fallWickets;
                };

                // --- 5. Simulate innings 1 and 2 ---
                $fallWicketsInnings1 = $simulateInnings($teamAPlayers, $teamBPlayers, $match->team_a_id, $match->team_b_id, $match->id, 1, $totalOvers);
                $fallWicketsInnings2 = $simulateInnings($teamBPlayers, $teamAPlayers, $match->team_b_id, $match->team_a_id, $match->id, 2, $totalOvers);

                // --- 6. Update PlayerStats & TournamentPlayerStats ---
                foreach (MatchPlayer::where('match_id', $match->id)->get() as $mp) {
                    $stat = PlayerStat::firstOrNew(['player_id' => $mp->player_id]);
                    $stat->matches_played += 1;
                    $stat->total_runs += $mp->runs_scored;
                    $stat->balls_faced += $mp->balls_faced;
                    $stat->overs_bowled += $mp->overs_bowled;
                    $stat->wickets += $mp->wickets_taken;
                    $stat->save();

                    if ($match->tournament_id) {
                        $tpStat = TournamentPlayerStat::firstOrNew([
                            'tournament_id' => $match->tournament_id,
                            'player_id' => $mp->player_id,
                        ]);
                        $tpStat->matches_played += 1;
                        $tpStat->total_runs += $mp->runs_scored;
                        $tpStat->balls_faced += $mp->balls_faced;
                        $tpStat->overs_bowled += $mp->overs_bowled;
                        $tpStat->wickets += $mp->wickets_taken;
                        $tpStat->save();
                    }
                }

                // --- 7. TournamentTeamStats ---
                if ($match->tournament_id) {
                    foreach ([$match->team_a_id, $match->team_b_id] as $teamId) {
                        $teamStat = TournamentTeamStat::updateOrCreate([
                            'tournament_id' => $match->tournament_id,
                            'team_id' => $teamId,
                        ]);
                        $teamStat->matches_played += 1;
                        $teamStat->wins += ($teamId == $match->team_a_id) ? 1 : 0;
                        $teamStat->losses += ($teamId == $match->team_b_id) ? 1 : 0;
                        $teamStat->points = $teamStat->wins * 2;

                        // --- Calculate NRR ---
                        $totalRunsScored = MatchScoreBoard::where('team_id', $teamId)
                            ->whereHas('match', fn($q) => $q->where('tournament_id', $match->tournament_id))
                            ->sum('runs');

                        // Convert balls faced into overs
                        $totalBallsFaced = MatchDelivery::where('match_id', $match->id)
                            ->where('batting_team_id', $teamId)
                            ->count();
                        $totalOversFaced = $totalBallsFaced / 6;

                        $totalRunsConceded = MatchScoreBoard::whereHas('match', fn($q) => $q->where('tournament_id', $match->tournament_id))
                            ->where('team_id', '<>', $teamId)
                            ->sum('runs');

                        $totalBallsBowled = MatchDelivery::where('match_id', $match->id)
                            ->where('bowling_team_id', $teamId)
                            ->count();
                        $totalOversBowled = $totalBallsBowled / 6;

                        // NRR calculation
                        $nrr = 0;
                        if ($totalOversFaced > 0 && $totalOversBowled > 0) {
                            $nrr = ($totalRunsScored / $totalOversFaced) - ($totalRunsConceded / $totalOversBowled);
                        }
                        $teamStat->nrr = round($nrr, 3);
                        $teamStat->save();
                    }
                }

                // --- 8. Close Match ---
                $match->status = 'completed';
                $match->winning_team_id = $match->team_a_id;
                $match->result_summary = "Completed via deterministic seeder.";
                $match->save();
            }
        });
    }
}
