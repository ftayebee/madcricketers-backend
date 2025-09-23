<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddStatusColumnToMatchScoreBoards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('match_score_boards', function (Blueprint $table) {
            $table->enum('status', ['ended', 'running', 'waiting'])->default('waiting')->after('overs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('match_score_boards', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
}
