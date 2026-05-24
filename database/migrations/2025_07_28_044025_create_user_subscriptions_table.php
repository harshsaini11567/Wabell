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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', array_keys(config('constant.subscription_status')))->default('active');
            $table->enum('billing_cycle',array_keys(config('constant.plan_billing_cycle')))->nullable();
            $table->boolean('auto_renew')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->json('plan_data')->nullable();
            $table->json('user_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
