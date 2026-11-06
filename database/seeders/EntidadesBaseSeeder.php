<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Seeder;

class EntidadesBaseSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = [
            ['nombre' => 'Gobernación del estado Yaracuy', 'siglas' => null],
            ['nombre' => 'Consejo Legislativo del estado Yaracuy', 'siglas' => 'CLEY'],
            ['nombre' => 'Procuraduría General del estado Yaracuy', 'siglas' => null],
            ['nombre' => 'Contraloría del estado Yaracuy', 'siglas' => null],
        ];

        foreach ($instituciones as $institucion) {
            Institucion::updateOrCreate(['nombre' => $institucion['nombre']], $institucion);
        }
    }
}
