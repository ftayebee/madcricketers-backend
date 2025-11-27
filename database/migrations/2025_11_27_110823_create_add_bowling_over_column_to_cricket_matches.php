<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddBowlingOverColumnToCricketMatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cricket_matches', function (Blueprint $table) {
            $table->integer('bowler_max_overs')->default(0)->after('max_overs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cricket_matches', function (Blueprint $table) {
            $table->drop('bowler_max_overs');
        });
    }
}
