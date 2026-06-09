<?php

namespace Database\Seeders;

use App\Models\Jefatura;
use Illuminate\Database\Seeder;

class JefaturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jefaturas = [
            'Despacho Consultora',
            'Coordinación General',
            'Jefatura de Publicaciones',
            'Jefatura de Legislación y Asuntos Jurídicos',
        ];

        foreach ($jefaturas as $nombre) {
            Jefatura::firstOrCreate(['nombre' => $nombre], [
                'descripcion' => 'Jefatura: ' . $nombre
            ]);
        }
    }
}
