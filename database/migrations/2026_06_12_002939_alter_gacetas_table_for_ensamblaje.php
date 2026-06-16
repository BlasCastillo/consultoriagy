<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gacetas', function (Blueprint $table) {
            if (Schema::hasColumn('gacetas', 'institucion_id')) {
                // Try dropping foreign key first, wrap in try-catch in case it's not a FK
                try { $table->dropForeign(['institucion_id']); } catch (\Exception $e) {}
                $table->dropColumn('institucion_id');
            }
            if (Schema::hasColumn('gacetas', 'titulo')) {
                $table->dropColumn('titulo');
            }
            if (Schema::hasColumn('gacetas', 'titulo_acto')) {
                $table->dropColumn('titulo_acto');
            }
            if (Schema::hasColumn('gacetas', 'archivo_word')) {
                $table->dropColumn('archivo_word');
            }
            if (Schema::hasColumn('gacetas', 'ruta_archivo')) {
                // Just in case they meant ruta_archivo when they said archivo_word
                // $table->dropColumn('ruta_archivo'); // Let's keep it safe and not drop unless exact name matches
            }
            
            if (!Schema::hasColumn('gacetas', 'estado')) {
                $table->string('estado')->default('En Ensamblaje');
            }
        });

        if (Schema::hasColumn('gacetas', 'estado')) {
            DB::statement("ALTER TABLE gacetas ALTER COLUMN estado SET DEFAULT 'En Ensamblaje'");
        }
    }

    public function down(): void
    {
        // down
    }
};
