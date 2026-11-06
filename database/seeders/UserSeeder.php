<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Jefatura;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Usuarios demo existentes en la base real (sgc_db).
     *
     * @var array<int, array<string, mixed>>
     */
    private array $users = [
        [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'status' => false,
            'institucion' => 'Corporación Yaracuyana de Turismo',
            'jefatura' => null,
            'roles' => [],
        ],
        [
            'email' => 'digitalizador@example.com',
            'name' => 'Digitalizador Principal',
            'status' => true,
            'institucion' => null,
            'jefatura' => 'Digitalizador',
            'roles' => ['Digitalizador'],
        ],
        [
            'email' => 'inajudey@example.com',
            'name' => 'Rep. INAJUDEY',
            'status' => true,
            'institucion' => 'Instituto Autónomo de la Juventud del Edo. Yaracuy',
            'jefatura' => null,
            'roles' => ['Representante Institucional'],
        ],
        [
            'email' => 'fundey@example.com',
            'name' => 'Rep. FUNDEY',
            'status' => true,
            'institucion' => 'Instituto Autónomo del Deporte del Edo. Yaracuy',
            'jefatura' => null,
            'roles' => ['Representante Institucional'],
        ],
        [
            'email' => 'prosalud@example.com',
            'name' => 'Rep. PROSALUD',
            'status' => true,
            'institucion' => 'Instituto Autónomo de la Salud  del Estado Yaracuy',
            'jefatura' => null,
            'roles' => ['Representante Institucional'],
        ],
        [
            'email' => 'admin@example.com',
            'name' => 'Super Admin User',
            'status' => true,
            'institucion' => null,
            'jefatura' => null,
            'roles' => ['Super Admin'],
        ],
        [
            'email' => 'director@example.com',
            'name' => 'Director de Área',
            'status' => false,
            'institucion' => null,
            'jefatura' => null,
            'roles' => [],
        ],
        [
            'email' => 'usuario@example.com',
            'name' => 'Usuario Común',
            'status' => false,
            'institucion' => null,
            'jefatura' => null,
            'roles' => [],
        ],
        [
            'email' => 'superadmin@yaracuy.gob.ve',
            'name' => 'Super Administrador',
            'status' => true,
            'institucion' => null,
            'jefatura' => null,
            'roles' => ['Super Admin'],
        ],
        [
            'email' => 'jefed@example.com',
            'name' => 'Jefe DIgitalizacion',
            'status' => true,
            'institucion' => null,
            'jefatura' => 'Coordinación de Publicaciones Oficiales',
            'roles' => ['Coordinador de Publicaciones Oficiales'],
        ],
        [
            'email' => 'jefel@example.com',
            'name' => 'JEFE DE LEGISLACION',
            'status' => true,
            'institucion' => null,
            'jefatura' => 'Jefatura Legislación',
            'roles' => [],
        ],
        [
            'email' => 'digitalizador2@example.com',
            'name' => 'Digitalizador 2',
            'status' => true,
            'institucion' => null,
            'jefatura' => 'Digitalizador',
            'roles' => ['Digitalizador'],
        ],
        [
            'email' => 'jefea@example.com',
            'name' => 'Jefe de Archivo',
            'status' => true,
            'institucion' => null,
            'jefatura' => 'Coordinación de Archivo',
            'roles' => ['Coordinador de Archivo'],
        ],
        [
            'email' => 'jefescp@example.com',
            'name' => 'Jefe de Seguimiento y Control de Políticas Públicas',
            'status' => true,
            'institucion' => null,
            'jefatura' => 'Coordinación de Seguimiento y Control de Políticas Públicas',
            'roles' => ['Coordinador de Seguimiento y Control de Políticas Públicas'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => $data['status'],
                ]
            );

            $user->forceFill([
                'name' => $data['name'],
                'status' => $data['status'],
                'institucion_id' => $data['institucion']
                    ? Institucion::where('nombre', $data['institucion'])->value('id')
                    : null,
                'jefatura_id' => $data['jefatura']
                    ? Jefatura::where('nombre', $data['jefatura'])->value('id')
                    : null,
            ])->save();

            $user->syncRoles($data['roles']);
        }
    }
}
