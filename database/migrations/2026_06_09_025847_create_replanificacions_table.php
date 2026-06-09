<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replanificacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_poa_id')->constrained('actividad_poas')->onDelete('cascade');
            $table->integer('trimestre_origen');
            $table->integer('trimestre_destino');
            $table->integer('cantidad_movida');
            $table->text('justificacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replanificacions');
    }
};
