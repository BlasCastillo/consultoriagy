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

        // Obtener todos los permisos posibles generados automáticamente por auditoría
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

        // Crear roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $directorRole = Role::firstOrCreate(['name' => 'Director']);
        $coordinadorRole = Role::firstOrCreate(['name' => 'Coordinador']);
        $usuarioRole = Role::firstOrCreate(['name' => 'Usuario']);

        // Asignar permisos:
        // Super Admin tiene todos los permisos por defecto en AuthServiceProvider o implicitamente, pero se los damos
        // O mejor: El Super Admin no requiere permisos directos si lo bypasseamos. Pero el Director sí necesita gestión total:
        $directorRole->syncPermissions($allPermissions);

        // Coordinador: digamos que puede leer y crear pero no eliminar. Lo ajustaremos genérico (todo menos delete)
        $coordPermissions = array_filter($allPermissions, fn($p) => !str_starts_with($p->name, 'delete'));
        $coordinadorRole->syncPermissions($coordPermissions);

        // Usuario: solo lectura
        $usuarioRole->syncPermissions($readPermissions);

        // Crear usuarios de prueba
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Super Admin User', 'password' => Hash::make('password')]
        );
        $superAdmin->assignRole($superAdminRole);

        $director = User::firstOrCreate(
            ['email' => 'director@example.com'],
            ['name' => 'Director de Área', 'password' => Hash::make('password')]
        );
        $director->assignRole($directorRole);

        $usuario = User::firstOrCreate(
            ['email' => 'usuario@example.com'],
            ['name' => 'Usuario Común', 'password' => Hash::make('password')]
        );
        $usuario->assignRole($usuarioRole);
    }
}
