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
        // Modificar columna tipo de Enum a Varchar y hacerla nullable
        DB::statement('ALTER TABLE gacetas ALTER COLUMN tipo DROP DEFAULT');
        DB::statement('ALTER TABLE gacetas ALTER COLUMN tipo TYPE VARCHAR(255)');
        DB::statement('ALTER TABLE gacetas ALTER COLUMN tipo DROP NOT NULL');

        // Modificar columnas numero y anio a nullable
        DB::statement('ALTER TABLE gacetas ALTER COLUMN numero DROP NOT NULL');
        DB::statement('ALTER TABLE gacetas ALTER COLUMN anio DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversar a NOT NULL puede fallar si hay datos con NULL. 
        // Se deja comentado o se usa una precaución:
        // DB::statement('ALTER TABLE gacetas ALTER COLUMN numero SET NOT NULL');
        // DB::statement('ALTER TABLE gacetas ALTER COLUMN anio SET NOT NULL');
        // DB::statement('ALTER TABLE gacetas ALTER COLUMN tipo SET NOT NULL');
    }
};
