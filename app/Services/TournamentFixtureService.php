<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Tournament;
use App\Models\CricketMatch;
use App\Models\TournamentGroup;
use App\Models\TournamentGroupTeam;
use App\Models\TournamentTeamStat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     *   next_stage_exists       bool  — any post-group-stage fixtures exist (playoffs/semi-final/super8)
     *   super8_exists           bool  — super8 fixtures specifically have been generated
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

        $super8Exists = CricketMatch::where('tournament_id', $tournament->id)
                            ->where('stage', 'super8')
                            ->exists();

        $nextStageExists = $super8Exists || CricketMatch::where('tournament_id', $tournament->id)
                            ->whereIn('stage', ['playoffs', 'semi-final'])
                            ->exists();

        return [
            'group_fixtures_exist'    => $total > 0,
            'group_total_count'       => $total,
            'group_complete_count'    => $completed,
            'group_stage_complete'    => $groupStageComplete,
            'next_stage_exists'       => $nextStageExists,
            'super8_exists'           => $super8Exists,
            'can_generate_next_stage' => $tournament->format === 'group'
                                            && $groupStageComplete
                                            && !$nextStageExists,
            'next_stage'              => 'playoffs',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Super 8 generation
    // ─────────────────────────────────────────────────────────────

    /**
     * Full Super 8 generation pipeline. Owns the entire flow:
     *   1. Eligibility check (group stage complete, 8+ teams, no duplicates)
     *   2. Rank teams within each group by computed points → NRR → DB order
     *   3. Select top N qualifiers per group (N = 8 ÷ group_count)
     *   4. Create two "Super 8 - Group X" records in tournament_groups
     *   5. Assign qualifiers using cross-seeding: (group_index + rank) % 2
     *   6. Generate round-robin fixtures within each Super 8 group
     *   7. Bulk-insert matches — all wrapped in a DB transaction
     *
     * Returns the number of fixtures inserted.
     *
     * Cross-seeding example (4 groups, 2 qualifiers each):
     *   S8-G1 ← G1-1st, G2-2nd, G3-1st, G4-2nd
     *   S8-G2 ← G1-2nd, G2-1st, G3-2nd, G4-1st
     * No two teams from the same original group end up in the same Super 8 group.
     *
     * Assumptions:
     *   - Points are computed live from cricket_matches (winning_team_id).
     *     TournamentTeamStat.nrr is used as a secondary sort but defaults to 0
     *     when Phase-1 post-match stat updates are not yet implemented.
     *   - Valid group counts: 1, 2, 4, 8 (any integer where 8 % count === 0).
     *
     * @throws \RuntimeException on any eligibility or configuration failure
     */
    public function generateSuper8(Tournament $tournament): int
    {
        // ── 1. Eligibility ──────────────────────────────────────────
        $eligibility = $this->getNextStageEligibility($tournament, 'super8');
        if (!$eligibility['eligible']) {
            throw new \RuntimeException($eligibility['reason']);
        }

        $groups     = $tournament->groups->loadMissing('teams');
        $groupCount = $groups->count();

        if ($groupCount < 1 || 8 % $groupCount !== 0) {
            throw new \RuntimeException(
                "Super 8 requires 8 total qualifiers. With {$groupCount} group(s) the number cannot divide evenly. " .
                "Supported group counts: 1, 2, 4, or 8."
            );
        }

        $qualifiersPerGroup = intdiv(8, $groupCount);

        // ── 2 & 3. Rank and select qualifiers ───────────────────────
        $rankings = $this->computeGroupRankings($tournament);

        $qualifiedByGroup = $groups->values()->map(function ($group, $gi) use ($rankings, $qualifiersPerGroup) {
            $ranked     = $rankings->get($group->id, collect());
            $qualifiers = $ranked->take($qualifiersPerGroup)->pluck('team');

            if ($qualifiers->count() < $qualifiersPerGroup) {
                throw new \RuntimeException(
                    "Group \"{$group->name}\" only has {$qualifiers->count()} ranked team(s) " .
                    "but {$qualifiersPerGroup} qualifier(s) are required for Super 8."
                );
            }

            return [
                'group_index' => $gi,
                'group'       => $group,
                'qualifiers'  => $qualifiers,
            ];
        });

        // Verify final total
        $totalQualified = $qualifiedByGroup->sum(fn ($g) => $g['qualifiers']->count());
        if ($totalQualified < 8) {
            throw new \RuntimeException(
                "Only {$totalQualified} team(s) qualified (need 8). " .
                "Ensure all groups have enough teams with completed matches."
            );
        }

        $dates      = $this->super8DatePeriod($tournament);
        $matchCount = 0;

        DB::transaction(function () use ($tournament, $qualifiedByGroup, $dates, &$matchCount) {

            // ── 4. Create two Super 8 groups ────────────────────────
            $s8Groups = [
                TournamentGroup::create([
                    'tournament_id' => $tournament->id,
                    'name'          => 'Super 8 - Group 1',
                ]),
                TournamentGroup::create([
                    'tournament_id' => $tournament->id,
                    'name'          => 'Super 8 - Group 2',
                ]),
            ];

            // ── 5. Assign qualifiers with cross-seeding ─────────────
            foreach ($qualifiedByGroup as $groupData) {
                $gi = $groupData['group_index'];

                foreach ($groupData['qualifiers']->values() as $r => $team) {
                    // Formula: teams from the same original group always land in
                    // different Super 8 groups because adjacent ranks flip groups.
                    $s8GroupIndex = ($gi + $r) % 2;

                    TournamentGroupTeam::create([
                        'tournament_id' => $tournament->id,
                        'group_id'      => $s8Groups[$s8GroupIndex]->id,
                        'team_id'       => $team->id,
                    ]);
                }
            }

            // ── 6. Generate round-robin within each Super 8 group ───
            $matches = [];

            foreach ($s8Groups as $s8Group) {
                $s8Group->load('teams');
                $teams = $s8Group->teams->values();

                if ($teams->count() < 2) {
                    // Shouldn't happen if the seeding above worked, but guard it
                    continue;
                }

                $matches = array_merge(
                    $matches,
                    $this->roundRobinPairs($teams, $tournament, 'super8', $dates)
                );
            }

            // ── 7. Bulk insert ───────────────────────────────────────
            if (!empty($matches)) {
                CricketMatch::insert($matches);
                $matchCount = count($matches);
            }
        });

        return $matchCount;
    }

    /**
     * Compute per-group team rankings from live match results.
     *
     * Ranking criteria (in order):
     *   1. Points  — win = 2, draw = 1, loss = 0  (computed from winning_team_id)
     *   2. NRR     — read from TournamentTeamStat.nrr (defaults to 0 if not updated)
     *   3. Stability — Collection preserves insertion order as final tiebreaker
     *
     * Returns a Collection keyed by group_id. Each value is an ordered Collection
     * of arrays: [team, points, nrr, wins, losses, played].
     *
     * NOTE: TournamentTeamStat.nrr is only reliable once Phase-1 post-match stat
     * hooks are implemented. Until then, equal-points teams are ordered by DB
     * insertion sequence, which is consistent and repeatable.
     */
    private function computeGroupRankings(Tournament $tournament): Collection
    {
        // All completed group-stage matches for this tournament
        $completedGroupMatches = CricketMatch::where('tournament_id', $tournament->id)
            ->where('stage', 'group')
            ->where('status', 'completed')
            ->get();

        // Stored stats keyed by team_id (NRR may be 0 if Phase-1 not done)
        $storedStats = TournamentTeamStat::where('tournament_id', $tournament->id)
            ->get()
            ->keyBy('team_id');

        $rankingsByGroup = collect();

        foreach ($tournament->groups as $group) {
            $teamIds = $group->teams->pluck('id');

            // Only include matches where both teams belong to this specific group
            $groupMatches = $completedGroupMatches->filter(
                fn ($m) => $teamIds->contains($m->team_a_id) && $teamIds->contains($m->team_b_id)
            );

            $ranked = $group->teams->map(function ($team) use ($groupMatches, $storedStats) {
                $played  = $groupMatches->filter(
                    fn ($m) => $m->team_a_id === $team->id || $m->team_b_id === $team->id
                );
                $wins    = $played->where('winning_team_id', $team->id)->count();
                $draws   = $played->whereNull('winning_team_id')->count();
                $losses  = $played->count() - $wins - $draws;
                $points  = ($wins * 2) + $draws;
                $nrr     = (float) ($storedStats->get($team->id)?->nrr ?? 0);

                return [
                    'team'   => $team,
                    'points' => $points,
                    'nrr'    => $nrr,
                    'wins'   => $wins,
                    'losses' => $losses,
                    'played' => $played->count(),
                ];
            })
            ->sort(function ($a, $b) {
                // Primary: more points is better
                if ($a['points'] !== $b['points']) {
                    return $b['points'] <=> $a['points'];
                }
                // Secondary: higher NRR is better
                return $b['nrr'] <=> $a['nrr'];
            })
            ->values();

            $rankingsByGroup->put($group->id, $ranked);
        }

        return $rankingsByGroup;
    }

    /**
     * Date pool for Super 8 fixtures.
     * Uses the last 8 days of the tournament window (the period reserved for
     * post-group knockout rounds). Falls back to today+2 if no dates are set.
     */
    private function super8DatePeriod(Tournament $tournament): array
    {
        if (!$tournament->end_date) {
            return [Carbon::today()->addDays(2)];
        }

        $end   = Carbon::parse($tournament->end_date);
        $start = $end->copy()->subDays(8);

        // Never go before the tournament's own start date
        if ($tournament->start_date) {
            $earliest = Carbon::parse($tournament->start_date);
            if ($start->lt($earliest)) {
                $start = $earliest;
            }
        }

        $dates = iterator_to_array(CarbonPeriod::create($start, $end));

        return empty($dates) ? [$end] : $dates;
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
