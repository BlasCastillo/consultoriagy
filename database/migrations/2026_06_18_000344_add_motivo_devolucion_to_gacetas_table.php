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
        Schema::table('gacetas', function (Blueprint $table) {
            $table->text('motivo_devolucion')->nullable()->comment('Motivo por el que el digitalizador devuelve la gaceta al jefe');
        });
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE gacetas ALTER COLUMN estado TYPE VARCHAR(255)");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE gacetas ALTER COLUMN estado SET DEFAULT 'En Proceso'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gacetas', function (Blueprint $table) {
            $table->dropColumn('motivo_devolucion');
        });
    }
};
