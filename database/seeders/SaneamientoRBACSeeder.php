<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class SaneamientoRBACSeeder extends Seeder
{
    public function run(): void
    {
        // Paso A: Limpieza de Caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Paso B: Definición de Permisos
        $permisos = [
            'ver gaceta', 'crear gaceta', 
            'ver solicitud', 'crear solicitud', 'evaluar solicitud', 
            'gestionar usuarios', 'gestionar instituciones', 
            'ver poa', 'gestionar poa'
        ];
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Paso C: Definición y Sincronización de Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Súper Administrador']);
        $superAdmin->syncPermissions(Permission::all());

        $jefePub = Role::firstOrCreate(['name' => 'Jefe de Publicaciones']);
        $jefePub->syncPermissions(['ver gaceta', 'crear gaceta', 'ver solicitud', 'evaluar solicitud', 'ver poa']);

        $repInst = Role::firstOrCreate(['name' => 'Representante Institucional']);
        $repInst->syncPermissions(['ver gaceta', 'ver solicitud', 'crear solicitud']);

        $digitalizador = Role::firstOrCreate(['name' => 'Digitalizador']);
        $digitalizador->syncPermissions(['ver gaceta', 'crear gaceta', 'ver poa']);

        $analistaPoa = Role::firstOrCreate(['name' => 'Analista POA']);
        $analistaPoa->syncPermissions(['ver gaceta', 'ver poa', 'gestionar poa']);

        // Paso D: Mapeo de Correos Actuales (Pruebas) a los Nuevos Roles
        $mapeoUsuarios = [
            'admin@example.com' => 'Súper Administrador',
            'superadmin@yaracuy.gob.ve' => 'Súper Administrador',
            'jefel@example.com' => 'Jefe de Publicaciones',
            'fundey@example.com' => 'Representante Institucional',
            'prosalud@example.com' => 'Representante Institucional',
            'inajudey@example.com' => 'Representante Institucional',
            'digitalizador@example.com' => 'Digitalizador',
            'jefed@example.com' => 'Digitalizador',
        ];

        foreach($mapeoUsuarios as $email => $rolName) {
            $user = User::where('email', $email)->first();
            if($user) {
                $user->syncRoles([$rolName]);
                $user->update(['status' => true]);
            }
        }

        // Paso E: Inactivar y despojar a todos los demás usuarios obsoletos
        $usuariosVitales = array_keys($mapeoUsuarios);
        $usuariosObsoletos = User::whereNotIn('email', $usuariosVitales)->get();

        foreach ($usuariosObsoletos as $user) {
            $user->syncRoles([]); // Despoja de roles
            $user->update(['status' => false]); // Inactiva cuenta
        }
        
        $this->command->info('Seguridad RBAC y usuarios saneados con éxito.');
    }
}
