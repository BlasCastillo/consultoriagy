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
        Schema::table('actividad_poas', function (Blueprint $table) {
            $table->decimal('monto_asignado', 15, 2)->default(0.00)->after('partida_presupuestaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividad_poas', function (Blueprint $table) {
            $table->dropColumn('monto_asignado');
        });
    }
};
