<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Admin\CricketMatchController as AdminCricketMatchController;
use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\CricketMatchToss;
use App\Models\MatchPlayer;
use App\Models\MatchScoreBoard;
use App\Models\Team;
use App\Models\TournamentTeamStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MatchManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePermission($request, 'cricket-matches-view');

        $matches = CricketMatch::with(['teamA', 'teamB', 'tournament', 'scoreboard.team', 'toss'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->date, fn ($query) => $query->whereDate('match_date', $request->date))
            ->latest('match_date')
            ->paginate((int) $request->input('per_page', 20));

        $matches->getCollection()->transform(fn (CricketMatch $match) => $this->matchPayload($match));

        return response()->json(['success' => true, 'data' => $matches]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission($request, 'cricket-matches-create');

        if ($request->filled('category') || $request->has('team_a_players') || $request->has('team_b_players')) {
            return $this->storeCasualMatch($request);
        }

        $validated = $request->validate($this->rules());

        $teamA = Team::findOrFail($validated['team_a_id']);
        $teamB = Team::findOrFail($validated['team_b_id']);

        $match = CricketMatch::create([
            'title' => $validated['title'] ?? "{$teamA->name} vs {$teamB->name}",
            'team_a_id' => $validated['team_a_id'],
            'team_b_id' => $validated['team_b_id'],
            'tournament_id' => $validated['tournament_id'] ?? null,
            'match_date' => $validated['match_date'],
            'venue' => $validated['venue'] ?? null,
            'match_type' => $validated['match_type'] ?? (($validated['tournament_id'] ?? null) ? 'tournament' : 'regular'),
            'status' => $validated['status'] ?? 'upcoming',
            'max_overs' => $validated['max_overs'] ?? 20,
            'bowler_max_overs' => $validated['bowler_max_overs'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Match created successfully.',
            'data' => $this->matchPayload($match->load(['teamA', 'teamB', 'tournament', 'scoreboard.team', 'toss'])),
        ], 201);
    }

    public function show(Request $request, CricketMatch $match)
    {
        $this->authorizePermission($request, 'cricket-matches-view');

        return response()->json([
            'success' => true,
            'data' => $this->matchPayload($match->load(['teamA.players.user', 'teamB.players.user', 'tournament', 'scoreboard.team', 'toss'])),
        ]);
    }

    public function update(Request $request, CricketMatch $match)
    {
        $this->authorizePermission($request, 'cricket-matches-edit');

        if ($match->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Completed matches cannot be edited.'], 422);
        }

        $validated = $request->validate($this->rules(true));

        if (isset($validated['team_a_id'], $validated['team_b_id'])) {
            $teamA = Team::findOrFail($validated['team_a_id']);
            $teamB = Team::findOrFail($validated['team_b_id']);
            $validated['title'] = $validated['title'] ?? "{$teamA->name} vs {$teamB->name}";
        }

        $match->fill($validated)->save();

        return response()->json([
            'success' => true,
            'message' => 'Match updated successfully.',
            'data' => $this->matchPayload($match->load(['teamA', 'teamB', 'tournament', 'scoreboard.team', 'toss'])),
        ]);
    }

    public function start(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizePermission($request, 'cricket-matches-start');

        $request->merge(['status' => 'live']);

        return $adminController->startCricketMatch($match->id);
    }

    public function toss(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizePermission($request, 'cricket-matches-toss');

        $validated = $request->validate([
            'toss_winner_team_id' => ['required', 'exists:teams,id'],
            'toss_decision' => ['required', 'in:bat,bowl,BAT,BOWL'],
        ]);

        $request->merge([
            'match_id' => $match->id,
            'toss_winner_team_id' => $validated['toss_winner_team_id'],
            'toss_decision' => $validated['toss_decision'],
        ]);

        return $adminController->storeToss($request);
    }

    public function complete(Request $request, CricketMatch $match)
    {
        $this->authorizePermission($request, 'cricket-matches-scoreboard');

        DB::transaction(function () use ($match) {
            $scoreboards = MatchScoreBoard::where('match_id', $match->id)->orderBy('innings')->get();
            $first = $scoreboards->where('innings', 1)->first();
            $second = $scoreboards->where('innings', 2)->first();

            if ($first && $second) {
                if ($first->runs > $second->runs) {
                    $match->winning_team_id = $first->team_id;
                    $match->result_summary = optional($first->team)->name . ' won by ' . ($first->runs - $second->runs) . ' runs';
                } elseif ($second->runs > $first->runs) {
                    $remainingWickets = max(0, optional($second->team)->players()->count() - 1 - $second->wickets);
                    $match->winning_team_id = $second->team_id;
                    $match->result_summary = optional($second->team)->name . " won by {$remainingWickets} wickets";
                } else {
                    $match->winning_team_id = null;
                    $match->result_summary = 'Match tied';
                }
            }

            MatchScoreBoard::where('match_id', $match->id)->update(['status' => 'ended']);
            $match->status = 'completed';
            $match->save();
            $this->syncTournamentStanding($match);
        });

        return response()->json([
            'success' => true,
            'message' => 'Match completed successfully.',
            'data' => $this->matchPayload($match->refresh()->load(['teamA', 'teamB', 'tournament', 'scoreboard.team', 'toss'])),
        ]);
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'team_a_id' => [$required, 'exists:teams,id', 'different:team_b_id'],
            'team_b_id' => [$required, 'exists:teams,id', 'different:team_a_id'],
            'tournament_id' => ['nullable', 'exists:tournaments,id'],
            'match_date' => [$required, 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'max_overs' => ['nullable', 'integer', 'min:1'],
            'bowler_max_overs' => ['nullable', 'integer', 'min:0'],
            'match_type' => ['nullable', 'in:tournament,regular'],
            'status' => ['nullable', 'in:upcoming,live,completed'],
        ];
    }

    private function storeCasualMatch(Request $request)
    {
        $categories = $this->casualCategories();

        $validated = $request->validate([
            'category' => ['required', 'in:' . implode(',', array_keys($categories))],
            'team_a_value' => ['required', 'string', 'max:100'],
            'team_b_value' => ['required', 'string', 'max:100'],
            'team_a_players' => ['required', 'array', 'min:1'],
            'team_a_players.*' => ['required', 'integer', 'exists:players,id'],
            'team_b_players' => ['required', 'array', 'min:1'],
            'team_b_players.*' => ['required', 'integer', 'exists:players,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'max_overs' => ['required', 'integer', 'min:1'],
            'bowler_max_overs' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:upcoming,live,completed'],
        ], [
            'team_a_players.required' => 'Please select at least one player for Team A.',
            'team_b_players.required' => 'Please select at least one player for Team B.',
        ]);

        $teamAPlayers = array_map('intval', $validated['team_a_players']);
        $teamBPlayers = array_map('intval', $validated['team_b_players']);
        if (array_intersect($teamAPlayers, $teamBPlayers)) {
            return response()->json([
                'success' => false,
                'message' => 'The same player cannot appear in both teams.',
                'errors' => ['team_a_players' => ['The same player cannot appear in both teams.']],
            ], 422);
        }

        $match = DB::transaction(function () use ($validated, $categories, $teamAPlayers, $teamBPlayers) {
            $teamAValue = trim($validated['team_a_value']);
            $teamBValue = trim($validated['team_b_value']);
            $teamAName = $this->generateCasualTeamName($validated['category'], $teamAValue);
            $teamBName = $this->generateCasualTeamName($validated['category'], $teamBValue);
            $categoryLabel = $categories[$validated['category']] ?? $validated['category'];

            $teamA = Team::create([
                'name' => $teamAName,
                'slug' => $this->uniqueTeamSlug($teamAName),
                'description' => "Generated team - {$categoryLabel}: {$teamAValue}",
            ]);

            $teamB = Team::create([
                'name' => $teamBName,
                'slug' => $this->uniqueTeamSlug($teamBName),
                'description' => "Generated team - {$categoryLabel}: {$teamBValue}",
            ]);

            $match = CricketMatch::create([
                'title' => $validated['title'] ?? "{$teamAName} vs {$teamBName}",
                'team_a_id' => $teamA->id,
                'team_b_id' => $teamB->id,
                'tournament_id' => null,
                'match_date' => $validated['match_date'],
                'venue' => $validated['venue'] ?? null,
                'match_type' => 'regular',
                'status' => $validated['status'],
                'max_overs' => $validated['max_overs'],
                'bowler_max_overs' => $validated['bowler_max_overs'] ?? null,
            ]);

            $this->assignPlayersToCasualMatch($match, $teamA, $teamAPlayers);
            $this->assignPlayersToCasualMatch($match, $teamB, $teamBPlayers);

            return $match;
        });

        return response()->json([
            'success' => true,
            'message' => 'Casual match created successfully.',
            'data' => $this->matchPayload($match->load(['teamA.players.user', 'teamB.players.user', 'tournament', 'scoreboard.team', 'toss'])),
        ], 201);
    }

    private function casualCategories(): array
    {
        return [
            'favourite_football_country' => 'Favourite Football Country',
            'favourite_cricket_country' => 'Favourite Cricket Country',
            'favourite_football_league_team' => 'Favourite Football League Team',
            'married_status' => 'Married Status',
            'education_batch' => 'Education Batch',
            'ssc_batch' => 'SSC Batch',
        ];
    }

    private function generateCasualTeamName(string $category, string $value): string
    {
        $value = trim($value);
        switch ($category) {
            case 'favourite_football_country':
            case 'favourite_cricket_country':
            case 'favourite_football_league_team':
                return strtoupper($value) . ' XI';
            case 'married_status':
                return ucfirst(strtolower($value)) . ' XI';
            case 'education_batch':
                return 'Batch ' . $value . ' XI';
            case 'ssc_batch':
                return 'SSC ' . $value . ' XI';
            default:
                return $value . ' XI';
        }
    }

    private function uniqueTeamSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $counter = 1;

        while (Team::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function assignPlayersToCasualMatch(CricketMatch $match, Team $team, array $playerIds): void
    {
        foreach ($playerIds as $playerId) {
            MatchPlayer::create([
                'match_id' => $match->id,
                'team_id' => $team->id,
                'player_id' => $playerId,
                'status' => 'fielding',
            ]);

            $team->players()->attach($playerId, [
                'match_id' => $match->id,
                'tournament_id' => null,
            ]);
        }
    }

    private function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->can($permission), 403, 'Unauthorized Access');
    }

    private function matchPayload(CricketMatch $match): array
    {
        return [
            'id' => $match->id,
            'title' => $match->title,
            'match_date' => $match->match_date ? (string) $match->match_date : null,
            'venue' => $match->venue,
            'match_type' => $match->match_type,
            'status' => $match->status,
            'max_overs' => $match->max_overs,
            'bowler_max_overs' => $match->bowler_max_overs,
            'result_summary' => $match->result_summary,
            'team_a' => $this->teamSummary($match->teamA),
            'team_b' => $this->teamSummary($match->teamB),
            'tournament' => $match->tournament,
            'toss' => $match->toss,
            'scoreboards' => $match->scoreboard,
        ];
    }

    private function teamSummary(?Team $team): ?array
    {
        return $team ? [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'logo' => $team->logo,
        ] : null;
    }

    private function syncTournamentStanding(CricketMatch $match): void
    {
        if (!$match->tournament_id || $match->status !== 'completed') {
            return;
        }

        foreach ([$match->team_a_id, $match->team_b_id] as $teamId) {
            $stat = TournamentTeamStat::firstOrCreate(
                ['tournament_id' => $match->tournament_id, 'team_id' => $teamId],
                ['matches_played' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0, 'points' => 0, 'nrr' => 0]
            );

            $stat->matches_played = CricketMatch::where('tournament_id', $match->tournament_id)
                ->where('status', 'completed')
                ->where(fn ($query) => $query->where('team_a_id', $teamId)->orWhere('team_b_id', $teamId))
                ->count();
            $stat->wins = CricketMatch::where('tournament_id', $match->tournament_id)->where('status', 'completed')->where('winning_team_id', $teamId)->count();
            $stat->draws = CricketMatch::where('tournament_id', $match->tournament_id)->where('status', 'completed')->whereNull('winning_team_id')
                ->where(fn ($query) => $query->where('team_a_id', $teamId)->orWhere('team_b_id', $teamId))
                ->count();
            $stat->losses = max(0, $stat->matches_played - $stat->wins - $stat->draws);
            $stat->points = ($stat->wins * 2) + $stat->draws;
            $stat->save();
        }
    }
}
