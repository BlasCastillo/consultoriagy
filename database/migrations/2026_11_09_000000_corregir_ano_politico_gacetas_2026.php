<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El año político 2026 corresponde al CXVIII (118). Algunos registros
     * quedaron guardados con 168/CLXVIII porque el formulario mostraba ese
     * valor como ejemplo. Se normalizan esos registros.
     */
    public function up(): void
    {
        DB::table('gacetas')
            ->where('anio', 2026)
            ->whereIn('ano_politico', ['168', '168°', '168º', 'CLXVIII'])
            ->update(['ano_politico' => 'CXVIII']);
    }

    public function down(): void
    {
        // No se revierte: CXVIII es el valor correcto para las gacetas de 2026.
    }
};
