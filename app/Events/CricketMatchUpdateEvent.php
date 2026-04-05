<?php

namespace App\Events;

use App\Models\CricketMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CricketMatchUpdateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $match;
    public $battingTeam;
    public $bowlingTeam;
    public $scoreboard;
    public $striker;
    public $nonStriker;
    public $currentBowler;
    public $currentOver;
    public $inningsStatus;
    public $matchResult;
    public $is_tournament;

    public function __construct(CricketMatch $match, array $data = [])
    {
        $this->match         = $match;
        $this->battingTeam   = $data['battingTeam'] ?? null;
        $this->bowlingTeam   = $data['bowlingTeam'] ?? null;
        $this->scoreboard    = $data['scoreboard'] ?? null;
        $this->striker       = $data['striker'] ?? null;
        $this->nonStriker    = $data['nonStriker'] ?? null;
        $this->currentBowler = $data['currentBowler'] ?? null;
        $this->currentOver   = $data['currentOver'] ?? null;
        $this->inningsStatus = $data['inningsStatus'] ?? null;
        $this->matchResult   = $data['matchResult'] ?? null;
        $this->is_tournament = $data['is_tournament'] ?? false;
    }

    public function broadcastOn()
    {
        return new Channel('cricket-match.' . $this->match->id);
    }

    public function broadcastAs()
    {
        return 'match-updated';
    }

    public function broadcastWith()
    {
        return [
            'match_id'      => $this->match->id,
            'battingTeam'   => $this->battingTeam,
            'bowlingTeam'   => $this->bowlingTeam,
            'scoreboard'    => $this->scoreboard,
            'striker'       => $this->striker,
            'nonStriker'    => $this->nonStriker,
            'currentBowler' => $this->currentBowler,
            'currentOver'   => $this->currentOver,
            'inningsStatus' => $this->inningsStatus,
            'matchResult'   => $this->matchResult,
            'is_tournament' => $this->is_tournament,
            'timestamp'     => now()->toISOString(),
        ];
    }
}
