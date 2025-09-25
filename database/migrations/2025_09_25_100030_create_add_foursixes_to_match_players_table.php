<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddFoursixesToMatchPlayersTable extends Migration
{
    public function up()
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->integer('fours')->default(0)->after('runs_scored');
            $table->integer('sixes')->default(0)->after('fours');
        });
    }

    public function down()
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->dropColumn(['fours', 'sixes']);
        });
    }
}
