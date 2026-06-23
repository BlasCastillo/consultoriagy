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
            $table->dropColumn(['ano_politico', 'mes_politico']);
        });
        
        if (Schema::hasColumn('gacetas', 'anio_politico')) {
            Schema::table('gacetas', function (Blueprint $table) {
                $table->dropColumn('anio_politico');
            });
        }
        if (Schema::hasColumn('gacetas', 'mes_politico')) {
            Schema::table('gacetas', function (Blueprint $table) {
                $table->dropColumn('mes_politico');
            });
        }

        Schema::table('gacetas', function (Blueprint $table) {
            $table->string('ano_politico')->nullable()->after('tipo')->comment('Ej: 168° o 2026');
            $table->string('mes_politico')->nullable()->after('ano_politico')->comment('Ej: 120° o Junio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gacetas', function (Blueprint $table) {
            $table->dropColumn(['ano_politico', 'mes_politico']);
        });
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('ano_politico')->nullable()->after('id')->comment('Ej: 168° o 2026');
            $table->string('mes_politico')->nullable()->after('ano_politico')->comment('Ej: 120° o Junio');
        });
    }
};
