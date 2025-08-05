<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_model_kendaraan', function (Blueprint $table) {
            $table->id(); // Kolom id primary key
            $table->string('nama_model'); // Nama model kendaraan
            $table->string('gambar', 100)->nullable(); // Gambar (varchar 100)
            $table->integer('kapasitas')->nullable(); // Kapasitas penumpang
            $table->text('detail_gambar')->nullable(); // Deskripsi gambar
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_model_kendaraan');
    }
};
