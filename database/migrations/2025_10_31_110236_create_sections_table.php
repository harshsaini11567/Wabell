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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->string('section_key'); // e.g., 'about_company', 'featured_services'
            $table->integer('position')->default(0); // for ordering

            $table->enum('status', array_keys(config('constant.status')))->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
