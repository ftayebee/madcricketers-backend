<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddOversToTournamentsTable extends Migration
{
    public function up()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->integer('overs_per_innings')->default(0)->after('status');
        });
    }

    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['overs_per_innings']);
        });
    }
}
