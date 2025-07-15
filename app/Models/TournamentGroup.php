<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentGroup extends Model
{
    use HasFactory;

    protected $fillable = ['tournament_id', 'name'];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'tournament_group_teams', 'group_id', 'team_id');
    }
}
