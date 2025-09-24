<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddInningsToFallOfWicketsTable extends Migration
{
    public function up()
    {
        Schema::table('fall_of_wickets', function (Blueprint $table) {
            $table->integer('innings')->default(1)->after('match_id');
        });
    }

    public function down()
    {
        Schema::table('fall_of_wickets', function (Blueprint $table) {
            $table->dropColumn(['innings']);
        });
    }
}
