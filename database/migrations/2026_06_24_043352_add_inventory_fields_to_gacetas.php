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
        Schema::table('gacetas', function (Blueprint $table) {
            $table->string('ubicacion_topografica')->nullable()->comment('Ej: Archivo Central, Gaveta 4');
            $table->integer('ejemplares_fisicos')->default(1)->comment('Stock disponible para préstamo');
            $table->boolean('es_retroactiva')->default(false)->comment('Identifica si fue subida por lote histórico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gacetas', function (Blueprint $table) {
            $table->dropColumn(['ubicacion_topografica', 'ejemplares_fisicos', 'es_retroactiva']);
        });
    }
};
