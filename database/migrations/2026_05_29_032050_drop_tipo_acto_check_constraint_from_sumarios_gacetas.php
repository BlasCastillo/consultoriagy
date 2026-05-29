<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero cambiamos la columna tipo_acto a VARCHAR por si seguía siendo de tipo enum a nivel de DB
        DB::statement('ALTER TABLE sumarios_gacetas ALTER COLUMN tipo_acto DROP DEFAULT');
        DB::statement('ALTER TABLE sumarios_gacetas ALTER COLUMN tipo_acto TYPE VARCHAR(255)');
        
        // Luego eliminamos el check constraint
        DB::statement('ALTER TABLE sumarios_gacetas DROP CONSTRAINT IF EXISTS sumarios_gacetas_tipo_acto_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se revierte el constraint en el down por si la base de datos ya contiene valores fuera del check original
    }
};
