<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Player;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class PlayerSeeder extends Seeder
{
    public function run()
    {
        $playerRole = Role::where('name', 'player')->first();
        $faker = Faker::create();

        if (!$playerRole) {
            $playerRole = Role::create(['name' => 'player']);
        }

        for ($i = 1; $i <= 10; $i++) {

            $fullName = $faker->name();
            $email = $faker->unique()->safeEmail();
            $username = explode('@', $email)[0];

            // Create user
            $user = User::create([
                'full_name' => $fullName,
                'nickname' => $faker->firstName(),
                'username' => $username,
                'phone' => $faker->numerify('017########'),
                'blood_group' => $faker->randomElement(['A+', 'B+', 'O+', 'AB+']),
                'email' => $email,
                'password' => bcrypt('password'),
                'status' => 'active',
                'national_id' => $faker->numerify('1234567890##'),
                'role_id' => $playerRole->id,
            ]);

            // Assign role (Spatie)
            $user->assignRole($playerRole->name);

            // Create player
            Player::create([
                'user_id'                        => $user->id,
                'player_type'                    => 'registered',
                'player_role'                    => $faker->randomElement(['batsman', 'bowler', 'all-rounder', 'wicketkeeper']),
                'batting_style'                  => $faker->randomElement(['right-handed', 'left-handed']),
                'bowling_style'                  => $faker->randomElement(['fast', 'medium', 'spin', 'none']),
                'jursey_number'                  => $faker->numberBetween(1, 99),
                'jursey_name'                    => strtoupper($faker->word()),
                'jursey_size'                    => $faker->randomElement(['s', 'm', 'l', 'xl', '2xl', '3xl']),
                'chest_measurement'              => $faker->numberBetween(36, 44),
                'favourite_football_country'     => $faker->country(),
                'favourite_cricket_country'      => $faker->country(),
                'favourite_football_league_team' => $faker->company(),
                'married_status'                 => $faker->randomElement(['Single', 'Married']),
                'education_batch'                => 'Batch ' . $faker->year(),
                'ssc_batch'                      => 'SSC ' . $faker->year(),
            ]);
        }
    }
}
