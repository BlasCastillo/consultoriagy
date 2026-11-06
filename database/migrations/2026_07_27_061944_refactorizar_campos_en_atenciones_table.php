<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // La columna datos_persona existía en el esquema viejo, pero no en
        // create_atencions_table. Se elimina solo si está presente para que
        // migrate:fresh funcione y para que la migración siga siendo segura
        // sobre bases parcialmente actualizadas.
        if (Schema::hasColumn('atencions', 'datos_persona')) {
            Schema::table('atencions', function (Blueprint $table) {
                $table->dropColumn('datos_persona');
            });
        }

        Schema::table('atencions', function (Blueprint $table) {
            if (! Schema::hasColumn('atencions', 'cedula')) {
                $table->string('cedula', 20)->after('id');
            }
            if (! Schema::hasColumn('atencions', 'nombre')) {
                $table->string('nombre', 100)->after('cedula');
            }
            if (! Schema::hasColumn('atencions', 'apellido')) {
                $table->string('apellido', 100)->after('nombre');
            }
            if (! Schema::hasColumn('atencions', 'telefono')) {
                $table->string('telefono', 20)->nullable()->after('apellido');
            }
            if (! Schema::hasColumn('atencions', 'correo')) {
                $table->string('correo', 150)->nullable()->after('telefono');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atencions', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'nombre', 'apellido', 'telefono', 'correo']);
            $table->string('datos_persona')->nullable()->after('id');
        });
    }
};
