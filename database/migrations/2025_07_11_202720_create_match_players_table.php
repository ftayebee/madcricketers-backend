<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('player_id');
            $table->unsignedInteger('runs_scored')->nullable();
            $table->unsignedInteger('balls_faced')->nullable();
            $table->unsignedInteger('wickets_taken')->nullable();
            $table->unsignedInteger('overs_bowled')->nullable();
            $table->unsignedInteger('runs_conceded')->nullable();
            $table->enum('status', ['batting', 'on-strike', 'bowled', 'caught', 'bowling'])->default('batting')->nullable();

            $table->foreign('match_id')->references('id')->on('cricket_matches')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('match_players');
    }
}
