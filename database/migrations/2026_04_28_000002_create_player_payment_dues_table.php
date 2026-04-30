<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerPaymentDuesTable extends Migration
{
    public function up()
    {
        Schema::create('player_payment_dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('payment_categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);                               // exact amount due
            $table->date('due_date')->nullable();                           // when it should be paid by
            $table->string('period_label')->nullable();                     // e.g. "January 2026", "Tournament 2025"
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'waived'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->default(0.00);           // running total of payments against this due
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint: one due per player per category per period
            $table->unique(['player_id', 'category_id', 'period_label'], 'unique_player_due_period');
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_payment_dues');
    }
}
