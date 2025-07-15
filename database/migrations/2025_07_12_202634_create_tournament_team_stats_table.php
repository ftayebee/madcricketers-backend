<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentTeamStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_team_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id');
            $table->unsignedBigInteger('team_id');
            $table->integer('matches_played')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('draws')->default(0);
            $table->integer('points')->default(0);
            $table->float('nrr')->default(0); // net run rate
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_team_stats');
    }
}
