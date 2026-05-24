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
        Schema::create('master_favorites', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('master_id');

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('master_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['customer_id', 'master_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_favorites');
    }
};
