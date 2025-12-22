<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo', 'coach_name', 'manager_name', 'description'];

    public function players()
    {
        return $this->belongsToMany(Player::class, 'player_team')
            ->withPivot(['match_id', 'tournament_id'])
            ->withTimestamps();
    }

    public function playersForTournamentMatch($matchId, $tournamentId)
    {
        return $this->belongsToMany(Player::class, 'player_team')
            ->wherePivot('match_id', $matchId)
            ->wherePivot('tournament_id', $tournamentId)
            ->withPivot(['match_id', 'tournament_id'])
            ->withTimestamps();
    }

    public function playersForFriendlyMatch($matchId)
    {
        return $this->belongsToMany(Player::class, 'player_team')
            ->wherePivot('match_id', $matchId)
            ->whereNull('player_team.tournament_id')
            ->withPivot(['match_id', 'tournament_id'])
            ->withTimestamps();
    }

    public function getLogoAttribute()
    {
        $originalImage = $this->getAttributes()['logo'] ?? null;

        $imagePath = 'public/uploads/teams/' . $originalImage;
        $defaultLogo = asset('storage/assets/images/team-dummy.png');

        if ($originalImage && Storage::exists($imagePath)) {
            return asset('storage/uploads/teams/' . $originalImage);
        }

        return $defaultLogo;
    }

    public function groups()
    {
        return $this->belongsToMany(
            TournamentGroup::class,
            'tournament_group_teams', // pivot table
            'team_id',                // foreign key on pivot table pointing to this model
            'group_id'                // foreign key on pivot table pointing to related model
        )->withTimestamps();
    }

    public function getTournamentStats($tournamentId)
    {
        return $this->hasMany(TournamentTeamStat::class, 'team_id')
            ->where('tournament_id', $tournamentId);
    }

    public function matchesAsTeamA()
    {
        return $this->hasMany(CricketMatch::class, 'team_a_id');
    }

    public function matchesAsTeamB()
    {
        return $this->hasMany(CricketMatch::class, 'team_b_id');
    }

    public function getCricketMatchesAttribute()
    {
        return $this->matchesAsTeamA
            ->merge($this->matchesAsTeamB);
    }
}
