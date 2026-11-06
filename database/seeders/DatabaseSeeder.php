<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Catálogos base
            EntidadesBaseSeeder::class,
            InstitucionesAdicionalesSeeder::class,
            ClasificarInstitucionesSeeder::class,
            JefaturaSeeder::class,
            GobernadorSeeder::class,
            ArchivoFisicoSeeder::class,

            // Seguridad
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
        ]);
    }
}
