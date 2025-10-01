<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddLastMatchIdToPlayerStatistics extends Migration
{
    public function up()
    {
        Schema::table('player_statistics', function (Blueprint $table) {
            $table->unsignedBigInteger('last_match_id')->after('player_id')->nullable();
            $table->unsignedBigInteger('last_batting_match_id')->after('player_id')->nullable();
            $table->unsignedBigInteger('last_bowling_match_id')->after('player_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('player_statistics', function (Blueprint $table) {
            $table->dropColumn(['last_match_id', 'last_bowling_match_id', 'last_batting_match_id']);
        });
    }
}
