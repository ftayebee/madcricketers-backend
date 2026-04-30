<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Admin\CricketMatchController as AdminCricketMatchController;
use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\MatchDelivery;
use App\Models\MatchScoreBoard;
use Illuminate\Http\Request;

class ScoreboardManagementController extends Controller
{
    public function show(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-view', 'scoreboard-edit']);

        $request->merge(['match_id' => $match->id]);

        return $adminController->getFullMatchState($request);
    }

    public function score(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);

        $request->validate($this->deliveryRules());
        $request->merge(['match_id' => $match->id]);

        return $this->withFreshState($adminController->storeDelivery($request), $request, $match, $adminController);
    }

    public function extras(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);

        $request->validate(array_merge($this->deliveryRules(), [
            'extras' => ['required', 'array'],
            'extras.type' => ['required', 'in:NB,WD,LB,B,no-ball,wide,leg-bye,bye'],
        ]));
        $request->merge(['match_id' => $match->id]);

        return $this->withFreshState($adminController->storeDelivery($request), $request, $match, $adminController);
    }

    public function wicket(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);

        $request->validate(array_merge($this->deliveryRules(), [
            'wicket' => ['required', 'string', 'in:bowled,caught,lbw,run out,run_out,stumped,hit wicket,hit-wicket,retired hurt,retired-hurt'],
            'batsman_out' => ['required', 'exists:players,id'],
        ]));
        $request->merge(['match_id' => $match->id]);

        $request->merge([
            'wicket' => $this->deliveryWicketType($request->input('wicket')),
        ]);

        return $this->withFreshState($adminController->storeDelivery($request), $request, $match, $adminController);
    }

    public function undo(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);
        $request->merge(['match_id' => $match->id]);

        return $this->withFreshState($adminController->undoLastDelivery($request), $request, $match, $adminController);
    }

    public function endInnings(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);
        $request->merge(['match_id' => $match->id]);

        return $this->withFreshState($adminController->setInningsStatus($request), $request, $match, $adminController);
    }

    public function complete(Request $request, CricketMatch $match, \App\Http\Controllers\API\MatchManagementController $matchController)
    {
        return $matchController->complete($request, $match);
    }

    public function selectBatsman(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);

        $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['required', 'exists:players,id'],
            'role' => ['required', 'in:on-strike,batting'],
        ]);
        $request->merge(['match_id' => $match->id]);

        return $this->withFreshState($adminController->selectBatsman($request), $request, $match, $adminController);
    }

    public function selectBowler(Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $this->authorizeAny($request, ['cricket-matches-scoreboard', 'scoreboard-edit']);
        $this->ensureLiveMatch($match);

        $validated = $request->validate([
            'team_id' => ['nullable', 'exists:teams,id'],
            'bowler_id' => ['required_without:player_id', 'exists:players,id'],
            'player_id' => ['required_without:bowler_id', 'exists:players,id'],
        ]);

        $runningScoreboard = MatchScoreBoard::where('match_id', $match->id)->where('status', 'running')->first();
        $bowlingTeamId = $validated['team_id'] ?? (
            $runningScoreboard && (int) $runningScoreboard->team_id === (int) $match->team_a_id
                ? $match->team_b_id
                : $match->team_a_id
        );
        $bowlerId = $validated['bowler_id'] ?? $validated['player_id'];

        if ($match->bowler_max_overs > 0) {
            $bowler = \App\Models\MatchPlayer::where('match_id', $match->id)
                ->where('team_id', $bowlingTeamId)
                ->where('player_id', $bowlerId)
                ->first();

            if ($bowler && $this->oversToBalls($bowler->overs_bowled) >= ((int) $match->bowler_max_overs * 6)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This bowler has already reached the maximum overs allowed.',
                ], 422);
            }
        }

        $previousOverBowlerId = $this->previousCompletedOverBowlerId($match, $runningScoreboard);
        if ($previousOverBowlerId && (int) $previousOverBowlerId === (int) $bowlerId) {
            return response()->json([
                'success' => false,
                'message' => 'The same bowler cannot bowl consecutive overs.',
            ], 422);
        }

        $request->merge([
            'match_id' => $match->id,
            'team_id' => $bowlingTeamId,
            'bowler_id' => $bowlerId,
        ]);

        return $this->withFreshState($adminController->chooseBowler($request), $request, $match, $adminController);
    }

    private function deliveryRules(): array
    {
        return [
            'striker_id' => ['required', 'exists:players,id'],
            'non_striker_id' => ['required', 'exists:players,id'],
            'bowler_id' => ['required', 'exists:players,id'],
            'runs' => ['required', 'integer', 'min:0', 'max:6'],
            'extras' => ['nullable', 'array'],
            'extras.runs' => ['nullable', 'integer', 'min:0'],
            'extras.run_out' => ['nullable', 'boolean'],
            'extras.batsman_out' => ['nullable', 'exists:players,id'],
            'extras.caught_by' => ['nullable', 'exists:players,id'],
            'extras.stumped_by' => ['nullable', 'exists:players,id'],
            'caught_by' => ['nullable', 'exists:players,id'],
            'stumped_by' => ['nullable', 'exists:players,id'],
            'fielder_id' => ['nullable', 'exists:players,id'],
            'wicket' => ['nullable', 'string'],
            'batsman_out' => ['nullable', 'exists:players,id'],
        ];
    }

    private function authorizeAny(Request $request, array $permissions): void
    {
        $user = $request->user();

        abort_unless($user && collect($permissions)->contains(fn ($permission) => $user->can($permission)), 403, 'Unauthorized Access');
    }

    private function ensureLiveMatch(CricketMatch $match): void
    {
        abort_if($match->status === 'completed', 422, 'Match is already completed.');
    }

    private function withFreshState($response, Request $request, CricketMatch $match, AdminCricketMatchController $adminController)
    {
        $payload = json_decode($response->getContent(), true) ?: [];
        $status = $response->getStatusCode();

        if ($status >= 400 || ($payload['success'] ?? true) === false) {
            return response()->json($payload, $status);
        }

        $stateRequest = Request::create('/', 'GET', ['match_id' => $match->id]);
        $stateRequest->setUserResolver(fn () => $request->user());
        $freshResponse = $adminController->getFullMatchState($stateRequest);
        $payload['fresh_state'] = json_decode($freshResponse->getContent(), true) ?: null;

        return response()->json($payload, $status);
    }

    private function previousCompletedOverBowlerId(CricketMatch $match, ?MatchScoreBoard $runningScoreboard): ?int
    {
        if (! $runningScoreboard) {
            return null;
        }

        $lastCompletedOver = MatchDelivery::where('match_id', $match->id)
            ->where('innings', $runningScoreboard->innings)
            ->whereNotIn('delivery_type', ['wide', 'no-ball'])
            ->select('over_number')
            ->groupBy('over_number')
            ->havingRaw('COUNT(*) = 6')
            ->orderByDesc('over_number')
            ->first();

        if (! $lastCompletedOver) {
            return null;
        }

        return MatchDelivery::where('match_id', $match->id)
            ->where('innings', $runningScoreboard->innings)
            ->where('over_number', $lastCompletedOver->over_number)
            ->whereNotIn('delivery_type', ['wide', 'no-ball'])
            ->value('bowler_id');
    }

    private function oversToBalls($overs): int
    {
        [$overPart, $ballPart] = array_pad(explode('.', (string) ($overs ?? '0.0')), 2, 0);

        return ((int) $overPart * 6) + (int) $ballPart;
    }

    private function deliveryWicketType(string $type): string
    {
        switch (strtolower(str_replace('-', ' ', $type))) {
            case 'run out':
                return 'run out';
            case 'hit wicket':
                return 'hit wicket';
            case 'retired hurt':
                return 'retired hurt';
            default:
                return strtolower($type);
        }
    }
}
