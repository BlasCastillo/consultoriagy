<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixSeguridadSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear permisos faltantes para que el menú no rompa
        $permisosExtra = ['gestionar configuracion', 'ver reportes', 'ver procesos'];
        foreach($permisosExtra as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // 2. Resetear al Digitalizador a sus funciones básicas
        $digitalizador = Role::where('name', 'Digitalizador')->first();
        if ($digitalizador) {
            // syncPermissions REEMPLAZA todos los permisos viejos por solo estos 4
            $digitalizador->syncPermissions(['ver gaceta', 'crear gaceta', 'ver poa', 'ver procesos']);
        }

        // 3. Limpiar Caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
