<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('m_merek_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_merek'); // Nama merek kendaraan
            $table->string('gambar')->nullable(); // Nama file gambar (bisa null)
            $table->timestamp('created')->nullable(); // Kolom created custom
            $table->timestamp('updated')->nullable(); // Kolom updated custom
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_merek_kendaraan');
    }
};

