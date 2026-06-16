<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institucion;
use App\Models\Jefatura;

class EntidadesBaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Instituciones
        $instituciones = [
            ['nombre' => 'Gobernación del estado Yaracuy', 'siglas' => null],
            ['nombre' => 'Consejo Legislativo del estado Yaracuy', 'siglas' => 'CLEY'],
            ['nombre' => 'Procuraduría General del estado Yaracuy', 'siglas' => null],
            ['nombre' => 'Contraloría del estado Yaracuy', 'siglas' => null],
        ];

        foreach ($instituciones as $institucion) {
            Institucion::updateOrCreate(['nombre' => $institucion['nombre']], $institucion);
        }

        // 2. Crear Jefaturas
        // Consultor Jurídico como máxima autoridad de la dirección
        $consultorJuridico = Jefatura::updateOrCreate(
            ['nombre' => 'Consultor Jurídico'],
            ['nivel_jerarquico' => 'Dirección Superior', 'dependencia_id' => null]
        );

        // Coordinación General depende del Consultor Jurídico
        $coordinacionGeneral = Jefatura::updateOrCreate(
            ['nombre' => 'Coordinación General'],
            ['nivel_jerarquico' => 'Coordinación', 'dependencia_id' => $consultorJuridico->id]
        );

        // Sub-coordinaciones y Jefaturas
        $otrasJefaturas = [
            ['nombre' => 'Coordinación de Consultoría Jurídica', 'nivel_jerarquico' => 'Coordinación', 'dependencia_id' => $coordinacionGeneral->id],
            ['nombre' => 'Coordinación de Seguimiento y Control de Políticas Públicas', 'nivel_jerarquico' => 'Coordinación', 'dependencia_id' => $coordinacionGeneral->id],
            ['nombre' => 'Coordinación de Publicaciones Oficiales', 'nivel_jerarquico' => 'Coordinación', 'dependencia_id' => $coordinacionGeneral->id],
            ['nombre' => 'Jefatura de Legislación y Asuntos Jurídicos', 'nivel_jerarquico' => 'Jefatura', 'dependencia_id' => $coordinacionGeneral->id],
            ['nombre' => 'Jefatura de Digitalización', 'nivel_jerarquico' => 'Jefatura', 'dependencia_id' => $coordinacionGeneral->id],
        ];

        foreach ($otrasJefaturas as $jefatura) {
            Jefatura::updateOrCreate(['nombre' => $jefatura['nombre']], $jefatura);
        }
    }
}
