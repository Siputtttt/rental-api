<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sx_content', function (Blueprint $table) {
            $table->id();
            $table->integer('categories_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('sinopsis', 50)->nullable();
            $table->longText('note')->nullable();
            $table->text('access_data')->nullable();
            $table->enum('allow_guest', ['0', '1'])->default('0');
            $table->string('labels', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sx_content');
    }
};
