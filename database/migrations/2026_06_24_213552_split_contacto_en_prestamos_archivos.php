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
        Schema::table('prestamos_archivos', function (Blueprint $table) {
            $table->dropColumn('contacto');
            $table->string('telefono')->nullable()->after('solicitante_nombres');
            $table->string('correo')->nullable()->after('telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos_archivos', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'correo']);
            $table->string('contacto')->nullable();
        });
    }
};
