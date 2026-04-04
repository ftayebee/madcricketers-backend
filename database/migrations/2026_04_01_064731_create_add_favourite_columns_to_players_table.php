<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddFavouriteColumnsToPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('favourite_football_country')->nullable();
            $table->string('favourite_cricket_country')->nullable();
            $table->string('favourite_football_league_team')->nullable();
            $table->string('married_status')->nullable();
            $table->string('education_batch')->nullable();
            $table->string('ssc_batch')->nullable();
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
            $table->dropColumn([
                'favourite_football_country',
                'favourite_cricket_country',
                'favourite_football_league_team',
                'married_status',
                'education_batch',
                'ssc_batch'
            ]);
        });
    }
}
