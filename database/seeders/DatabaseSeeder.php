<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear el usuario de prueba por defecto de forma idempotente
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Llamar a los seeders en el orden correcto
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            GacetasTestSeeder::class,
        ]);
    }
}
