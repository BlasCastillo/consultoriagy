<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Hash;

class GacetasTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $digitalizadorRole = Role::firstOrCreate(['name' => 'Digitalizador']);
        $institucionRole = Role::firstOrCreate(['name' => 'Institucion']);

        // 2. Instituciones
        $gobernacion = Institution::firstOrCreate(
            ['name' => 'Gobernación del Estado Yaracuy'],
            ['type' => 'ente_adscrito', 'status' => 'active']
        );
        $inajudey = Institution::firstOrCreate(
            ['name' => 'INAJUDEY'],
            ['type' => 'ente_adscrito', 'status' => 'active']
        );
        $fundey = Institution::firstOrCreate(
            ['name' => 'FUNDEY'],
            ['type' => 'ente_adscrito', 'status' => 'active']
        );
        $prosalud = Institution::firstOrCreate(
            ['name' => 'PROSALUD'],
            ['type' => 'ente_adscrito', 'status' => 'active']
        );

        // 3. Usuarios
        // Digitalizador
        $digitalizador = User::firstOrCreate(
            ['email' => 'digitalizador@example.com'],
            [
                'name' => 'Digitalizador Principal',
                'password' => Hash::make('password'),
                'institution_id' => $gobernacion->id
            ]
        );
        $digitalizador->assignRole($digitalizadorRole);

        // Representantes Institucionales
        $repInajudey = User::firstOrCreate(
            ['email' => 'inajudey@example.com'],
            [
                'name' => 'Rep. INAJUDEY',
                'password' => Hash::make('password'),
                'institution_id' => $inajudey->id
            ]
        );
        $repInajudey->assignRole($institucionRole);

        $repFundey = User::firstOrCreate(
            ['email' => 'fundey@example.com'],
            [
                'name' => 'Rep. FUNDEY',
                'password' => Hash::make('password'),
                'institution_id' => $fundey->id
            ]
        );
        $repFundey->assignRole($institucionRole);

        $repProsalud = User::firstOrCreate(
            ['email' => 'prosalud@example.com'],
            [
                'name' => 'Rep. PROSALUD',
                'password' => Hash::make('password'),
                'institution_id' => $prosalud->id
            ]
        );
        $repProsalud->assignRole($institucionRole);
    }
}
