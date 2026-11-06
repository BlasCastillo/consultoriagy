<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Services\ModelPermissionAuditor;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Catálogo maestro de permisos unificados 100% en español (sgc_db).
     *
     * @var array<int, string>
     */
    private array $permissions = [
        'crear Usuario', 'leer Usuario', 'actualizar Usuario', 'eliminar Usuario',
        'crear Rol', 'leer Rol', 'actualizar Rol', 'eliminar Rol',
        'crear Permiso', 'leer Permiso', 'actualizar Permiso', 'eliminar Permiso',
        'crear Institucion', 'leer Institucion', 'actualizar Institucion', 'eliminar Institucion',
        'crear TipoInstitucion', 'leer TipoInstitucion', 'actualizar TipoInstitucion', 'eliminar TipoInstitucion',
        'crear Jefatura', 'leer Jefatura', 'actualizar Jefatura', 'eliminar Jefatura',
        'crear Gobernador', 'leer Gobernador', 'actualizar Gobernador', 'eliminar Gobernador',
        'crear Titulo', 'leer Titulo', 'actualizar Titulo', 'eliminar Titulo',
        'crear Solicitud', 'leer Solicitud', 'actualizar Solicitud', 'eliminar Solicitud',
        'crear Gaceta', 'leer Gaceta', 'actualizar Gaceta', 'eliminar Gaceta',
        'crear ActoAdministrativo', 'leer ActoAdministrativo', 'actualizar ActoAdministrativo', 'eliminar ActoAdministrativo',
        'crear PrestamoArchivo', 'leer PrestamoArchivo', 'actualizar PrestamoArchivo', 'eliminar PrestamoArchivo',
        'crear InventarioArchivo', 'leer InventarioArchivo', 'actualizar InventarioArchivo', 'eliminar InventarioArchivo',
        'crear Sede', 'leer Sede', 'actualizar Sede', 'eliminar Sede',
        'crear Estante', 'leer Estante', 'actualizar Estante', 'eliminar Estante',
        'crear Gaveta', 'leer Gaveta', 'actualizar Gaveta', 'eliminar Gaveta',
        'crear Poa', 'leer Poa', 'actualizar Poa', 'eliminar Poa',
        'crear ActividadPoa', 'leer ActividadPoa', 'actualizar ActividadPoa', 'eliminar ActividadPoa',
        'crear MetaTrimestral', 'leer MetaTrimestral', 'actualizar MetaTrimestral', 'eliminar MetaTrimestral',
        'crear Replanificacion', 'leer Replanificacion', 'actualizar Replanificacion', 'eliminar Replanificacion',
        'crear FichaActividad', 'leer FichaActividad', 'actualizar FichaActividad', 'eliminar FichaActividad',
        'crear ImagenFicha', 'leer ImagenFicha', 'actualizar ImagenFicha', 'eliminar ImagenFicha',
        'crear Evidencia', 'leer Evidencia', 'actualizar Evidencia', 'eliminar Evidencia',
        'crear Atencion', 'leer Atencion', 'actualizar Atencion', 'eliminar Atencion',
        'crear Bitacora', 'leer Bitacora', 'actualizar Bitacora', 'eliminar Bitacora',
        'crear Respaldo', 'leer Respaldo', 'actualizar Respaldo', 'eliminar Respaldo',
        'crear Dashboard', 'leer Dashboard', 'actualizar Dashboard', 'eliminar Dashboard',
    ];

    /**
     * Matriz de permisos en español por rol en sgc_db.
     *
     * @var array<string, array<int, string>>
     */
    private array $rolePermissions = [
        'Digitalizador' => [
            'crear Gaceta',
            'leer Gaceta',
            'actualizar Gaceta',
            'leer Poa',
            'leer PrestamoArchivo',
            'leer Dashboard',
        ],
        'Representante Institucional' => [
            'crear Solicitud',
            'leer Solicitud',
            'leer Gaceta',
        ],
        'Coordinador General' => [
            'crear ActividadPoa',
            'leer ActividadPoa',
            'actualizar ActividadPoa',
            'eliminar ActividadPoa',
            'crear ActoAdministrativo',
            'leer ActoAdministrativo',
            'actualizar ActoAdministrativo',
            'crear Evidencia',
            'leer Evidencia',
            'actualizar Evidencia',
            'crear FichaActividad',
            'leer FichaActividad',
            'actualizar FichaActividad',
            'eliminar FichaActividad',
            'crear ImagenFicha',
            'leer ImagenFicha',
            'actualizar ImagenFicha',
            'eliminar ImagenFicha',
            'crear Jefatura',
            'leer Jefatura',
            'actualizar Jefatura',
            'crear MetaTrimestral',
            'leer MetaTrimestral',
            'actualizar MetaTrimestral',
            'crear Poa',
            'leer Poa',
            'actualizar Poa',
            'crear PrestamoArchivo',
            'leer PrestamoArchivo',
            'actualizar PrestamoArchivo',
            'eliminar PrestamoArchivo',
            'crear Replanificacion',
            'leer Replanificacion',
            'actualizar Replanificacion',
            'leer Gaceta',
            'leer Gobernador',
            'leer Institucion',
            'leer TipoInstitucion',
            'leer Permiso',
            'leer Rol',
            'leer Usuario',
            'leer Bitacora',
            'leer Respaldo',
            'crear Respaldo',
            'eliminar Respaldo',
            'leer Atencion',
            'crear Atencion',
            'leer Dashboard',
        ],
        'Director de Consultoría Jurídica' => [
            'crear ActividadPoa',
            'leer ActividadPoa',
            'actualizar ActividadPoa',
            'eliminar ActividadPoa',
            'crear ActoAdministrativo',
            'leer ActoAdministrativo',
            'actualizar ActoAdministrativo',
            'eliminar ActoAdministrativo',
            'crear Evidencia',
            'leer Evidencia',
            'actualizar Evidencia',
            'eliminar Evidencia',
            'crear FichaActividad',
            'leer FichaActividad',
            'actualizar FichaActividad',
            'eliminar FichaActividad',
            'leer Gaceta',
            'eliminar Gaceta',
            'crear Gobernador',
            'leer Gobernador',
            'actualizar Gobernador',
            'crear ImagenFicha',
            'leer ImagenFicha',
            'actualizar ImagenFicha',
            'eliminar ImagenFicha',
            'crear Institucion',
            'leer Institucion',
            'actualizar Institucion',
            'leer TipoInstitucion',
            'crear Jefatura',
            'leer Jefatura',
            'actualizar Jefatura',
            'eliminar Jefatura',
            'crear MetaTrimestral',
            'leer MetaTrimestral',
            'actualizar MetaTrimestral',
            'eliminar MetaTrimestral',
            'crear Permiso',
            'leer Permiso',
            'actualizar Permiso',
            'crear Poa',
            'leer Poa',
            'actualizar Poa',
            'eliminar Poa',
            'crear PrestamoArchivo',
            'leer PrestamoArchivo',
            'actualizar PrestamoArchivo',
            'eliminar PrestamoArchivo',
            'crear Replanificacion',
            'leer Replanificacion',
            'actualizar Replanificacion',
            'eliminar Replanificacion',
            'crear Rol',
            'leer Rol',
            'actualizar Rol',
            'leer Solicitud',
            'leer Titulo',
            'actualizar Titulo',
            'crear Usuario',
            'leer Usuario',
            'actualizar Usuario',
            'leer Bitacora',
            'leer Respaldo',
            'crear Respaldo',
            'eliminar Respaldo',
            'leer Atencion',
            'crear Atencion',
            'leer Dashboard',
        ],
        'Coordinador de Archivo' => [
            'crear Estante',
            'leer Estante',
            'actualizar Estante',
            'eliminar Estante',
            'crear FichaActividad',
            'leer FichaActividad',
            'actualizar FichaActividad',
            'eliminar FichaActividad',
            'crear Gaveta',
            'leer Gaveta',
            'actualizar Gaveta',
            'eliminar Gaveta',
            'crear PrestamoArchivo',
            'leer PrestamoArchivo',
            'actualizar PrestamoArchivo',
            'eliminar PrestamoArchivo',
            'crear InventarioArchivo',
            'leer InventarioArchivo',
            'actualizar InventarioArchivo',
            'eliminar InventarioArchivo',
            'crear Sede',
            'leer Sede',
            'actualizar Sede',
            'eliminar Sede',
            'leer Poa',
            'leer Dashboard',
        ],
        'Coordinador de Atención al Ciudadano' => [
            'crear Atencion',
            'leer Atencion',
            'leer Dashboard',
        ],
        'Coordinador de Seguimiento y Control de Políticas Públicas' => [
            'crear ActividadPoa',
            'leer ActividadPoa',
            'actualizar ActividadPoa',
            'eliminar ActividadPoa',
            'crear FichaActividad',
            'leer FichaActividad',
            'actualizar FichaActividad',
            'eliminar FichaActividad',
            'crear ImagenFicha',
            'leer ImagenFicha',
            'actualizar ImagenFicha',
            'eliminar ImagenFicha',
            'crear Poa',
            'leer Poa',
            'actualizar Poa',
            'eliminar Poa',
            'leer ActoAdministrativo',
            'leer Gaceta',
            'leer PrestamoArchivo',
            'leer Dashboard',
        ],
        'Coordinador de Legislación y Asuntos Jurídicos' => [
            'crear Evidencia',
            'leer Evidencia',
            'actualizar Evidencia',
            'crear FichaActividad',
            'leer FichaActividad',
            'actualizar FichaActividad',
            'crear ImagenFicha',
            'leer ImagenFicha',
            'actualizar ImagenFicha',
            'crear MetaTrimestral',
            'leer MetaTrimestral',
            'actualizar MetaTrimestral',
            'leer ActividadPoa',
            'leer Gaceta',
            'leer Poa',
            'leer PrestamoArchivo',
            'leer Dashboard',
        ],
        'Jefe de Asuntos Jurídicos' => [
            'crear ActoAdministrativo',
            'leer ActoAdministrativo',
            'leer Gaceta',
            'leer Poa',
            'leer PrestamoArchivo',
            'leer Dashboard',
        ],
        'Jefe de Legislación' => [
            'crear ActoAdministrativo',
            'leer ActoAdministrativo',
            'leer Gaceta',
            'leer Poa',
            'leer PrestamoArchivo',
            'leer Dashboard',
        ],
        'Coordinador de Publicaciones Oficiales' => [
            'crear Evidencia',
            'leer Evidencia',
            'actualizar Evidencia',
            'crear Gaceta',
            'leer Gaceta',
            'actualizar Gaceta',
            'eliminar Gaceta',
            'leer ActividadPoa',
            'leer FichaActividad',
            'actualizar FichaActividad',
            'leer ImagenFicha',
            'actualizar ImagenFicha',
            'leer MetaTrimestral',
            'leer Poa',
            'leer PrestamoArchivo',
            'leer Solicitud',
            'actualizar Solicitud',
            'leer Dashboard',
        ],
    ];

    /**
     * Ejecuta la siembra de roles y permisos unificados en español.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = array_values(array_unique(array_merge(
            $this->permissions,
            ModelPermissionAuditor::getAllPermissions()
        )));

        // 1. Eliminar permisos obsoletos en inglés
        Permission::whereNotIn('name', $allPermissions)->delete();

        // 2. Crear permisos vigentes en español
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 3. Crear roles vigentes (incluye Super Admin con acceso total)
        $roles = array_keys($this->rolePermissions);
        $roles[] = 'Super Admin';

        foreach (array_unique($roles) as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // 4. Asignación de permisos por rol (idempotente)
        foreach ($this->rolePermissions as $roleName => $permissions) {
            Role::findByName($roleName, 'web')->syncPermissions($permissions);
        }

        // 5. Super Admin recibe todos los permisos unificados en español
        Role::findByName('Super Admin', 'web')->syncPermissions($allPermissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
