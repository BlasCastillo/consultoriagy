<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UpdateRolesNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar el rol 'Institucion' (asegúrate de que el nombre coincida exactamente con cómo está en tu DB)
        $role = Role::where('name', 'Institucion')->first();

        if ($role) {
            $role->name = 'Representante Institucional';
            $role->save();
            
            $this->command->info('Rol "Institucion" actualizado a "Representante Institucional" exitosamente.');
        } else {
            $this->command->warn('No se encontró ningún rol con el nombre "Institucion".');
        }
    }
}
