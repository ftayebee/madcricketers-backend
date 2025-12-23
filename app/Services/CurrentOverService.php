<?php

namespace App\Services;

use App\Models\MatchScoreBoard;
use App\Models\MatchDelivery;

class CurrentOverService
{
    public static function get(int $matchId): array
    {
        $scoreboard = MatchScoreBoard::where('match_id', $matchId)
            ->where('status', 'running')
            ->first();

        if (!$scoreboard) {
            return [
                'current_over' => null,
                'balls' => [],
                'legalBalls' => 6
            ];
        }

        $lastDelivery = MatchDelivery::where('match_id', $matchId)
            ->where('innings', $scoreboard->innings)
            ->latest('id')
            ->first();

        $currentOverNumber = $lastDelivery ? $lastDelivery->over_number : 1;

        $deliveries = MatchDelivery::where('match_id', $matchId)
            ->where('innings', $scoreboard->innings)
            ->where('over_number', $currentOverNumber)
            ->orderBy('ball_in_over')
            ->get();

        return [
            'current_over' => $currentOverNumber,
            'balls' => $deliveries->map(fn ($d) => [
                'delivery_type' => $d->delivery_type,
                'runs_batsman'  => $d->runs_batsman,
                'runs_extras'   => $d->runs_extras,
                'is_wicket'     => $d->is_wicket,
            ]),
            'legalBalls' => $deliveries
                ->whereIn('delivery_type', ['normal', 'bye', 'leg-bye'])
                ->count()
        ];
    }
}
