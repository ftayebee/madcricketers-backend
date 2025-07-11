<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'format', 'description', 'start_date', 'end_date'];

    public function groups()
    {
        return $this->hasMany(TournamentGroup::class);
    }

    public function teams()
    {
        return $this->hasManyThrough(Team::class, TournamentGroupTeam::class, 'tournament_id', 'id', 'id', 'team_id');
    }

    public function matches()
    {
        return $this->hasMany(CricketMatch::class);
    }
}
