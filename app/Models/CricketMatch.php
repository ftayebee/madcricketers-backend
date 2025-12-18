<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CricketMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'team1_id',
        'team2_id',
        'match_date',
        'venue',
        'status',
        'winning_team_id',
        'bowler_max_overs',
        'max_overs',
        'result_summary'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function toss()
    {
        return $this->hasOne(CricketMatchToss::class, 'cricket_match_id', 'id');
    }

    public function teamA()
    {
        return $this->belongsTo(Team::class, 'team_a_id', 'id');
    }

    public function teamB()
    {
        return $this->belongsTo(Team::class, 'team_b_id', 'id');
    }

    public function winningTeam()
    {
        return $this->belongsTo(Team::class, 'winning_team_id');
    }

    public function players()
    {
        return $this->hasMany(MatchPlayer::class, 'match_id', 'id');
    }

    public function deliveries()
    {
        return $this->hasMany(MatchDelivery::class, 'match_id', 'id');
    }

    public function wickets()
    {
        return $this->hasMany(FallOfWicket::class, 'match_id', 'id');
    }

    public function partnerships()
    {
        return $this->hasMany(Partnership::class, 'match_id', 'id');
    }

    public function scoreboard()
    {
        return $this->hasMany(MatchScoreBoard::class, 'match_id', 'id');
    }

    public function getOpponentTeamAttribute()
    {
        if ($this->match) {
            if ($this->team_id) {
                if ($this->team_id == $this->match->team_a_id) {
                    return $this->match->teamB;
                } else {
                    return $this->match->teamA;
                }
            }

            if ($this->player && $this->player->teams()->exists()) {
                $playerTeam = $this->player->teams()->first();
                if ($playerTeam->id == $this->match->team_a_id) {
                    return $this->match->teamB;
                } else {
                    return $this->match->teamA;
                }
            }
        }

        return null;
    }
}
