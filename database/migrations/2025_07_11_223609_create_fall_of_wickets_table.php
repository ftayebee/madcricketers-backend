<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFallOfWicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fall_of_wickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedTinyInteger('wicket_number'); // 1 to 10
            $table->unsignedInteger('runs'); // Team score at this wicket
            $table->unsignedDecimal('overs', 4, 1); // e.g. 15.2 overs
            $table->unsignedBigInteger('batter_id');
            $table->unsignedBigInteger('bowler_id')->nullable();
            $table->unsignedBigInteger('fielder_id')->nullable();
            $table->string('dismissal_type')->nullable(); // caught, bowled, lbw, etc.
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
        Schema::dropIfExists('fall_of_wickets');
    }
}
