<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutTypeToMatchPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Add out_type column to match_players to store dismissal method separately
     * from the status column. Previously, dismissal types (bowled, caught, etc.)
     * were being stored in the status field, polluting the status lifecycle.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->string('out_type')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->dropColumn('out_type');
        });
    }
}
