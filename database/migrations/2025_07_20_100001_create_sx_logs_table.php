<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sx_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('data');
            $table->string('module');
            $table->string('action');
            $table->timestamp('inserted')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sx_logs');
    }
};

