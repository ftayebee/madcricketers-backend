<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStageCoulmnToCricketMatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cricket_matches', function (Blueprint $table) {
            $table->enum('stage', ['group', 'playoffs', 'semi-final', 'final'])->after('tournament_id');
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
            //
        });
    }
}
