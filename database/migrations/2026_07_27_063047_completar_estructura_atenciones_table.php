<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('atencions', function (Blueprint $table) {
            // Verificamos y creamos el ID de la jefatura si no existe
            if (!Schema::hasColumn('atencions', 'jefatura_destino_id')) {
                $table->unsignedBigInteger('jefatura_destino_id')->nullable();
            }

            // Verificamos y creamos el motivo por si Antigravity también lo omitió
            if (!Schema::hasColumn('atencions', 'motivo')) {
                $table->string('motivo')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('atencions', function (Blueprint $table) {
            if (Schema::hasColumn('atencions', 'jefatura_destino_id')) {
                $table->dropColumn('jefatura_destino_id');
            }
            if (Schema::hasColumn('atencions', 'motivo')) {
                $table->dropColumn('motivo');
            }
        });
    }
};