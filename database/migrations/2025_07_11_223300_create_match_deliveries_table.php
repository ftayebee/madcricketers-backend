<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('over_number');
            $table->unsignedBigInteger('ball_in_over'); // 1 to 6 (or more if extras)

            $table->unsignedBigInteger('bowler_id'); // Player ID
            $table->unsignedBigInteger('batsman_id'); // Player ID
            $table->unsignedBigInteger('non_striker_id')->nullable(); // Optional

            $table->unsignedBigInteger('batting_team_id');
            $table->unsignedBigInteger('bowling_team_id');

            // Run info
            $table->tinyInteger('runs_batsman')->default(0); // Runs scored off the bat
            $table->tinyInteger('runs_extras')->default(0); // Extras (wides, no-balls, byes, leg-byes)

            // Type of delivery
            $table->enum('delivery_type', ['normal', 'wide', 'no-ball', 'bye', 'leg-bye'])->default('normal');

            // Wicket info
            $table->boolean('is_wicket')->default(false);
            $table->enum('wicket_type', ['bowled', 'caught', 'lbw', 'run out', 'stumped', 'hit wicket', 'retired hurt', 'none'])->default('none');
            $table->unsignedBigInteger('wicket_player_id')->nullable(); // Who got out
            $table->unsignedBigInteger('fielder_id')->nullable(); // Who took the catch or run-out

            $table->timestamps();

            // Foreign keys
            $table->foreign('match_id')->references('id')->on('cricket_matches')->onDelete('cascade');
            $table->foreign('batsman_id')->references('id')->on('players')->onDelete('cascade');
            $table->foreign('bowler_id')->references('id')->on('players')->onDelete('cascade');
            $table->foreign('non_striker_id')->references('id')->on('players')->onDelete('set null');
            $table->foreign('wicket_player_id')->references('id')->on('players')->onDelete('set null');
            $table->foreign('fielder_id')->references('id')->on('players')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('match_deliveries');
    }
}
