<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosFaltantesSeeder extends Seeder
{
    public function run()
    {
        // 1. Permisos Faltantes de Archivo Físico
        $permisosArchivo = [
            'read Sedes', 'create Sedes', 'edit Sedes', 'delete Sedes',
            'read Estantes', 'create Estantes', 'edit Estantes', 'delete Estantes',
            'read Gavetas', 'create Gavetas', 'edit Gavetas', 'delete Gavetas',
        ];

        foreach ($permisosArchivo as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // 2. Permisos del Nuevo Módulo de Atención al Ciudadano
        $permisosAtencion = [
            'registrar atencion', 'ver atenciones'
        ];

        foreach ($permisosAtencion as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // 3. Asignación al Rol Específico
        $rolAtencion = Role::firstOrCreate(['name' => 'Coordinador de Atención al Ciudadano']);
        $rolAtencion->givePermissionTo($permisosAtencion);

        // Opcional: Asegurar que el Super Admin tenga todo
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}
