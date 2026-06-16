<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RestaurarMenusSeeder extends Seeder
{
    public function run(): void
    {
        $permisosExtraidos = [
            'read Solicitud',
            'create Solicitud',
            'ver solicitud',
            'crear solicitud',
            'ver gacetas',
            'read Poa',
            'ver poa'
        ];

        foreach ($permisosExtraidos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        $digitalizador = Role::where('name', 'Digitalizador')->first();
        if ($digitalizador) {
            $digitalizador->givePermissionTo(['ver gacetas', 'read Solicitud', 'ver solicitud']);
        }

        $jefePub = Role::where('name', 'Jefe de Publicaciones')->first();
        if ($jefePub) {
            $jefePub->givePermissionTo(['read Poa', 'ver poa']);
        }

        $analistaPoa = Role::where('name', 'Analista POA')->first();
        if ($analistaPoa) {
            $analistaPoa->givePermissionTo(['read Poa', 'ver poa']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Menús restaurados con éxito.');
    }
}
