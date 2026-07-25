<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UbicacionFisicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\UbicacionFisica::create(['sede' => 'Consultoría Jurídica', 'estante' => 'Archivo Principal', 'gaveta' => 'Tramo 1']);
        \App\Models\UbicacionFisica::create(['sede' => 'Archivo Histórico', 'estante' => 'Estante A', 'gaveta' => 'Caja 1']);
        \App\Models\UbicacionFisica::create(['sede' => 'Centro de Historia', 'estante' => 'Estante B', 'gaveta' => 'Caja 1']);
        \App\Models\UbicacionFisica::create(['sede' => 'Biblioteca Regional Dr. Félix Pífano', 'estante' => 'Sección Legal', 'gaveta' => 'Nivel 1']);
    }
}
