<?php

namespace App\Services;

use App\Models\MonthlyDonation;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceService
{
    public function ensureMonthlyDonationDuesForCurrentMonth(?string $month = null): array
    {
        $date = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        $defaultAmount = $this->monthlyDonationDefaultAmount();
        $created = 0;
        $skipped = 0;

        Player::query()
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->select('id')
            ->chunkById(100, function ($players) use ($date, $defaultAmount, &$created, &$skipped) {
                foreach ($players as $player) {
                    $due = MonthlyDonation::firstOrCreate(
                        [
                            'player_id' => $player->id,
                            'year' => $date->year,
                            'month' => $date->month,
                        ],
                        [
                            'expected_amount' => $defaultAmount,
                            'paid_amount' => 0,
                            'is_paid' => false,
                        ]
                    );

                    $due->wasRecentlyCreated ? $created++ : $skipped++;
                }
            });

        return [
            'created' => $created,
            'skipped' => $skipped,
            'month' => $date->format('Y-m'),
            'period_label' => $date->format('F Y'),
            'default_amount' => $defaultAmount,
        ];
    }

    public function currentMonthDonationSummary(?string $month = null): array
    {
        $date = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        $dues = MonthlyDonation::with('player.user')
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->whereHas('player.user', fn ($query) => $query->where('status', 'active'))
            ->get();

        $totalExpected = $dues->sum('expected_amount');
        $totalCollected = $dues->sum('paid_amount');
        $paidPlayers = $dues->where('is_paid', true)->count();
        $totalActivePlayers = Player::whereHas('user', fn ($query) => $query->where('status', 'active'))->count();

        return [
            'period_label' => $date->format('F Y'),
            'total_active_players' => $totalActivePlayers,
            'paid_players' => $paidPlayers,
            'unpaid_players' => max(0, $totalActivePlayers - $paidPlayers),
            'collection_percentage' => $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0,
            'total_expected' => $totalExpected,
            'total_collected' => $totalCollected,
            'remaining_amount' => max(0, $totalExpected - $totalCollected),
        ];
    }

    public function monthlyDonationDefaultAmount(): float
    {
        if (Schema::hasTable('payment_categories') && Schema::hasColumn('payment_categories', 'default_amount')) {
            $query = DB::table('payment_categories');

            if (Schema::hasColumn('payment_categories', 'slug')) {
                $query->whereIn('slug', ['monthly-due', 'monthly-donation']);
            } elseif (Schema::hasColumn('payment_categories', 'name')) {
                $query->where(function ($nameQuery) {
                    $nameQuery->where('name', 'like', '%Monthly Due%')
                        ->orWhere('name', 'like', '%Monthly Donation%');
                });
            }

            if (Schema::hasColumn('payment_categories', 'is_active')) {
                $query->where('is_active', true);
            } elseif (Schema::hasColumn('payment_categories', 'status')) {
                $query->where('status', 'active');
            }

            $categoryAmount = $query->value('default_amount');

            if ($categoryAmount !== null) {
                return (float) $categoryAmount;
            }
        }

        $latestAmount = MonthlyDonation::where('expected_amount', '>', 0)->latest()->value('expected_amount');

        return (float) ($latestAmount ?: config('finance.monthly_donation_default_amount', 0));
    }
}
