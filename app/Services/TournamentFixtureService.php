<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Tournament;
use App\Models\CricketMatch;
use Illuminate\Support\Collection;

class TournamentFixtureService
{
    /**
     * Entry point. Returns an array of match rows ready for CricketMatch::insert().
     *
     * @throws \RuntimeException on invalid state or duplicate fixtures
     */
    public function generate(Tournament $tournament, string $stage): array
    {
        $this->validateState($tournament);
        $this->validateStageProgression($tournament, $stage);
        $this->guardDuplicates($tournament, $stage);

        return match ($tournament->format) {
            'group'       => $this->groupFixtures($tournament, $stage),
            'round-robin' => $this->roundRobinFixtures($tournament),
            'knockout'    => $this->knockoutFixtures($tournament, $stage),
            default       => throw new \RuntimeException(
                "Unsupported tournament format: \"{$tournament->format}\"."
            ),
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Format handlers
    // ─────────────────────────────────────────────────────────────

    /**
     * Group format: every team in a group plays every other team in that group once.
     * Multiple groups are each handled independently.
     */
    private function groupFixtures(Tournament $tournament, string $stage): array
    {
        $matches = [];
        $dates   = $this->datePeriod($tournament);

        foreach ($tournament->groups as $group) {
            $teams = $group->teams->values();

            if ($teams->count() < 2) {
                throw new \RuntimeException(
                    "Group \"{$group->name}\" has fewer than 2 teams. Add more teams before generating fixtures."
                );
            }

            $matches = array_merge(
                $matches,
                $this->roundRobinPairs($teams, $tournament, $stage, $dates)
            );
        }

        return $matches;
    }

    /**
     * Round-robin format: every team plays every other team once, regardless of groups.
     * All teams from all groups are pooled together.
     */
    private function roundRobinFixtures(Tournament $tournament): array
    {
        $allTeams = $tournament->groups
            ->flatMap(fn ($g) => $g->teams)
            ->unique('id')
            ->values();

        if ($allTeams->count() < 2) {
            throw new \RuntimeException(
                "Round-robin requires at least 2 teams. Assign teams before generating fixtures."
            );
        }

        $dates = $this->datePeriod($tournament);

        // Round-robin uses 'group' as its stage label (there is no separate stage)
        return $this->roundRobinPairs($allTeams, $tournament, 'group', $dates);
    }

    /**
     * Knockout format: single-elimination bracket.
     * If team count is not a power of 2, byes are awarded to the top-seeded teams
     * (those assigned first) so the remaining teams play Round 1 matches.
     *
     * Example — 6 teams, bracket size 8:
     *   Byes  : Team 1, Team 2           (skip R1, auto-advance)
     *   R1    : Team 3 v Team 4, Team 5 v Team 6
     *   R2 has: Team 1, Team 2, winner(3/4), winner(5/6) = 4 teams
     */
    private function knockoutFixtures(Tournament $tournament, string $stage): array
    {
        $allTeams = $tournament->groups
            ->flatMap(fn ($g) => $g->teams)
            ->unique('id')
            ->values();

        $count = $allTeams->count();

        if ($count < 2) {
            throw new \RuntimeException(
                "Knockout requires at least 2 teams. Assign teams before generating fixtures."
            );
        }

        $bracketSize   = $this->nextPowerOfTwo($count);
        $byeCount      = $bracketSize - $count;
        $r1TeamCount   = $count - $byeCount; // always even (proof: 2n - 2^k is even)

        // Top $byeCount teams get automatic byes; the rest play R1
        $playingTeams = $allTeams->slice($byeCount)->values();

        // $r1TeamCount is always even by math, but guard defensively
        if ($r1TeamCount % 2 !== 0) {
            throw new \RuntimeException(
                "Odd number of teams after bye calculation ({$r1TeamCount}). This should not happen — check team assignments."
            );
        }

        $dates   = $this->datePeriod($tournament);
        $matches = [];

        for ($i = 0; $i < $playingTeams->count() - 1; $i += 2) {
            $date = $this->randomDate($dates);
            $matches[] = $this->matchRow(
                $playingTeams[$i],
                $playingTeams[$i + 1],
                $tournament,
                $stage,
                $date
            );
        }

        return $matches;
    }

    // ─────────────────────────────────────────────────────────────
    // Shared helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Generate all unique pairs from a team list (round-robin pairing).
     */
    private function roundRobinPairs(
        Collection $teams,
        Tournament $tournament,
        string     $stage,
        array      $dates
    ): array {
        $matches = [];

        for ($i = 0; $i < $teams->count(); $i++) {
            for ($j = $i + 1; $j < $teams->count(); $j++) {
                $matches[] = $this->matchRow(
                    $teams[$i],
                    $teams[$j],
                    $tournament,
                    $stage,
                    $this->randomDate($dates)
                );
            }
        }

        return $matches;
    }

    /**
     * Build a single match row for bulk insertion.
     */
    private function matchRow(
        $teamA,
        $teamB,
        Tournament $tournament,
        string     $stage,
        Carbon     $date
    ): array {
        return [
            'title'         => "{$teamA->name} vs {$teamB->name}",
            'team_a_id'     => $teamA->id,
            'team_b_id'     => $teamB->id,
            'tournament_id' => $tournament->id,
            'match_date'    => $date->format('Y-m-d H:i:s'),
            'venue'         => $tournament->location ?? null,
            'max_overs'     => $tournament->overs_per_innings ?? 20,
            'match_type'    => 'tournament',
            'status'        => 'upcoming',
            'stage'         => $stage,
            'created_at'    => now()->format('Y-m-d H:i:s'),
            'updated_at'    => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Returns an array of Carbon date objects spanning the group-stage window.
     * Reserves the last 4 days of the tournament for knockout rounds (consistent
     * with original behaviour). Falls back to full range if dates are too tight.
     */
    private function datePeriod(Tournament $tournament): array
    {
        if (!$tournament->start_date || !$tournament->end_date) {
            // No dates configured — schedule everything from today
            return [Carbon::today()];
        }

        $start = Carbon::parse($tournament->start_date);
        $end   = Carbon::parse($tournament->end_date)->subDays(4);

        // If reserved days shrink the window to nothing, use the full range
        if ($end->lt($start)) {
            $end = Carbon::parse($tournament->end_date);
        }

        $dates = iterator_to_array(CarbonPeriod::create($start, $end));

        if (empty($dates)) {
            throw new \RuntimeException(
                "Tournament date range is too narrow to schedule fixtures. Please set valid start and end dates."
            );
        }

        return $dates;
    }

    /**
     * Pick a random date from the period and assign a random time (09:00–18:30).
     * Clones the Carbon instance so the shared pool is never mutated.
     */
    private function randomDate(array $dates): Carbon
    {
        /** @var Carbon $date */
        $date = $dates[array_rand($dates)];

        return $date->copy()->setTime(rand(9, 18), rand(0, 1) === 0 ? 0 : 30);
    }

    // ─────────────────────────────────────────────────────────────
    // Public helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Validate whether the tournament is eligible to run a Super 8 or Super 4
     * next stage. Returns a structured result — never throws.
     *
     * Shape:
     *   eligible       bool    — whether the requested next stage is allowed
     *   reason         string  — human-readable explanation when not eligible
     *   total_teams    int     — total unique teams enrolled in the tournament
     *   required_teams int     — minimum teams needed for the requested stage
     *   already_exists bool    — true if next-stage fixtures are already generated
     *
     * Eligibility rules:
     *   1. Tournament format must be 'group' (no next-stage for round-robin / knockout)
     *   2. Group stage must be fully complete
     *   3. No fixtures for the requested stage must already exist
     *   4. Total enrolled teams >= required_teams (8 for super8, 4 for super4)
     */
    public function getNextStageEligibility(Tournament $tournament, string $nextStage): array
    {
        $required = $nextStage === 'super8' ? 8 : 4;

        // 1. Format guard
        if ($tournament->format !== 'group') {
            return [
                'eligible'       => false,
                'reason'         => 'Next-stage scheduling is only available for group-format tournaments.',
                'total_teams'    => 0,
                'required_teams' => $required,
                'already_exists' => false,
            ];
        }

        // 2. Group stage completion check
        $groupTotal     = CricketMatch::where('tournament_id', $tournament->id)->where('stage', 'group')->count();
        $groupCompleted = CricketMatch::where('tournament_id', $tournament->id)->where('stage', 'group')->where('status', 'completed')->count();

        if ($groupTotal === 0) {
            return [
                'eligible'       => false,
                'reason'         => 'Group stage fixtures have not been generated yet.',
                'total_teams'    => 0,
                'required_teams' => $required,
                'already_exists' => false,
            ];
        }

        if ($groupCompleted < $groupTotal) {
            $remaining = $groupTotal - $groupCompleted;
            return [
                'eligible'       => false,
                'reason'         => "Group stage is not complete — {$remaining} match(es) still pending.",
                'total_teams'    => 0,
                'required_teams' => $required,
                'already_exists' => false,
            ];
        }

        // 3. Duplicate check
        $alreadyExists = CricketMatch::where('tournament_id', $tournament->id)
                            ->where('stage', $nextStage)
                            ->exists();

        if ($alreadyExists) {
            return [
                'eligible'       => false,
                'reason'         => strtoupper($nextStage) . ' fixtures have already been generated for this tournament.',
                'total_teams'    => 0,
                'required_teams' => $required,
                'already_exists' => true,
            ];
        }

        // 4. Team count check
        $totalTeams = $tournament->groups
                        ->flatMap(fn ($g) => $g->teams)
                        ->unique('id')
                        ->count();

        if ($totalTeams < $required) {
            return [
                'eligible'       => false,
                'reason'         => strtoupper($nextStage) . " requires at least {$required} teams. This tournament only has {$totalTeams} enrolled.",
                'total_teams'    => $totalTeams,
                'required_teams' => $required,
                'already_exists' => false,
            ];
        }

        return [
            'eligible'       => true,
            'reason'         => null,
            'total_teams'    => $totalTeams,
            'required_teams' => $required,
            'already_exists' => false,
        ];
    }

    /**
     * Returns a structured status array for the group stage of a tournament.
     * Used by the controller to pass state variables to the view.
     *
     * Shape:
     *   group_fixtures_exist    bool  — at least one group-stage match has been generated
     *   group_total_count       int   — total group-stage matches
     *   group_complete_count    int   — how many are completed
     *   group_stage_complete    bool  — all group matches are completed (and at least 1 exists)
     *   next_stage_exists       bool  — playoffs or semi-final fixtures already generated
     *   can_generate_next_stage bool  — safe to show "Make Schedule" button
     *   next_stage              string — stage label to use when generating next round
     */
    public function groupStageStatus(Tournament $tournament): array
    {
        $total     = CricketMatch::where('tournament_id', $tournament->id)
                        ->where('stage', 'group')
                        ->count();

        $completed = CricketMatch::where('tournament_id', $tournament->id)
                        ->where('stage', 'group')
                        ->where('status', 'completed')
                        ->count();

        $groupStageComplete = $total > 0 && $completed === $total;

        $nextStageExists = CricketMatch::where('tournament_id', $tournament->id)
                            ->whereIn('stage', ['playoffs', 'semi-final'])
                            ->exists();

        return [
            'group_fixtures_exist'    => $total > 0,
            'group_total_count'       => $total,
            'group_complete_count'    => $completed,
            'group_stage_complete'    => $groupStageComplete,
            'next_stage_exists'       => $nextStageExists,
            'can_generate_next_stage' => $tournament->format === 'group'
                                            && $groupStageComplete
                                            && !$nextStageExists,
            'next_stage'              => 'playoffs',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Guards
    // ─────────────────────────────────────────────────────────────

    /**
     * Ensure the tournament has groups and at least one team before proceeding.
     */
    private function validateState(Tournament $tournament): void
    {
        if ($tournament->groups->isEmpty()) {
            throw new \RuntimeException(
                "No groups found for this tournament. Assign teams before generating fixtures."
            );
        }

        $hasTeams = $tournament->groups->contains(fn ($g) => $g->teams->isNotEmpty());

        if (!$hasTeams) {
            throw new \RuntimeException(
                "No teams are assigned to any group in this tournament."
            );
        }
    }

    /**
     * For group-format tournaments, block generation of any post-group stage
     * (playoffs, semi-final, final) until every group-stage match is completed.
     *
     * Does nothing for round-robin and knockout formats since they have no
     * preceding group stage to validate against.
     */
    private function validateStageProgression(Tournament $tournament, string $stage): void
    {
        // Only enforced for group format moving past the group stage
        if ($tournament->format !== 'group' || $stage === 'group') {
            return;
        }

        $total = CricketMatch::where('tournament_id', $tournament->id)
                    ->where('stage', 'group')
                    ->count();

        if ($total === 0) {
            throw new \RuntimeException(
                "Group stage fixtures have not been generated yet. Generate group fixtures before scheduling the next stage."
            );
        }

        $completed = CricketMatch::where('tournament_id', $tournament->id)
                        ->where('stage', 'group')
                        ->where('status', 'completed')
                        ->count();

        if ($completed < $total) {
            $remaining = $total - $completed;
            throw new \RuntimeException(
                "Group stage is not complete — {$remaining} match(es) still need to be played before the next stage can be scheduled."
            );
        }
    }

    /**
     * Block re-generation: if fixtures already exist for this stage, throw.
     */
    private function guardDuplicates(Tournament $tournament, string $stage): void
    {
        $exists = CricketMatch::where('tournament_id', $tournament->id)
            ->where('stage', $stage)
            ->exists();

        if ($exists) {
            throw new \RuntimeException(
                "Fixtures for the \"{$stage}\" stage already exist for this tournament. Delete them first if you want to regenerate."
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Math utility
    // ─────────────────────────────────────────────────────────────

    /**
     * Returns the smallest power of 2 that is >= $n.
     * Used to calculate the knockout bracket size and bye count.
     */
    private function nextPowerOfTwo(int $n): int
    {
        $power = 1;
        while ($power < $n) {
            $power *= 2;
        }
        return $power;
    }
}
