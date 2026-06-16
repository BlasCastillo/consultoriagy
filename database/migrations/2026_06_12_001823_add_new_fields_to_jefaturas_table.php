<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jefaturas', function (Blueprint $table) {
            // Agregamos las columnas faltantes solo si la tabla ya existía
            if (!Schema::hasColumn('jefaturas', 'nivel_jerarquico')) {
                $table->string('nivel_jerarquico')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('jefaturas', 'dependencia_id')) {
                $table->unsignedBigInteger('dependencia_id')->nullable()->after('nivel_jerarquico');
                $table->foreign('dependencia_id')->references('id')->on('jefaturas')->onDelete('set null');
            }
            if (!Schema::hasColumn('jefaturas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jefaturas', function (Blueprint $table) {
            $table->dropForeign(['dependencia_id']);
            $table->dropColumn(['nivel_jerarquico', 'dependencia_id']);
            // Nota: No revertimos el softDeletes por seguridad de la data
        });
    }
};
