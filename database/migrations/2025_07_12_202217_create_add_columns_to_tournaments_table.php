<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddColumnsToTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->enum('format', ['group', 'round-robin', 'knockout'])->default('round-robin')->after('status');
            $table->boolean('has_knockout')->default(true)->after('format');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            //
        });
    }
}
