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
            if (!Schema::hasColumn('gacetas', 'tipo')) {
                $table->enum('tipo', ['Ordinaria', 'Extraordinaria'])->default('Ordinaria');
            }
            if (!Schema::hasColumn('gacetas', 'fecha_publicacion')) {
                $table->date('fecha_publicacion')->nullable();
            }
            if (!Schema::hasColumn('gacetas', 'digitalizador_id')) {
                $table->foreignId('digitalizador_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('gacetas', 'archivo_pdf_completo')) {
                $table->string('archivo_pdf_completo')->nullable()->comment('PDF final unificado');
            }
            if (Schema::hasColumn('gacetas', 'estado')) {
                // Drop and recreate or change if needed. 
                // In Postgres we might need raw queries or dbal. 
                // Let's just leave it as is if it exists, since previous migration set it to string.
            } else {
                $table->enum('estado', ['En Proceso', 'Publicada', 'Archivada'])->default('En Proceso');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gacetas', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'fecha_publicacion', 'digitalizador_id', 'archivo_pdf_completo']);
        });
    }
};
