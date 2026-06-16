<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoInstitucion;
use App\Models\Institucion;

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
            'Proceso de Liquidación'
        ];

        foreach ($tiposBase as $tipo) {
            TipoInstitucion::firstOrCreate(['nombre' => $tipo]);
        }

        $enteAdscritoId = TipoInstitucion::where('nombre', 'Ente Adscrito')->value('id');
        Institucion::whereIn('nombre', [
            'Gobernación del estado Yaracuy',
            'Consejo Legislativo del estado Yaracuy (CLEY)',
            'Procuraduría General del estado Yaracuy',
            'Contraloría del estado Yaracuy'
        ])->update(['tipo_institucion_id' => $enteAdscritoId]);

        $fundacionId = TipoInstitucion::where('nombre', 'Fundación')->value('id');
        Institucion::where('nombre', 'LIKE', '%Fundación%')->update(['tipo_institucion_id' => $fundacionId]);

        $institutoId = TipoInstitucion::where('nombre', 'Instituto')->value('id');
        Institucion::where('nombre', 'LIKE', '%Instituto%')->update(['tipo_institucion_id' => $institutoId]);

        $servicioId = TipoInstitucion::where('nombre', 'Servicio')->value('id');
        Institucion::where('nombre', 'LIKE', '%Servicio%')->update(['tipo_institucion_id' => $servicioId]);

        $corporacionId = TipoInstitucion::where('nombre', 'Corporación')->value('id');
        Institucion::where('nombre', 'LIKE', '%Corporación%')
            ->orWhere('nombre', 'LIKE', '%Prosalud%')
            ->update(['tipo_institucion_id' => $corporacionId]);

        $empresaId = TipoInstitucion::where('nombre', 'Empresa')->value('id');
        Institucion::where('nombre', 'LIKE', '%Empresa%')
            ->orWhere('nombre', 'LIKE', '%Aguas%')
            ->update(['tipo_institucion_id' => $empresaId]);
            
        $this->command->info('Instituciones clasificadas con éxito.');
    }
}
