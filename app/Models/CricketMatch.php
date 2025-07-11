<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CricketMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'team1_id', 'team2_id', 'match_date',
        'venue', 'status', 'winning_team_id'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winningTeam()
    {
        return $this->belongsTo(Team::class, 'winning_team_id');
    }

    public function players()
    {
        return $this->hasMany(MatchPlayer::class);
    }

    public function deliveries()
    {
        return $this->hasMany(MatchDelivery::class);
    }

    public function wickets()
    {
        return $this->hasMany(FallOfWicket::class);
    }

    public function partnerships()
    {
        return $this->hasMany(Partnership::class);
    }

    public function scoreboard()
    {
        return $this->hasMany(MatchScoreBoard::class);
    }
}
