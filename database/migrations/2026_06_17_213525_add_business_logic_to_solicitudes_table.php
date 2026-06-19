<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->json('checklist')->nullable()->comment('Almacena el estado de la verificación de los requisitos legales');
            $table->string('ano_politico')->nullable()->comment('Ej: 167° o 2026');
            $table->string('mes_politico')->nullable()->comment('Ej: 161° o Junio');
            $table->timestamp('fecha_recepcion_fisica')->nullable()->comment('Marca de tiempo para el reloj de auditoría');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn(['checklist', 'ano_politico', 'mes_politico', 'fecha_recepcion_fisica']);
        });
    }
};
