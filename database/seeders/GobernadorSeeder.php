<?php

namespace Database\Seeders;

use App\Models\Gobernador;
use App\Models\Titulo;
use Illuminate\Database\Seeder;

class GobernadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $titulos = [
            ['abreviatura' => 'Lcdo.', 'descripcion' => 'Licenciado (a)'],
            ['abreviatura' => 'Ing.', 'descripcion' => 'Ingeniero (a)'],
            ['abreviatura' => 'Dr.', 'descripcion' => 'Doctor (a)'],
            ['abreviatura' => 'Abg.', 'descripcion' => 'Abogado (a)'],
            ['abreviatura' => 'Prof.', 'descripcion' => 'Profesor (a)'],
            ['abreviatura' => 'Mgs.', 'descripcion' => 'Magister'],
        ];

        foreach ($titulos as $titulo) {
            Titulo::updateOrCreate(
                ['abreviatura' => $titulo['abreviatura']],
                ['descripcion' => $titulo['descripcion']]
            );
        }

        $profesor = Titulo::where('abreviatura', 'Prof.')->value('id');
        $licenciado = Titulo::where('abreviatura', 'Lcdo.')->value('id');

        Gobernador::updateOrCreate(
            ['nombres' => 'Leonardo Gabriel', 'apellidos' => 'Intoci Cipullo'],
            ['titulo_id' => $profesor, 'estado' => false]
        );

        Gobernador::updateOrCreate(
            ['nombres' => 'JULIO CESAR', 'apellidos' => 'LEON HEREDIA'],
            ['titulo_id' => $licenciado, 'estado' => true]
        );
    }
}
