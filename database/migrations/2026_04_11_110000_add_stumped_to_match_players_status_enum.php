<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStumpedToMatchPlayersStatusEnum extends Migration
{
    /**
     * The match_players.status enum was missing 'stumped' — so any stumping
     * dismissal would fail with a truncated-value SQL error. This migration
     * adds 'stumped' while preserving all existing values.
     *
     * Raw SQL on purpose (no doctrine/dbal in composer).
     */
    public function up()
    {
        if (!Schema::hasTable('match_players')) {
            return;
        }

        DB::statement("ALTER TABLE `match_players`
            MODIFY `status` ENUM(
                'batting',
                'on-strike',
                'bowled',
                'caught',
                'bowling',
                'run_out',
                'stumped',
                'lbw',
                'retired-hurt',
                'fielding',
                'hit-wicket',
                'closed'
            ) DEFAULT 'batting'");
    }

    public function down()
    {
        if (!Schema::hasTable('match_players')) {
            return;
        }

        // Before removing 'stumped', rehome any existing rows so we don't hit
        // a truncation error on rollback.
        DB::table('match_players')->where('status', 'stumped')->update(['status' => 'caught']);

        DB::statement("ALTER TABLE `match_players`
            MODIFY `status` ENUM(
                'batting',
                'on-strike',
                'bowled',
                'caught',
                'bowling',
                'run_out',
                'lbw',
                'retired-hurt',
                'fielding',
                'hit-wicket',
                'closed'
            ) DEFAULT 'batting'");
    }
}
