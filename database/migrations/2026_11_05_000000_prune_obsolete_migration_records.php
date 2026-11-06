<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Registros históricos que quedaron en la tabla `migrations` sin archivo
     * asociado (o cuyo archivo fue consolidado/eliminado en esta limpieza).
     *
     * @var array<int, string>
     */
    private array $obsolete = [
        // Archivos eliminados por consolidación (create + drop sin efecto neto).
        '2026_04_29_041257_create_institutions_table',
        '2026_04_29_041258_add_institution_id_to_users_table',
        '2026_05_26_135218_create_sumario_gacetas_table',
        '2026_05_29_032050_drop_tipo_acto_check_constraint_from_sumarios_gacetas',
        '2026_06_12_002950_drop_sumario_gacetas_table',
        '2026_06_12_014500_drop_institutions_table',
        '2026_07_24_190941_create_ubicacion_fisicas_table',
        // Registros huérfanos históricos (nunca existió el archivo en el repo).
        '2026_05_27_140628_create_titulos_table',
        '2026_05_27_140629_create_gobernadors_table',
        '2026_05_27_140630_add_gobernador_id_to_gacetas_table',
    ];

    public function up(): void
    {
        DB::table('migrations')->whereIn('migration', $this->obsolete)->delete();
    }

    public function down(): void
    {
        // No se restauran registros históricos obsoletos.
    }
};
