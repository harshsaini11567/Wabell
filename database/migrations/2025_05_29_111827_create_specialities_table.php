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
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('specialty_request_id')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();

            $table->unsignedBigInteger('parent_specialty_id')->nullable();

            $table->enum('specialty_status', array_keys(config('constant.status')))->default('active');
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
        Schema::dropIfExists('specialties');
    }
};
