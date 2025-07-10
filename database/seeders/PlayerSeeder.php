<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Player;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PlayerSeeder extends Seeder
{
    public function run()
    {
        // Get the 'player' role
        $playerRole = Role::where('name', 'player')->first();

        if (!$playerRole) {
            $playerRole = Role::create(['name' => 'player']);
        }

        // Create multiple players
        for ($i = 1; $i <= 90; $i++) {
            $fullName = "Player $i";
            $email = "player{$i}@example.com";
            $username = explode('@', $email)[0];

            // Create the user
            $user = User::create([
                'full_name' => $fullName,
                'nickname' => "P$i",
                'username' => $username,
                'phone' => '0170000000' . $i,
                'blood_group' => 'A+',
                'email' => $email,
                'password' => bcrypt('password'),
                'visible_pass' => 'password',
                'status' => 'active',
                'national_id' => '1234567890' . $i,
                'role_id' => $playerRole->id,
            ]);

            // Assign role (Spatie)
            $user->assignRole($playerRole->name);

            // Create player details
            Player::create([
                'user_id' => $user->id,
                'player_type' => 'registered',
                'player_role' => collect(['batsman', 'bowler', 'all-rounder', 'wicketkeeper'])->random(),
                'batting_style' => collect(['right-handed', 'left-handed'])->random(),
                'bowling_style' => collect(['fast', 'medium', 'spin', 'none'])->random(),
            ]);
        }
    }
}
