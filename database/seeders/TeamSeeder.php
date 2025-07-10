<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Player;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run()
    {
        $teamNames = [
            'Thunder Warriors',
            'Desert Hawks',
            'Ocean Blazers',
            'Sky Smashers',
            'Mountain Kings',
            'Valley Vipers',
            'Forest Rangers',
            'Storm Strikers'
        ];

        // Get all 90 player IDs
        $playerIds = Player::pluck('id')->toArray();

        // Shuffle them to ensure random assignment
        shuffle($playerIds);

        // Assign 11 players to each of 8 teams
        for ($i = 0; $i < 8; $i++) {
            $teamName = $teamNames[$i];
            $slug = Str::slug($teamName);

            $team = Team::create([
                'name' => $teamName,
                'slug' => $slug,
                'coach_name' => "Coach $i",
                'manager_name' => "Manager $i",
                'description' => "This is team $teamName",
            ]);

            $assignedPlayers = array_slice($playerIds, $i * 11, 11);
            $team->players()->attach($assignedPlayers);
        }
    }
}
