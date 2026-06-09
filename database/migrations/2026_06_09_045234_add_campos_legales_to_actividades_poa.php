<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('actividad_poas', function (Blueprint $table) {
            $table->string('unidad_medida')->nullable();
            $table->string('medios_verificacion')->nullable();
            $table->string('frecuencia_lectura')->default('Trimestral');
            $table->string('formulacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('poas', function (Blueprint $table) {
            $table->string('tipo')->default('Jefatura');
        });

        Schema::table('actividad_poas', function (Blueprint $table) {
            $table->dropColumn([
                'unidad_medida',
                'medios_verificacion',
                'frecuencia_lectura',
                'formulacion'
            ]);
        });
    }
};
