<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'player_type',
        'player_role',
        'batting_style',
        'bowling_style',
        'jursey_number',
        'jursey_name',
        'jursey_size',
        'chest_measurement'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'player_team')->withTimestamps();
    }

    public function matches()
    {
        return $this->belongsToMany(CricketMatch::class, 'match_players', 'match_id', 'player_id')->withTimestamps();
    }
}
