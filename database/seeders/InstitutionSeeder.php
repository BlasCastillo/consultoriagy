<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consultoria = \App\Models\Institution::firstOrCreate(
            ['name' => 'Consultoría Jurídica del Estado Yaracuy'],
            [
                'type' => 'consultoria',
                'status' => 'active',
            ]
        );

        // Migramos a los usuarios actuales que no tengan institución
        \App\Models\User::whereNull('institution_id')->update(['institution_id' => $consultoria->id]);
    }
}
