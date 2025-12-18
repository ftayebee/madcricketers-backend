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

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($match, $tossData, $battingFirstTeam, $bowlingFirstTeam)
    {
        $this->match = $match;
        $this->tossData = $tossData;
        $this->battingFirstTeam = $battingFirstTeam;
        $this->bowlingFirstTeam = $bowlingFirstTeam;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast on a public channel for match updates
        return new Channel('cricket-match.' . $this->match->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'toss.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        Log::info('Live Update Sent');
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