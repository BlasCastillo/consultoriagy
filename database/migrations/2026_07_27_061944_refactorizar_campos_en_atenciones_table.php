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
        Schema::table('atencions', function (Blueprint $table) {
            $table->dropColumn('datos_persona');
            $table->string('cedula', 20)->after('id');
            $table->string('nombre', 100)->after('cedula');
            $table->string('apellido', 100)->after('nombre');
            $table->string('telefono', 20)->nullable()->after('apellido');
            $table->string('correo', 150)->nullable()->after('telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atencions', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'nombre', 'apellido', 'telefono', 'correo']);
            $table->string('datos_persona')->after('id');
        });
    }
};
