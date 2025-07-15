<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentGroupTeam extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'tournament_id', 'team_id'];

    public function group()
    {
        return $this->belongsTo(TournamentGroup::class, 'group_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
