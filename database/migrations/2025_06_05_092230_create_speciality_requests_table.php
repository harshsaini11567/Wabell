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
        Schema::create('specialty_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->text('message_en')->nullable();
            $table->text('message_ar')->nullable();
            $table->json('user_info')->nullable();
            $table->string('user_role')->nullable();
            $table->enum('status', array_keys(config('constant.specialties_request_status')))->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialty_requests');
    }
};
