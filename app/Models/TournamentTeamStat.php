<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentTeamStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'team_id',
        'matches_played',
        'wins',
        'losses',
        'draws',
        'points',
        'nrr',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
