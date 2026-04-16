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
        return MatchPlayer::with('player.user')
            ->where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->whereIn('status', ['batting', 'on-strike', 'bowled', 'caught', 'bowling', 'run_out', 'lbw', 'retired-hurt', 'fielding', 'hit-wicket', 'closed', 'stumped', 'ready'])
            ->get()
            ->map(function ($mp) {
                return [
                    'id'          => $mp->player_id,
                    'name'        => optional(optional($mp->player)->user)->full_name ?? '—',
                    'image'       => $mp->player?->image,
                    'runs'        => $mp->runs_scored,
                    'balls'       => $mp->balls_faced,
                    'status'      => $mp->status,
                    'strike_rate' => $mp->balls_faced > 0 ? round(($mp->runs_scored / $mp->balls_faced) * 100, 2) : 0,
                    'fours'       => $mp->fours,
                    'sixes'       => $mp->sixes,
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
                    'id'      => $player->player_id,
                    'overs'   => $player->overs_bowled,
                    'maidens' => $player->maidens ?? 0,
                    'runs'    => $player->runs_conceded,
                    'wickets' => $player->wickets_taken,
                    'economy' => $player->overs_bowled > 0 ? round($player->runs_conceded / $player->overs_bowled, 2) : 0,
                ];
            });
    }

    public function fallOfWickets()
    {
        return FallOfWicket::with([
                'batter.user:id,full_name',
                'bowler.user:id,full_name',
                'fielder.user:id,full_name'
            ])
            ->where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->orderBy('wicket_number')
            ->get()
            ->map(function ($wicket) {
                return [
                    'wicket'          => $wicket->wicket_number,
                    'score'           => $wicket->runs,
                    'over'            => $wicket->overs,
                    'batter'          => optional($wicket->batter?->user)->full_name,
                    'bowler'          => optional($wicket->bowler?->user)->full_name,
                    'fielder'         => optional($wicket->fielder?->user)->full_name,
                    'dismissal_type'  => $wicket->dismissal_type,
                ];
            })
            ->values()
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
        $allPlayers = Player::whereHas('teams', function ($q) {
                $q->where('teams.id', $this->team_id);
            })
            ->pluck('players.id');
        $battedPlayers = MatchPlayer::where('match_id', $this->match_id)
            ->where('team_id', $this->team_id)
            ->whereIn('status', ['batting', 'on-strike', 'bowled', 'caught', 'bowling', 'run_out', 'lbw', 'retired-hurt', 'fielding', 'hit-wicket', 'closed', 'stumped', 'ready'])
            ->pluck('player_id');

        $yetToBat = $allPlayers->diff($battedPlayers);

        return Player::with('user')->whereIn('id', $yetToBat)->get();
    }
}
