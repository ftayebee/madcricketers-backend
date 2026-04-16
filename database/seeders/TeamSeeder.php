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
            'Rajasthan Royals',
            'Punjab Kings',
            'Royal Challengers Bangalore',
            'Delhi Capitals',
            'Gujrat Titans',
            'Lucknow Super Giants',
            'Kolkata Knight Riders',
            'Sunrisers Hyderabad',
            'Mumbai Indians',
            'Chennai Super Kings',
        ];

        // Get all player IDs
        $playerIds = Player::pluck('id')->toArray();

        shuffle($playerIds);

        foreach ($teamNames as $index => $teamName) {
            $slug = Str::slug($teamName);

            $team = Team::create([
                'name' => $teamName,
                'slug' => $slug,
                'coach_name' => "Coach $index",
                'manager_name' => "Manager $index",
                'description' => "This is team $teamName",
            ]);

            // Take 5 unique players per team
            $assignedPlayers = array_slice($playerIds, $index * 5, 5);

            $team->players()->attach($assignedPlayers);
        }
    }
}