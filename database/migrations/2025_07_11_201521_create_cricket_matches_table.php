<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCricketMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cricket_matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->unsignedBigInteger('team_a_id');
            $table->unsignedBigInteger('team_b_id');
            $table->unsignedBigInteger('tournament_id')->nullable(); // Nullable for regular matches
            $table->dateTime('match_date');
            $table->string('venue')->nullable();
            $table->enum('match_type', ['tournament', 'regular'])->default('regular');
            $table->enum('status', ['upcoming', 'live', 'completed'])->default('upcoming');
            $table->unsignedBigInteger('winning_team_id')->nullable();
            $table->string('result_summary')->nullable(); // e.g., "Team A won by 5 wickets"
            $table->timestamps();

            $table->foreign('team_a_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('team_b_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cricket_matches');
    }
}
