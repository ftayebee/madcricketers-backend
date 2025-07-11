<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partnerships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('batter_1_id');
            $table->unsignedBigInteger('batter_2_id')->nullable(); // null if not yet joined
            $table->unsignedInteger('runs');
            $table->unsignedInteger('balls');
            $table->unsignedDecimal('start_over', 4, 1);
            $table->unsignedDecimal('end_over', 4, 1)->nullable(); // in-progress if null
            $table->unsignedBigInteger('wicket_id')->nullable(); // reference to fall_of_wickets
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
        Schema::dropIfExists('partnerships');
    }
}
