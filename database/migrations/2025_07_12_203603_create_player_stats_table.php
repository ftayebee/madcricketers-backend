<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('player_id')->unique(); // One record per player

            // Batting
            $table->unsignedInteger('matches_played')->default(0);
            $table->unsignedInteger('innings_batted')->default(0);
            $table->unsignedInteger('total_runs')->default(0);
            $table->unsignedInteger('balls_faced')->default(0);
            $table->unsignedInteger('fifties')->default(0);
            $table->unsignedInteger('hundreds')->default(0);
            $table->unsignedInteger('sixes')->default(0);
            $table->unsignedInteger('fours')->default(0);
            $table->float('strike_rate')->default(0); // Calculated as (runs / balls) * 100
            $table->float('average')->default(0); // runs / dismissals

            // Bowling
            $table->unsignedInteger('innings_bowled')->default(0);
            $table->unsignedInteger('overs_bowled')->default(0);
            $table->unsignedInteger('runs_conceded')->default(0);
            $table->unsignedInteger('wickets')->default(0);
            $table->float('bowling_average')->default(0); // runs_conceded / wickets
            $table->float('economy_rate')->default(0); // runs_conceded / overs

            // Fielding
            $table->unsignedInteger('catches')->default(0);
            $table->unsignedInteger('runouts')->default(0);
            $table->unsignedInteger('stumpings')->default(0);

            $table->timestamps();

            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_stats');
    }
}
