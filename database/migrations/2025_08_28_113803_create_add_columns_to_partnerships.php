<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddColumnsToPartnerships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partnerships', function (Blueprint $table) {
            $table->decimal('player1_runs')->default(0);
            $table->decimal('player2_runs')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partnerships', function (Blueprint $table) {
            
        });
    }
}
