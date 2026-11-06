<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\TipoInstitucion;
use Illuminate\Database\Seeder;

class ClasificarInstitucionesSeeder extends Seeder
{
    public function run(): void
    {
        $tiposBase = [
            'Ente Adscrito',
            'Fundación',
            'Instituto',
            'Servicio',
            'Corporación',
            'Empresa',
            'Proceso de Liquidación',
        ];

        foreach ($tiposBase as $tipo) {
            TipoInstitucion::firstOrCreate(['nombre' => $tipo]);
        }

        $tipo = fn (string $nombre): ?int => TipoInstitucion::where('nombre', $nombre)->value('id');

        // Entes adscritos (en la base real el CLEY quedó sin clasificar).
        Institucion::whereIn('nombre', [
            'Gobernación del estado Yaracuy',
            'Procuraduría General del estado Yaracuy',
            'Contraloría del estado Yaracuy',
        ])->update(['tipo_institucion_id' => $tipo('Ente Adscrito')]);

        Institucion::where('nombre', 'LIKE', '%Fundación%')
            ->update(['tipo_institucion_id' => $tipo('Fundación')]);

        Institucion::where('nombre', 'LIKE', '%Instituto%')
            ->update(['tipo_institucion_id' => $tipo('Instituto')]);

        Institucion::where('nombre', 'LIKE', '%Servicio%')
            ->update(['tipo_institucion_id' => $tipo('Servicio')]);

        // Corporaciones: la segunda contiene "Instituto" en el nombre, por eso
        // se reclasifica explícitamente después del bloque de institutos.
        Institucion::whereIn('nombre', [
            'Instituto Autónomo de la Salud  del Estado Yaracuy',
            'Corporación Yaracuyana de Turismo',
        ])->update(['tipo_institucion_id' => $tipo('Corporación')]);

        Institucion::where('nombre', 'LIKE', '%Empresa%')
            ->orWhere('nombre', 'Aguas de Yaracuy C.A.')
            ->update(['tipo_institucion_id' => $tipo('Empresa')]);
    }
}
