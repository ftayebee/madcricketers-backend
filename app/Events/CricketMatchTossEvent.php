<?php

namespace App\Events;

use App\Models\CricketMatch;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CricketMatchTossEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $match;
    public $tossData;
    public $battingFirstTeam;
    public $bowlingFirstTeam;

    public function __construct($match, $tossData, $battingFirstTeam, $bowlingFirstTeam)
    {
        $this->match = $match;
        $this->tossData = $tossData;
        $this->battingFirstTeam = $battingFirstTeam;
        $this->bowlingFirstTeam = $bowlingFirstTeam;
    }

    public function broadcastOn()
    {
        return new Channel('cricket-match.' . $this->match->id);
    }

    public function broadcastAs()
    {
        return 'toss-updated';
    }

    public function broadcastWith()
    {
        return [
            'match' => [
                'id' => $this->match->id,
                'status' => $this->match->status,
                'toss' => $this->tossData,
            ],
            'toss_winner_team_id' => $this->tossData->toss_winner_team_id,
            'toss_decision' => $this->tossData->decision,
            'batting_first_team_id' => $this->battingFirstTeam,
            'bowling_first_team_id' => $this->bowlingFirstTeam,
            'timestamp' => now()->toISOString(),
        ];
    }
}