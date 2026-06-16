<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar la vieja columna institution_id y su FK si existe
            if (Schema::hasColumn('users', 'institution_id')) {
                $table->dropForeign(['institution_id']);
                $table->dropColumn('institution_id');
            }

            // Agregar las nuevas columnas
            $table->unsignedBigInteger('institucion_id')->nullable()->after('status');
            $table->unsignedBigInteger('jefatura_id')->nullable()->after('institucion_id');

            $table->foreign('institucion_id')->references('id')->on('instituciones')->onDelete('set null');
            $table->foreign('jefatura_id')->references('id')->on('jefaturas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institucion_id']);
            $table->dropForeign(['jefatura_id']);
            $table->dropColumn(['institucion_id', 'jefatura_id']);
        });
    }
};
