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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            // $table->string('city_id')->nullable();
            // $table->string('neighborhood_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('neighborhood_id')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('phone_varified')->nullable();
            $table->boolean('is_ban')->default(false);
            $table->boolean('is_approved')->nullable();
            $table->tinyInteger('approval_status')
                ->default(0)
                ->comment('0 = pending, 1 = approved, 2 = rejected');
            $table->tinyInteger('is_available')->default(1)->comment('0=> unavailable, 1=> available');
            $table->date('till_offline')->nullable();  // if is_available is 0 then will added toll_offline value.
            $table->enum('login_type',['google','facebook', 'apple', 'normal'])->default('normal');
            $table->text('social_user_id')->nullable();
            $table->text('device_token')->nullable();
            $table->enum('user_status', array_keys(config('constant.user_status')))->default('active');
            $table->text('user_interest')->nullable();
            $table->text('about_user')->nullable();
            $table->rememberToken();
         //   $table->string('refresh_token')->nullable();
            $table->integer('token_version')->default(1);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->dateTime('last_access_date_time')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
