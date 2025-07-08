<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->enum('player_type', ['registered', 'guest'])->default('registered');
            $table->enum('player_role', ['batsman', 'bowler', 'all-rounder', 'wicketkeeper'])->nullable();
            $table->enum('batting_style', ['right-handed', 'left-handed', 'switch hitter'])->nullable();
            $table->enum('bowling_style', ['fast', 'medium', 'spin', 'none'])->nullable();

            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
}
