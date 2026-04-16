<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSuperStagesToCricketMatchesAndTournaments extends Migration
{
    /**
     * Extend the cricket_matches.stage enum to include 'super8' and 'super4'.
     * Add next_stage_selection (nullable) to tournaments for persisting admin's
     * next-stage choice after group stage completion.
     */
    public function up()
    {
        // MySQL does not allow Schema::table() enum modification via the fluent
        // builder without a full column rewrite — use a raw statement instead.
        DB::statement("
            ALTER TABLE cricket_matches
            MODIFY COLUMN stage
            ENUM('group','playoffs','semi-final','final','super8','super4')
            NOT NULL
        ");

        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('next_stage_selection')->nullable()->after('format')
                  ->comment('Persisted admin selection of next stage after group stage (super8 | super4)');
        });
    }

    public function down()
    {
        // Revert enum to original four values (will fail if any super8/super4 rows exist)
        DB::statement("
            ALTER TABLE cricket_matches
            MODIFY COLUMN stage
            ENUM('group','playoffs','semi-final','final')
            NOT NULL
        ");

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('next_stage_selection');
        });
    }
}
