<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixRolesYPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Limpia la caché de Spatie al inicio
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Mapeo del Planificador
        $planificadorPermisos = [
            'create Poa', 'read Poa', 'update Poa', 'delete Poa',
            'crear poa', 'ver poa', 'editar poa', 'eliminar poa',
            'create ActividadPoa', 'read ActividadPoa', 'update ActividadPoa', 'delete ActividadPoa',
            'create MetaTrimestral', 'read MetaTrimestral', 'update MetaTrimestral', 'delete MetaTrimestral',
            'create Replanificacion', 'read Replanificacion', 'update Replanificacion', 'delete Replanificacion',
            'create Evidencia', 'read Evidencia', 'update Evidencia', 'delete Evidencia',
            'read Jefatura', 'read Institution'
        ];

        // Mapeo Jefes Base (Publicaciones y Legislación)
        $jefesBasePermisos = [
            'read Poa', 'ver poa', 'read ActividadPoa', 'read MetaTrimestral',
            'create Evidencia', 'read Evidencia', 'update Evidencia', 'delete Evidencia',
            'create FichaActividad', 'read FichaActividad', 'update FichaActividad',
            'create Replanificacion', 'read Replanificacion', 'registrar actividad poa'
        ];

        // Mapeo Jefe de Digitalización
        $jefeDigitalizacionPermisos = array_merge($jefesBasePermisos, [
            'create Gaceta', 'read Gaceta', 'update Gaceta', 'delete Gaceta',
            'crear gacetas', 'ver gacetas', 'editar gacetas', 'eliminar gacetas', 'publicar gacetas',
            'create SumarioGaceta', 'read SumarioGaceta', 'update SumarioGaceta', 'delete SumarioGaceta'
        ]);

        // Mapeo Digitalizador
        $digitalizadorPermisos = [
            'create Gaceta', 'read Gaceta', 'update Gaceta',
            'crear gacetas', 'ver gacetas', 'editar gacetas',
            'create SumarioGaceta', 'read SumarioGaceta', 'update SumarioGaceta',
            'create Evidencia', 'read Evidencia', 'registrar actividad poa'
        ];

        // Mapeo Alta Dirección (Consultor Jurídico y Coordinador)
        $altaDireccionPermisos = [
            'read Poa', 'ver poa', 'aprobar poa', 'read ActividadPoa', 'read MetaTrimestral',
            'read Evidencia', 'read Gaceta', 'ver gacetas'
        ];

        // Array global con la suma de todas las matrices
        $todosLosPermisos = array_unique(array_merge(
            $planificadorPermisos,
            $jefesBasePermisos,
            $jefeDigitalizacionPermisos,
            $digitalizadorPermisos,
            $altaDireccionPermisos
        ));

        // Registrar los permisos que no existan
        foreach ($todosLosPermisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Roles exactos a buscar/crear
        $roles = [
            'Abogado Planificador' => $planificadorPermisos,
            'Jefe de Digitalización' => $jefeDigitalizacionPermisos,
            'Jefe de Publicaciones' => $jefesBasePermisos,
            'Jefe de Legislacion y Asuntos Juridicos' => $jefesBasePermisos,
            'Digitalizador' => $digitalizadorPermisos,
            'Consultor Juridico' => $altaDireccionPermisos,
            'Coordinador General' => $altaDireccionPermisos,
        ];

        foreach ($roles as $roleName => $permisos) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permisos);
        }
    }
}
