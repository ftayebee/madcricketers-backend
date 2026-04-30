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
            'Mumbai Indians',
            'Chennai Super Kings', 
            'Royal Challengers Bangalore',
            'Kolkata Knight Riders',
            'Delhi Capitals',
            'Rajasthan Royals',
            'Sunrisers Hyderabad',
            'Punjab Kings',
        ];

        // Get all 90 player IDs
        $playerIds = Player::pluck('id')->toArray();

        shuffle($playerIds);

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

            $assignedPlayers = array_slice($playerIds, $i * 5, 5);
            $team->players()->attach($assignedPlayers);
        }
    }
}
