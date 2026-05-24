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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            
            $table->text('question_en')->nullable();
            $table->text('question_ar')->nullable();
            $table->longText('answer_en')->nullable();
            $table->longText('answer_ar')->nullable();

            $table->enum('faq_status', array_keys(config('constant.status')))->default('active');
            $table->enum('faq_type', array_keys(config('constant.faq_type')))->default('customer');
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
        Schema::dropIfExists('faqs');
    }
};
