<?php

namespace Database\Seeders;

use App\Models\PaymentCategory;
use Illuminate\Database\Seeder;

class FinanceCategorySeeder extends Seeder
{
    /**
     * Seeds default payment categories.
     * Idempotent — uses firstOrCreate keyed on slug.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'            => 'Monthly Due',
                'slug'            => 'monthly-due',
                'description'     => 'Monthly membership fee',
                'recurrence_type' => 'monthly',
                'default_amount'  => 200.00,
                'is_active'       => true,
                'sort_order'      => 1,
            ],
            [
                'name'            => 'Jersey Fee',
                'slug'            => 'jersey-fee',
                'description'     => 'Fee for club jersey',
                'recurrence_type' => 'one_time',
                'default_amount'  => 1500.00,
                'is_active'       => true,
                'sort_order'      => 2,
            ],
            [
                'name'            => 'Tournament Fee',
                'slug'            => 'tournament-fee',
                'description'     => 'Per-tournament registration fee',
                'recurrence_type' => 'one_time',
                'default_amount'  => 500.00,
                'is_active'       => true,
                'sort_order'      => 3,
            ],
            [
                'name'            => 'Annual Due',
                'slug'            => 'annual-due',
                'description'     => 'Yearly membership fee',
                'recurrence_type' => 'annual',
                'default_amount'  => 2000.00,
                'is_active'       => true,
                'sort_order'      => 4,
            ],
            [
                'name'            => 'Match Fee',
                'slug'            => 'match-fee',
                'description'     => 'Per-match participation fee',
                'recurrence_type' => 'one_time',
                'default_amount'  => 100.00,
                'is_active'       => true,
                'sort_order'      => 5,
            ],
            [
                'name'            => 'Other',
                'slug'            => 'other',
                'description'     => 'Miscellaneous fee',
                'recurrence_type' => 'one_time',
                'default_amount'  => 0.00,
                'is_active'       => true,
                'sort_order'      => 99,
            ],
        ];

        foreach ($categories as $data) {
            PaymentCategory::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('Finance categories seeded.');
    }
}
