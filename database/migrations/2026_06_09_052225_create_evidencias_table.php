<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_trimestral_id')->constrained('meta_trimestrals')->onDelete('cascade');
            $table->string('archivo_path');
            $table->text('descripcion')->nullable();
            $table->string('tipo')->default('Informe');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
