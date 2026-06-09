<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@yaracuy.gob.ve'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        
        $superadmin->assignRole('Super Administrador');

        $director = User::firstOrCreate(
            ['email' => 'director@example.com'],
            [
                'name' => 'Jefe de Digitalización',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $director->syncRoles(['Jefe de Digitalización']);

        $digitalizador = User::firstOrCreate(
            ['email' => 'digitalizador@example.com'],
            [
                'name' => 'Digitalizador',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $digitalizador->syncRoles(['Digitalizador']);

        $consultor = User::firstOrCreate(
            ['email' => 'consultor@example.com'],
            [
                'name' => 'Consultor Juridico',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $consultor->syncRoles(['Consultor Juridico']);

        $coordGral = User::firstOrCreate(
            ['email' => 'coordinador_gral@example.com'],
            [
                'name' => 'Coordinador General',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $coordGral->syncRoles(['Coordinador General']);

        $jefePub = User::firstOrCreate(
            ['email' => 'jefe_publicaciones@example.com'],
            [
                'name' => 'Jefe de Publicaciones',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $jefePub->syncRoles(['Jefe de Publicaciones']);

        $jefeLegis = User::firstOrCreate(
            ['email' => 'jefe_legislacion@example.com'],
            [
                'name' => 'Jefe de Legislacion',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $jefeLegis->syncRoles(['Jefe de Legislacion y Asuntos Juridicos']);

        $abogadoPlan = User::firstOrCreate(
            ['email' => 'planificador@example.com'],
            [
                'name' => 'Abogado Planificador',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $abogadoPlan->syncRoles(['Abogado Planificador']);

        $abogado = User::firstOrCreate(
            ['email' => 'abogado@example.com'],
            [
                'name' => 'Abogado',
                'password' => Hash::make('password'),
                'status' => true
            ]
        );
        $abogado->syncRoles(['Abogado']);
    }
}
