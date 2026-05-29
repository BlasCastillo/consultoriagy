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
        DB::statement('ALTER TABLE gacetas DROP CONSTRAINT IF EXISTS gacetas_estado_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding the check constraint might fail if the table already contains rows that don't pass it.
        // It's generally omitted here, but we could add it back strictly based on the old ENUM values.
        // DB::statement("ALTER TABLE gacetas ADD CONSTRAINT gacetas_estado_check CHECK (estado::text = ANY (ARRAY['Reservada'::character varying, 'Pendiente_Subida'::character varying, 'Publicada'::character varying, 'Reimpresa'::character varying]::text[]))");
    }
};
