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
        Schema::create('master_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            // $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('experience')->nullable();
            $table->json('education')->nullable();
            $table->string('tagline')->nullable();
            $table->text('biography')->nullable();
            $table->decimal('price_per_hour', 8, 2)->nullable();
            $table->json('available_time')->nullable();
            $table->json('available_day')->nullable();
            // $table->string('registration_day')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_details');
    }
};
