<?php

namespace Database\Seeders;

use App\Models\Estante;
use App\Models\Gaveta;
use App\Models\Sede;
use Illuminate\Database\Seeder;

class ArchivoFisicoSeeder extends Seeder
{
    /**
     * Reproduce la estructura de archivo físico vigente en sgc_db.
     */
    public function run(): void
    {
        $estructura = [
            'Consultoría Jurídica' => [
                'Archivo 1' => ['Gaveta 1'],
            ],
            'Archivo Histórico' => [
                'Estante 1' => ['Sección Legal'],
            ],
        ];

        foreach ($estructura as $sedeNombre => $estantes) {
            $sede = Sede::firstOrCreate(['nombre' => $sedeNombre]);

            foreach ($estantes as $estanteNombre => $gavetas) {
                $estante = Estante::firstOrCreate([
                    'sede_id' => $sede->id,
                    'nombre' => $estanteNombre,
                ]);

                foreach ($gavetas as $gavetaNombre) {
                    Gaveta::firstOrCreate([
                        'estante_id' => $estante->id,
                        'nombre' => $gavetaNombre,
                    ]);
                }
            }
        }
    }
}
