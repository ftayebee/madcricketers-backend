<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeOversBowledToDecimal extends Migration
{
    /**
     * The `overs_bowled` column was originally `unsignedInteger`, but the
     * scoreboard logic stores it as a cricket over notation (e.g. 0.1, 0.2,
     * ... 0.5, 1.0, 1.1) where the decimal part represents balls bowled in
     * the current over. MySQL was silently truncating the decimal portion
     * to an integer, so per-ball progress was lost and overs were stuck at 0.
     *
     * Convert the column to DECIMAL(5,1) on every table that stores it.
     *
     * Raw SQL is used on purpose — the project does not ship with
     * doctrine/dbal, which Laravel 8's Schema::change() requires.
     */
    public function up()
    {
        if (Schema::hasTable('match_players')) {
            DB::statement('ALTER TABLE `match_players` MODIFY `overs_bowled` DECIMAL(5,1) NULL DEFAULT 0');
        }

        if (Schema::hasTable('player_stats')) {
            DB::statement('ALTER TABLE `player_stats` MODIFY `overs_bowled` DECIMAL(6,1) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('tournament_player_stats')) {
            DB::statement('ALTER TABLE `tournament_player_stats` MODIFY `overs_bowled` DECIMAL(6,1) NOT NULL DEFAULT 0');
        }
    }

    public function down()
    {
        if (Schema::hasTable('match_players')) {
            DB::statement('ALTER TABLE `match_players` MODIFY `overs_bowled` INT(10) UNSIGNED NULL DEFAULT NULL');
        }

        if (Schema::hasTable('player_stats')) {
            DB::statement('ALTER TABLE `player_stats` MODIFY `overs_bowled` INT(10) UNSIGNED NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('tournament_player_stats')) {
            DB::statement('ALTER TABLE `tournament_player_stats` MODIFY `overs_bowled` INT(10) UNSIGNED NOT NULL DEFAULT 0');
        }
    }
}
