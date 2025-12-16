<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddMatchIdToPlayerTeamTable extends Migration
{
    public function up()
    {
        Schema::table('player_team', function (Blueprint $table) {
            $table->unsignedBigInteger('match_id')->after('id')->nullable();
            $table->foreign('match_id')->references('id')->on('cricket_matches')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('player_team', function (Blueprint $table) {
            $table->dropColumn('match_id');
        });
    }
}
