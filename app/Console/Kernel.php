<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // Run this monthly if the project scheduler is active:
        // $schedule->command('finance:generate-monthly-dues')->monthlyOn(1, '00:05');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        $generateMonthlyDues = function () {
            $month = $this->option('month');

            if ($month && !preg_match('/^\d{4}-\d{2}$/', $month)) {
                $this->error('The --month option must use YYYY-MM format.');

                return 1;
            }

            $result = app(\App\Services\FinanceService::class)->ensureMonthlyDonationDuesForCurrentMonth($month);

            $this->info("Monthly donation dues checked for {$result['period_label']}.");
            $this->line("Created: {$result['created']}");
            $this->line("Skipped existing: {$result['skipped']}");
            $this->line('Default amount: ' . number_format($result['default_amount'], 2));

            return 0;
        };

        $this->command('finance:generate-monthly-dues {--month= : Month in YYYY-MM format}', $generateMonthlyDues)
            ->purpose('Generate recurring monthly donation dues for active players without duplicates.');

        $this->command('generate-monthly-dues {--month= : Month in YYYY-MM format}', $generateMonthlyDues)
            ->purpose('Generate recurring monthly donation dues for active players without duplicates.');

        require base_path('routes/console.php');
    }
}
