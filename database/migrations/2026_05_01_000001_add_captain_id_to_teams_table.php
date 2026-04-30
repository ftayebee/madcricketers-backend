<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaptainIdToTeamsTable extends Migration
{
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'captain_id')) {
                $table->unsignedBigInteger('captain_id')->nullable()->after('description');
                $table->foreign('captain_id')->references('id')->on('players')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'captain_id')) {
                $table->dropForeign(['captain_id']);
                $table->dropColumn('captain_id');
            }
        });
    }
}
