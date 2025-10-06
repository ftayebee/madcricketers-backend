<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddTournamentIdToPlayerTeamTable extends Migration
{
    public function up()
    {
        Schema::table('player_team', function (Blueprint $table) {
            $table->unsignedBigInteger('tournament_id')->nullable()->after('team_id');
            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('player_team', function (Blueprint $table) {
            $table->dropColumn(['tournament_id']);
        });
    }
}
