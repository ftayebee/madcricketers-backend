<?php

namespace App\Console\Commands;

use App\Models\CricketMatch;
use App\Models\MatchScoreBoard;
use App\Models\Tournament;
use App\Models\TournamentTeamStat;
use Illuminate\Console\Command;

class RecalculateTournamentPoints extends Command
{
    protected $signature = 'tournament:recalculate-points {tournamentId? : ID of tournament to recalculate (omit for all tournaments)}';

    protected $description = 'Recalculate points table for one or all tournaments from completed match data';

    public function handle(): int
    {
        $tournamentId = $this->argument('tournamentId');

        $tournaments = $tournamentId
            ? Tournament::where('id', $tournamentId)->get()
            : Tournament::all();

        if ($tournaments->isEmpty()) {
            $this->error('No tournaments found.');
            return self::FAILURE;
        }

        foreach ($tournaments as $tournament) {
            $this->info("Processing: [{$tournament->id}] {$tournament->name}");
            $this->recalculateForTournament($tournament);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    private function recalculateForTournament(Tournament $tournament): void
    {
        $teamIds = CricketMatch::where('tournament_id', $tournament->id)
            ->where('status', 'completed')
            ->get(['team_a_id', 'team_b_id'])
            ->flatMap(fn ($m) => [$m->team_a_id, $m->team_b_id])
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            $this->line("  No completed matches — skipping.");
            return;
        }

        $bar = $this->output->createProgressBar($teamIds->count());
        $bar->start();

        foreach ($teamIds as $teamId) {
            $completedMatches = CricketMatch::where('tournament_id', $tournament->id)
                ->where('status', 'completed')
                ->where(function ($q) use ($teamId) {
                    $q->where('team_a_id', $teamId)->orWhere('team_b_id', $teamId);
                })->get();

            $played = $completedMatches->count();
            $wins   = $completedMatches->where('winning_team_id', $teamId)->count();
            $ties   = $completedMatches->whereNull('winning_team_id')->count();
            $losses = $played - $wins - $ties;
            $points = ($wins * 2) + ($ties * 1);

            $totalRunsScored   = 0.0;
            $totalOversFaced   = 0.0;
            $totalRunsConceded = 0.0;
            $totalOversBowled  = 0.0;

            foreach ($completedMatches as $m) {
                $scoreboards = MatchScoreBoard::where('match_id', $m->id)
                    ->whereIn('status', ['ended', 'running'])
                    ->get();

                $teamBoard = $scoreboards->firstWhere('team_id', $teamId);
                $oppBoard  = $scoreboards->first(fn ($s) => $s->team_id != $teamId);

                if ($teamBoard) {
                    $totalRunsScored += (int) ($teamBoard->runs ?? 0);
                    $totalOversFaced += $this->oversToDecimal((float) ($teamBoard->overs ?? 0));
                }
                if ($oppBoard) {
                    $totalRunsConceded += (int) ($oppBoard->runs ?? 0);
                    $totalOversBowled  += $this->oversToDecimal((float) ($oppBoard->overs ?? 0));
                }
            }

            $nrr = 0.0;
            if ($totalOversFaced > 0 && $totalOversBowled > 0) {
                $nrr = round(
                    ($totalRunsScored / $totalOversFaced) - ($totalRunsConceded / $totalOversBowled),
                    3
                );
            }

            TournamentTeamStat::updateOrCreate(
                ['tournament_id' => $tournament->id, 'team_id' => $teamId],
                [
                    'matches_played' => $played,
                    'wins'           => $wins,
                    'losses'         => $losses,
                    'draws'          => $ties,
                    'points'         => $points,
                    'nrr'            => $nrr,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  Updated {$teamIds->count()} team(s).");
    }

    private function oversToDecimal(float $cricketOvers): float
    {
        $fullOvers = (int) $cricketOvers;
        $balls     = round(($cricketOvers - $fullOvers) * 10);
        return $fullOvers + ($balls / 6);
    }
}
