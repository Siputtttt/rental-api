<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sx_modules', function (Blueprint $table) {
            $table->id('module_id');
            $table->string('module_name')->nullable();
            $table->string('module_title')->nullable();
            $table->string('module_note')->nullable();
            $table->string('module_author')->nullable();
            $table->timestamp('module_created')->nullable()->useCurrent();
            $table->text('module_desc');
            $table->string('module_db')->nullable();
            $table->string('module_db_key')->nullable();
            $table->char('module_type', 20);
            $table->longText('module_config')->nullable();
            $table->string('module_template')->nullable();
            $table->text('module_lang')->nullable();
            $table->timestamps(); // created_at & updated_at with default current_timestamp
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sx_modules');
    }
};
