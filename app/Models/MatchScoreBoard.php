<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchScoreBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
        'innings',
        'runs',
        'wickets',
        'overs',
        'extras',
        'status'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function battingPlayers()
    {
        return MatchPlayer::where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->whereIn('type', ['batting','on-strike','bowled','caught','bowling','run_out','lbw','retired-hurt','fielding','hit-wicket','closed','stumped','ready'])
            ->orderBy('batting_order')
            ->get()
            ->map(function ($player) {
                return [
                    'id' => $player->player_id,
                    'runs' => $player->runs,
                    'balls' => $player->balls,
                    'status' => $player->status,
                    'strikeRate' => $player->balls > 0 ? round(($player->runs / $player->balls) * 100, 2) : 0,
                    'fours' => $player->fours,
                    'sixes' => $player->sixes
                ];
            });
    }

    public function bowlingPlayers()
    {
        return MatchPlayer::where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->whereIn('status', ['bowling', 'fielding', 'wicket-keeper'])
            ->get()
            ->map(function ($player) {
                return [
                    'id' => $player->player_id,
                    'overs' => $player->overs,
                    'maidens' => $player->maidens,
                    'runs' => $player->runs,
                    'wickets' => $player->wickets,
                    'economy' => $player->overs > 0 ? round($player->runs / $player->overs, 2) : 0
                ];
            });
    }

    public function fallOfWickets()
    {
        return FallOfWicket::where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->orderBy('wicket_number')
            ->get(['player_name', 'score', 'over'])
            ->toArray();
    }

    public function partnerships()
    {
        return Partnership::where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->orderBy('start_over')
            ->get()
            ->map(function ($p) {
                return [
                    'batsmen' => json_decode($p->batsmen),
                    'runs' => $p->runs,
                    'balls' => $p->balls
                ];
            });
    }

    public function yetToBatPlayers()
    {
        $allPlayers = Player::where('team_id', $this->team_id)->pluck('id');
        $battedPlayers = PlayerScore::where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->where('type', 'batting')
            ->pluck('player_id');

        $yetToBat = $allPlayers->diff($battedPlayers);

        return Player::whereIn('id', $yetToBat)->get(['id', 'name', 'player_role']);
    }
}
