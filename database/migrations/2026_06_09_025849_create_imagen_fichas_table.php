<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagen_fichas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ficha_actividad_id')->constrained('ficha_actividads')->onDelete('cascade');
            $table->string('ruta_imagen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagen_fichas');
    }
};
