<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [ 'name', 'slug', 'logo', 'coach_name', 'manager_name', 'description' ];

    public function players()
    {
        return $this->belongsToMany(Player::class, 'player_team')->withTimestamps();
    }

    public function getLogoAttribute()
    {
        $originalImage = $this->getAttributes()['logo'] ?? null;

        $imagePath = 'public/uploads/teams/' . $originalImage;
        $defaultLogo = asset('storage/assets/images/users/dummy-avatar.jpg');

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
}
