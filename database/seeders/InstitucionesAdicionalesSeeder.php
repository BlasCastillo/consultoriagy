<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Seeder;

class InstitucionesAdicionalesSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = [
            // Fundaciones
            ['nombre' => 'Fundación Yaracuy Bonito', 'siglas' => null],
            ['nombre' => 'Fundación Pueblo Sano', 'siglas' => null],
            ['nombre' => 'Fundación Niño Jesús del Edo. Yaracuy (Pediátrico)', 'siglas' => null],
            ['nombre' => 'Fundación Regional El Niño Simón', 'siglas' => null],
            // Institutos
            ['nombre' => 'Instituto de Hábitat y Vivienda del Edo. Yaracuy', 'siglas' => 'IHAVEY'],
            ['nombre' => 'Instituto Autónomo de Ayuda a los Func. de Seguridad del Edo. Yaracuy', 'siglas' => 'IAA'],
            ['nombre' => 'Instituto Autónomo Cuerpo de Bomberos del Edo. Yaracuy', 'siglas' => 'IABOY'],
            ['nombre' => 'Instituto Autónomo del Deporte del Edo. Yaracuy', 'siglas' => 'FUNDEY'],
            ['nombre' => 'Instituto Autónomo de Desarrollo Económico del Edo. Yaracuy', 'siglas' => 'IADEY'],
            ['nombre' => 'Instituto de Cultura del Edo. Yaracuy', 'siglas' => 'ICEY'],
            ['nombre' => 'Instituto Autónomo de la Juventud del Edo. Yaracuy', 'siglas' => 'INAJUDEY'],
            ['nombre' => 'Instituto de Vialidad y Obras Públicas del Edo. Yaracuy', 'siglas' => 'IVOPEY'],
            // Servicios
            ['nombre' => 'Servicio Socialista Abastecimiento del Edo. Yaracuy', 'siglas' => null],
            ['nombre' => 'Servicio Socialista Agrícola del Edo. Yaracuy', 'siglas' => 'SESAY'],
            ['nombre' => 'Servicio Socialista de Logística, Mantenimiento y Transporte del Edo. Yaracuy', 'siglas' => 'BUS YARACUY'],
            ['nombre' => 'Servicio Desconcentrado Socialista Gas Yaracuy', 'siglas' => null],
            ['nombre' => 'Servicio Autónomo de la Infraestructura Deportiva, Cultural y Turística del Edo. Yaracuy', 'siglas' => 'SAIEY'],
            ['nombre' => 'Servicio Socialista de Saneamiento y Disposición de Desechos Sólidos del Edo. Yaracuy', 'siglas' => 'SANEAMIENTO'],
            // Corporaciones
            ['nombre' => 'Instituto Autónomo de la Salud  del Estado Yaracuy', 'siglas' => 'PROSALUD'],
            ['nombre' => 'Corporación Yaracuyana de Turismo', 'siglas' => 'CORYATUR'],
            // Empresas y Liquidación
            ['nombre' => 'Aguas de Yaracuy C.A.', 'siglas' => null],
            ['nombre' => 'Empresa Socialista Minerales de Yaracuy C.A.', 'siglas' => null],
            ['nombre' => 'Empresa Socialista Construcciones', 'siglas' => null],
            ['nombre' => 'Empresa Socialista Asfalto', 'siglas' => null],
        ];

        foreach ($instituciones as $institucion) {
            Institucion::updateOrCreate(['nombre' => $institucion['nombre']], $institucion);
        }
    }
}
