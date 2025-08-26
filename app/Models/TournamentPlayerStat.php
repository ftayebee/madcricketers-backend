<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentPlayerStat extends Model
{
    use HasFactory;

    protected $table = 'tournament_player_stats';

    protected $fillable = [
        'tournament_id',
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
