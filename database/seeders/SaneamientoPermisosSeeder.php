<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SaneamientoPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'read Solicitud' => 'ver solicitud',
            'create Solicitud' => 'crear solicitud',
            'update Solicitud' => 'editar solicitud',
            'delete Solicitud' => 'eliminar solicitud',
            'read Gaceta' => 'ver gacetas',
            'create Gaceta' => 'crear gaceta',
            'update Gaceta' => 'editar gaceta',
            'delete Gaceta' => 'eliminar gaceta',
        ];

        foreach ($map as $old => $new) {
            $perm = Permission::where('name', $old)->first();
            if ($perm) {
                $existingNew = Permission::where('name', $new)->first();
                if ($existingNew) {
                    foreach($perm->roles as $role) {
                        $role->givePermissionTo($new);
                    }
                    $perm->delete();
                } else {
                    $perm->update(['name' => $new]);
                }
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
