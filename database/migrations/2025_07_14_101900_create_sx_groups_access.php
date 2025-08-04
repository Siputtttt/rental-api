<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sx_groups_access', function (Blueprint $table) {
            $table->id(); // ini sama dengan auto increment integer primary key
            $table->integer('group_id')->nullable();
            $table->integer('module_id')->nullable();
            $table->text('access_data')->nullable();
            $table->timestamps(); // jika kamu ingin created_at dan updated_at, bisa dihapus kalau tidak perlu
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sx_groups_access');
    }
};

