<?php

namespace App\Events;

use App\Models\CricketMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CricketMatchUpdate
{
    use InteractsWithSockets, SerializesModels;

    public $match;

    public function __construct(CricketMatch $match)
    {
        $this->match = $match;
    }

    public function broadcastOn()
    {
        return new Channel('match.' . $this->match->id);
    }

    public function broadcastAs()
    {
        return 'MatchUpdated';
    }
}
