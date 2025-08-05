<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('m_tipe_kendaraan', function (Blueprint $table) {
            $table->id(); // Kolom id (bigint auto increment)
            $table->string('tipe'); // Kolom tipe kendaraan
            $table->timestamps(); // Kolom created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_tipe_kendaraan');
    }
};

