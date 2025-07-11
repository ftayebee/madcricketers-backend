<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'over', 'ball_number', 'bowler_id', 'batsman_id',
        'runs', 'extras', 'wicket_type', 'is_wicket', 'fielder_id'
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
