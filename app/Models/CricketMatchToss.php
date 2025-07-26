<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CricketMatchToss extends Model
{
    use HasFactory;

    protected $fillable = ['cricket_match_id', 'toss_winner_team_id', 'decision'];
}
