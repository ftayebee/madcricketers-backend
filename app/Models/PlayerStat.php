<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerStat extends Model
{
    protected $table = 'player_statistics';

    use HasFactory;

    protected $fillable = [
        'player_id',
        'matches_played',
        'innings_batted',
        'total_runs',
        'balls_faced',
        'fifties',
        'hundreds',
        'sixes',
        'fours',
        'strike_rate',
        'average',
        'innings_bowled',
        'overs_bowled',
        'runs_conceded',
        'wickets',
        'bowling_average',
        'economy_rate',
        'catches',
        'runouts',
        'stumpings',
    ];
}
