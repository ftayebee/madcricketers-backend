<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partnership extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'team_id', 'batter_1_id', 'batter_2_id',
        'runs', 'balls', 'start_over', 'end_over', 'wicket_id'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function batter1()
    {
        return $this->belongsTo(Player::class, 'batter_1_id');
    }

    public function batter2()
    {
        return $this->belongsTo(Player::class, 'batter_2_id');
    }
}
