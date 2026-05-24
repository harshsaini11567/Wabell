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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('reviewer_id');
            $table->string('reviewer_type'); // 'customer' or 'master'

            $table->unsignedBigInteger('reviewed_id');

            $table->tinyInteger('rating'); // 1 to 5
            $table->text('review')->nullable();

            $table->tinyInteger('is_edited')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
