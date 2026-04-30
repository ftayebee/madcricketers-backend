<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('payment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                         // e.g. "Monthly Due", "Jersey Fee"
            $table->string('slug')->unique();                               // e.g. "monthly-due", "jersey-fee"
            $table->text('description')->nullable();
            $table->enum('recurrence_type', ['monthly', 'annual', 'one_time'])->default('one_time');
            $table->decimal('default_amount', 10, 2)->default(0.00);       // suggested amount when creating dues
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_categories');
    }
}
