<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchScoreBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_score_boards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedTinyInteger('innings')->default(1); // 1 or 2 for 2nd innings (if applicable)
            $table->unsignedInteger('runs')->default(0);
            $table->unsignedInteger('wickets')->default(0);
            $table->float('overs')->default(0);

            $table->timestamps();

            $table->foreign('match_id')->references('id')->on('cricket_matches')->onDelete('cascade');
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
        Schema::dropIfExists('match_score_boards');
    }
}
