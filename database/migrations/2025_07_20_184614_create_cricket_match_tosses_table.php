<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCricketMatchTossesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cricket_match_tosses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cricket_match_id')->index();
            $table->unsignedBigInteger('toss_winner_team_id')->index();
            $table->enum('decision', ['bat', 'bowl'])->comment('Decision made by toss winner');
            $table->timestamps();

            $table->foreign('cricket_match_id')->references('id')->on('cricket_matches')->onDelete('cascade');
            $table->foreign('toss_winner_team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cricket_match_tosses');
    }
}
