<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchScoreBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'team_id', 'innings', 'runs',
        'wickets', 'overs', 'extras', 'status'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

}
