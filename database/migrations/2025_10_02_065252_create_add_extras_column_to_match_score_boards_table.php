<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddExtrasColumnToMatchScoreBoardsTable extends Migration
{
    public function up()
    {
        Schema::table('match_score_boards', function (Blueprint $table) {
            $table->integer('extras')->default(0)->after('overs');
        });
    }

    public function down()
    {
        Schema::table('match_score_boards', function (Blueprint $table) {
            $table->dropColumn(['extras']);
        });
    }
}
