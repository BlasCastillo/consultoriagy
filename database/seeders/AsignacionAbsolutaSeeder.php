<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AsignacionAbsolutaSeeder extends Seeder
{
    public function run(): void
    {
        // Rescatar los roles de admin
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole = Role::firstOrCreate(['name' => 'Súper Administrador']);

        // Extraer todos los permisos vigentes
        $todosLosPermisos = Permission::all();

        // Sincronización absoluta
        $adminRole->syncPermissions($todosLosPermisos);
        $superAdminRole->syncPermissions($todosLosPermisos);

        // Asegurar que la cuenta admin@example.com tenga ambos roles por si acaso
        $adminUser = User::where('email', 'admin@example.com')->first();
        if($adminUser){
            $adminUser->syncRoles(['Super Admin', 'Súper Administrador']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
