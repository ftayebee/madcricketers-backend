<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('player_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('due_id')->nullable()->constrained('player_payment_dues')->onDelete('set null');
            $table->foreignId('category_id')->constrained('payment_categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bkash', 'nagad', 'bank_transfer', 'other'])->default('cash');
            $table->string('reference')->nullable();                        // receipt / transaction ID
            $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_payments');
    }
}
