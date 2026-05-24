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
        Schema::create('section_metas', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('display_name_en')->nullable();
            $table->string('display_name_ar')->nullable();
            $table->string('meta_key');        
            $table->text('meta_value')->nullable();
            $table->text('field_type')->nullable();

            $table->enum('status', array_keys(config('constant.status')))->default('active');

            $table->timestamps();
            
            $table->index(['section_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_metas');
    }
};
