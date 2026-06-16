<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes', 'orden_sumario')) {
                $table->integer('orden_sumario')->nullable()->after('gaceta_id');
            }
            if (!Schema::hasColumn('solicitudes', 'titulo_acto')) {
                $table->string('titulo_acto')->after('institucion_id');
            }
            if (!Schema::hasColumn('solicitudes', 'archivo_word_path')) {
                $table->string('archivo_word_path')->nullable()->after('titulo_acto');
            }
            if (!Schema::hasColumn('solicitudes', 'estado')) {
                $table->string('estado')->default('Pendiente')->after('archivo_word_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('solicitudes', 'orden_sumario')) $columns[] = 'orden_sumario';
            if (Schema::hasColumn('solicitudes', 'titulo_acto')) $columns[] = 'titulo_acto';
            if (Schema::hasColumn('solicitudes', 'archivo_word_path')) $columns[] = 'archivo_word_path';
            if (Schema::hasColumn('solicitudes', 'estado')) $columns[] = 'estado';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
