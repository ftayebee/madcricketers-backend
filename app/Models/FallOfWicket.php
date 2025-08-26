<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FallOfWicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'team_id', 'wicket_number',
        'runs', 'overs', 'batter_id', 'bowler_id',
        'fielder_id', 'dismissal_type'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function batter() {
        return $this->belongsTo(MatchPlayer::class, 'batter_id');
    }

    public function bowler() {
        return $this->belongsTo(MatchPlayer::class, 'bowler_id');
    }

    public function fielder() {
        return $this->belongsTo(MatchPlayer::class, 'fielder_id');
    }
}
