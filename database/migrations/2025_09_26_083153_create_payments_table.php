<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['donation', 'tournament', 'jersey', 'other']);
            $table->decimal('amount', 10, 2);
            $table->date('payment_date')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
