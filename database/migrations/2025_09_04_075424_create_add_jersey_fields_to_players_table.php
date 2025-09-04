<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddJerseyFieldsToPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('jursey_number')->nullable()->after('bowling_style');
            $table->string('jursey_name')->nullable()->after('jursey_number');
            $table->enum('jursey_size', ['s','m','l','xl','2xl', '3xl'])->nullable()->after('jursey_name');
            $table->string('chest_measurement')->nullable()->after('jursey_size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['jursey_number', 'jursey_name', 'jursey_size', 'chest_measurement']);
        });
    }
}
