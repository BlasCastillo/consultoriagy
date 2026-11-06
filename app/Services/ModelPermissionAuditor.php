<?php

namespace App\Services;

class ModelPermissionAuditor
{
    /**
     * Catálogo maestro de módulos del sistema con sus etiquetas legibles en español.
     *
     * @var array<string, string>
     */
    private static array $moduleLabels = [
        'Usuario' => 'Usuarios',
        'Rol' => 'Roles',
        'Permiso' => 'Permisos',
        'Institucion' => 'Instituciones',
        'TipoInstitucion' => 'Tipos de Institución',
        'Jefatura' => 'Estructura Interna (Jefaturas)',
        'Gobernador' => 'Gobernadores',
        'Titulo' => 'Títulos Académicos / Cargos',
        'Solicitud' => 'Solicitudes de Publicación',
        'Gaceta' => 'Gacetas Oficiales',
        'ActoAdministrativo' => 'Actos Administrativos',
        'PrestamoArchivo' => 'Archivo y Préstamos',
        'InventarioArchivo' => 'Inventario de Archivo',
        'Sede' => 'Sedes (Edificios)',
        'Estante' => 'Estantes',
        'Gaveta' => 'Gavetas',
        'Poa' => 'Planes Operativos Anuales (POA)',
        'ActividadPoa' => 'Actividades POA',
        'MetaTrimestral' => 'Metas Trimestrales POA',
        'Replanificacion' => 'Replanificaciones POA',
        'FichaActividad' => 'Fichas de Actividades',
        'ImagenFicha' => 'Imágenes de Fichas',
        'Evidencia' => 'Evidencias POA',
        'Atencion' => 'Atención al Ciudadano',
        'Bitacora' => 'Bitácora de Auditoría',
        'Respaldo' => 'Respaldos (Backups)',
        'Dashboard' => 'Panel Principal (Dashboard)',
    ];

    /**
     * Acciones estándar unificadas en español.
     *
     * @var array<int, string>
     */
    private static array $actions = [
        'crear',
        'leer',
        'actualizar',
        'eliminar',
    ];

    /**
     * Devuelve la lista maestra de módulos del sistema en español.
     *
     * @return array<int, string>
     */
    public static function getModels(): array
    {
        return array_keys(static::$moduleLabels);
    }

    /**
     * Devuelve la etiqueta legible en español para un módulo dado.
     */
    public static function getModuleLabel(string $module): string
    {
        return static::$moduleLabels[$module] ?? $module;
    }

    /**
     * Genera la matriz de permisos estándar en español agrupada por módulo.
     * Devuelve: ['Usuario' => ['crear Usuario', 'leer Usuario', 'actualizar Usuario', 'eliminar Usuario'], ...]
     *
     * @return array<string, array<int, string>>
     */
    public static function getPermissionsMatrix(): array
    {
        $matrix = [];

        foreach (static::getModels() as $module) {
            $matrix[$module] = [];
            foreach (static::$actions as $action) {
                $matrix[$module][] = "{$action} {$module}";
            }
        }

        return $matrix;
    }

    /**
     * Devuelve la lista plana de todos los permisos del sistema en español.
     *
     * @return array<int, string>
     */
    public static function getAllPermissions(): array
    {
        $permissions = [];

        foreach (static::getPermissionsMatrix() as $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }
}
