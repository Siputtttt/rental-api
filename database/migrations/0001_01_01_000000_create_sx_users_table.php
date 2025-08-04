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
        Schema::create('sx_users', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id');
            $table->string('username');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('active', ['0', '1', '2'])->default('0');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('avatar')->nullable();
            $table->string('otp', 4)->nullable();
            $table->string('otp_token', 64)->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });

        Schema::create('sx_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sx_sessions', function (Blueprint $table) {
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
        Schema::dropIfExists('sx_users');
        Schema::dropIfExists('sx_password_reset_tokens');
        Schema::dropIfExists('sx_sessions');
    }
};
