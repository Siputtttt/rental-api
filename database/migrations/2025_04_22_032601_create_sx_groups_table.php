<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSxGroupsTable  extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sx_groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->string('name', 20)->nullable();
            $table->string('description', 100)->nullable();
            $table->unsignedBigInteger('level')->nullable();
            $table->unsignedTinyInteger('backend')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sx_groups');
    }
}
