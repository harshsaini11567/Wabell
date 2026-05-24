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
        Schema::create('customer_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('master_id');
            $table->enum('request_status', array_keys(config('constant.customer_request_status')))->default('pending');
            $table->string('request_type')->nullable();   // call & chat
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('master_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['customer_id', 'master_id', 'request_type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_requests');
    }
};
