<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'innings',
        'over_number',
        'ball_in_over',
        'bowler_id',
        'batsman_id',
        'non_striker_id',
        'batting_team_id',
        'bowling_team_id',
        'runs_batsman',
        'runs_extras',
        'delivery_type',
        'is_wicket',
        'wicket_type',
        'wicket_player_id',
        'fielder_id'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class);
    }

    public function batsman()
    {
        return $this->belongsTo(Player::class, 'batsman_id');
    }

    public function bowler()
    {
        return $this->belongsTo(Player::class, 'bowler_id');
    }

    public function fielder()
    {
        return $this->belongsTo(Player::class, 'fielder_id');
    }
}
