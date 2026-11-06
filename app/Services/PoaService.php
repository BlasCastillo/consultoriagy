<?php

namespace App\Services;

use App\Models\Poa;
use Carbon\Carbon;

class PoaService
{
    public static function registrarEjecucion($tipoAccion, $jefaturaId, $fechaReferencia = null)
    {
        $fecha = $fechaReferencia ? Carbon::parse($fechaReferencia) : now();
        $anio = $fecha->year;
        $trimestre = ceil($fecha->month / 3);

        $keyword = '';
        switch ($tipoAccion) {
            case 'gaceta_corriente':
                $keyword = '%gacetas%';
                break;
            case 'contrato':
                $keyword = '%contratos%';
                break;
            case 'atencion':
                $keyword = '%asesorar%';
                break;
            case 'proyecto':
                $keyword = '%proyectos normativos%';
                break;
            default:
                return false;
        }

        // Caza del POA sin auth() ni global scope para evitar fallos entre jefaturas
        $poa = Poa::withoutGlobalScopes()
            ->where('anio', $anio)
            ->where('jefatura_id', $jefaturaId)
            ->whereIn('estado', ['Ejecucion', 'Aprobado'])
            ->first();

        if ($poa) {
            $actividad = $poa->actividades()->where('descripcion', 'ILIKE', $keyword)->first();
            
            if ($actividad) {
                $meta = $actividad->metasTrimestrales()->where('trimestre', $trimestre)->first();
                
                if ($meta) {
                    $meta->increment('ejecutada');
                    Poa::checkAndClose($poa);
                    return true;
                }
            }
        }
        
        return false;
    }
}
