<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->string('payment_id')->nullable();
            $table->string('registration_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('步');
            $table->enum('payment_status', array_keys(config('constant.payment_status')))->default('pending');
            $table->string('payment_method')->default('hyperpay');
            $table->json('payment_data')->nullable(); // store full HyperPay response
            $table->json('user_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
