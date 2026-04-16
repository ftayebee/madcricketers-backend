<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    use HasFactory;

    protected $fillable = ['match_id', 'player_id', 'team_id', 'status', 'overs_bowled', 'runs_conceded', 'wickets_taken', 'runs_scored', 'balls_faced', 'fours', 'sixes'];

    protected $casts = [
        'overs_bowled' => 'float',
        'runs_conceded' => 'integer',
        'wickets_taken' => 'integer',
        'runs_scored' => 'integer',
        'balls_faced' => 'integer',
        'fours' => 'integer',
        'sixes' => 'integer',
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class, 'match_id', 'id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
