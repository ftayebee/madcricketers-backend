<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','location','description','start_date','end_date','status','trophy_image','logo', 'format', 'has_knockout'];

    public function groups()
    {
        return $this->hasMany(TournamentGroup::class);
    }

    public function matches()
    {
        return $this->hasMany(CricketMatch::class);
    }

    public function standings() {
        return $this->hasMany(TournamentTeamStat::class, 'tournament_id', 'id');
    }

    public function playerStats()
    {
        return $this->hasMany(TournamentPlayerStat::class, 'tournament_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'tournament_group_teams', 'tournament_id', 'team_id');
    }
}
