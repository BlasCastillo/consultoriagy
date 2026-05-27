<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GobernadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $titulos = ['Lcdo.', 'Ing.', 'Dr.', 'Abg.', 'Prof.'];
        foreach ($titulos as $abreviatura) {
            \App\Models\Titulo::firstOrCreate(['abreviatura' => $abreviatura]);
        }

        $tituloLcdo = \App\Models\Titulo::where('abreviatura', 'Lcdo.')->first();

        \App\Models\Gobernador::firstOrCreate(
            ['nombres' => 'Julio César', 'apellidos' => 'León Heredia'],
            [
                'titulo_id' => $tituloLcdo->id,
                'estado' => true,
            ]
        );
    }
}
