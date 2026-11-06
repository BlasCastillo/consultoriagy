<?php

namespace Database\Seeders;

use App\Models\Jefatura;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JefaturaSeeder extends Seeder
{
    /**
     * Unidades organizativas vigentes en la base real (sgc_db).
     *
     * Se mantienen las columnas modernas (nivel / parent_id) y las heredadas
     * (nivel_jerarquico / dependencia_id) para que un migrate:fresh + db:seed
     * reproduzca exactamente la jerarquía actual.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $jefaturas = [
        [
            'nombre' => 'Jefatura Legislación',
            'nivel' => 5,
            'nivel_jerarquico' => null,
            'parent' => 'Coordinación de Legislación y Asuntos Jurídicos',
            'dependencia' => null,
        ],
        [
            'nombre' => 'Coordinación General',
            'nivel' => 2,
            'nivel_jerarquico' => 'Coordinación',
            'parent' => 'Dirección de Consultoría Jurídica',
            'dependencia' => 'Digitalizador',
        ],
        [
            'nombre' => 'Coordinación de Archivo',
            'nivel' => 3,
            'nivel_jerarquico' => null,
            'parent' => 'Coordinación General',
            'dependencia' => null,
        ],
        [
            'nombre' => 'Coordinación de Legislación y Asuntos Jurídicos',
            'nivel' => 4,
            'nivel_jerarquico' => 'Jefatura',
            'parent' => 'Coordinación General',
            'dependencia' => 'Coordinación General',
        ],
        [
            'nombre' => 'Digitalizador',
            'nivel' => 6,
            'nivel_jerarquico' => 'Dirección Superior',
            'parent' => 'Coordinación de Publicaciones Oficiales',
            'dependencia' => null,
        ],
        [
            'nombre' => 'Dirección de Consultoría Jurídica',
            'nivel' => 1,
            'nivel_jerarquico' => 'Coordinación',
            'parent' => null,
            'dependencia' => 'Coordinación General',
        ],
        [
            'nombre' => 'Coordinación de Seguimiento y Control de Políticas Públicas',
            'nivel' => 3,
            'nivel_jerarquico' => 'Coordinación',
            'parent' => 'Coordinación General',
            'dependencia' => 'Coordinación General',
        ],
        [
            'nombre' => 'Coordinación de Publicaciones Oficiales',
            'nivel' => 4,
            'nivel_jerarquico' => 'Coordinación',
            'parent' => 'Coordinación General',
            'dependencia' => 'Coordinación General',
        ],
        [
            'nombre' => 'Jefatura de Asuntos Jurídicos',
            'nivel' => 5,
            'nivel_jerarquico' => 'Jefatura',
            'parent' => 'Coordinación de Legislación y Asuntos Jurídicos',
            'dependencia' => 'Coordinación General',
        ],
        [
            'nombre' => 'Coordinación de Atención al Ciudadano',
            'nivel' => 3,
            'nivel_jerarquico' => null,
            'parent' => 'Coordinación General',
            'dependencia' => null,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primera pasada: crear todas las unidades sin dependencias.
        foreach ($this->jefaturas as $data) {
            Jefatura::firstOrCreate(['nombre' => $data['nombre']], [
                'nivel' => $data['nivel'],
                'parent_id' => null,
            ]);
        }

        // Segunda pasada: resolver relaciones por nombre.
        foreach ($this->jefaturas as $data) {
            $jefatura = Jefatura::where('nombre', $data['nombre'])->first();

            if (! $jefatura) {
                continue;
            }

            $jefatura->forceFill([
                'nivel' => $data['nivel'],
                'parent_id' => $data['parent']
                    ? Jefatura::where('nombre', $data['parent'])->value('id')
                    : null,
            ])->save();

            // Columnas heredadas que aún existen en el esquema real.
            DB::table('jefaturas')->where('id', $jefatura->id)->update([
                'nivel_jerarquico' => $data['nivel_jerarquico'],
                'dependencia_id' => $data['dependencia']
                    ? Jefatura::where('nombre', $data['dependencia'])->value('id')
                    : null,
            ]);
        }
    }
}
