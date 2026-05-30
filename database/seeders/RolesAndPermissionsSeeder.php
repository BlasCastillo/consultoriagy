<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\ModelPermissionAuditor;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear permisos dinámicos basados en la auditoría de modelos
        $matrix = ModelPermissionAuditor::getPermissionsMatrix();
        
        $allPermissions = [];
        $readPermissions = [];

        foreach ($matrix as $model => $perms) {
            foreach ($perms as $permName) {
                // Crear permiso si no existe
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $allPermissions[] = $permission;
                
                if (str_starts_with($permName, 'read')) {
                    $readPermissions[] = $permission;
                }
            }
        }

        // 2. Crear permisos explícitos solicitados para el módulo de Gacetas
        $gacetaPermissions = [
            'ver gacetas',
            'crear gacetas',
            'editar gacetas',
            'publicar gacetas',
            'eliminar gacetas'
        ];

        foreach ($gacetaPermissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName]);
            $allPermissions[] = $permission;
            if ($permName === 'ver gacetas') {
                $readPermissions[] = $permission;
            }
        }

        // Obtener la colección de todos los nombres de permisos para asignación rápida
        $allPermissionNames = collect($allPermissions)->pluck('name')->toArray();
        $readPermissionNames = collect($readPermissions)->pluck('name')->toArray();

        // 3. Crear roles (tanto los antiguos como los nuevos solicitados para evitar regresiones)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminNewRole = Role::firstOrCreate(['name' => 'Super Administrador']);
        $directorRole = Role::firstOrCreate(['name' => 'Director']);
        $coordinadorRole = Role::firstOrCreate(['name' => 'Coordinador']);
        $usuarioRole = Role::firstOrCreate(['name' => 'Usuario']);
        $institucionRole = Role::firstOrCreate(['name' => 'Institucion']);
        $institucionalRole = Role::firstOrCreate(['name' => 'Institucional']);
        
        $jefeRole = Role::firstOrCreate(['name' => 'Jefe de Digitalización']);
        $digitalizadorRole = Role::firstOrCreate(['name' => 'Digitalizador']);

        // 4. Asignar permisos de forma segura e idempotente con syncPermissions()
        // Super Administradores tienen acceso total
        $superAdminRole->syncPermissions($allPermissionNames);
        $superAdminNewRole->syncPermissions($allPermissionNames);
        $directorRole->syncPermissions($allPermissionNames);

        // Coordinador: todo menos eliminación
        $coordPermissions = array_filter($allPermissionNames, fn($name) => !str_starts_with($name, 'delete') && !str_starts_with($name, 'eliminar'));
        $coordinadorRole->syncPermissions($coordPermissions);

        // Usuario e Institucionales: solo lectura/ver gacetas
        $usuarioRole->syncPermissions($readPermissionNames);
        $institucionRole->syncPermissions(['ver gacetas']);
        $institucionalRole->syncPermissions(['ver gacetas']);

        // Permisos para Jefe de Digitalización
        $jefeRole->syncPermissions([
            'ver gacetas', 'crear gacetas', 'editar gacetas', 'eliminar gacetas', 'publicar gacetas',
            'create Gaceta', 'read Gaceta', 'update Gaceta', 'delete Gaceta',
            'create SumarioGaceta', 'read SumarioGaceta', 'update SumarioGaceta', 'delete SumarioGaceta',
            'read Institution', 'read Gobernador', 'read Titulo'
        ]);

        // Permisos para Digitalizador
        $digitalizadorRole->syncPermissions([
            'ver gacetas', 'editar gacetas',
            'read Gaceta', 'update Gaceta'
        ]);

        // 5. Crear usuarios de prueba de forma completamente idempotente
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Super Admin User', 'password' => Hash::make('password')]
        );
        $superAdminUser->assignRole($superAdminRole);

        $directorUser = User::firstOrCreate(
            ['email' => 'director@example.com'],
            ['name' => 'Director de Área', 'password' => Hash::make('password')]
        );
        $directorUser->assignRole($directorRole);

        $usuarioCommon = User::firstOrCreate(
            ['email' => 'usuario@example.com'],
            ['name' => 'Usuario Común', 'password' => Hash::make('password')]
        );
        $usuarioCommon->assignRole($usuarioRole);
    }
}

