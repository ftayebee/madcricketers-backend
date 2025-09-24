<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddInningsToPartnershipsTable extends Migration
{
    public function up()
    {
        Schema::table('partnerships', function (Blueprint $table) {
            $table->integer('innings')->default(1)->after('match_id');
        });
    }

    public function down()
    {
        Schema::table('partnerships', function (Blueprint $table) {
            $table->dropColumn(['innings']);
        });
    }
}
