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
    }
}
