<?php

namespace App\Observers;

use App\Models\ActoAdministrativo;

class ActoAdministrativoObserver
{
    /**
     * Handle the ActoAdministrativo "created" event.
     */
    public function created($acto)
    {
        $acto->loadMissing('solicitud.gaceta');
        $solicitud = $acto->solicitud;

        // No incrementar el POA cuando un ente externo crea o edita una Solicitud en estado 'Pendiente',
        // sino únicamente cuando la gaceta asociada sea es_retroactiva == true
        if (!$solicitud || $solicitud->estado === 'Pendiente') {
            return;
        }

        $gaceta = $solicitud->gaceta;
        if (!$gaceta || !$gaceta->es_retroactiva) {
            return;
        }

        // Evitar duplicar con ArchivoController@guardarRetroactiva si ya se especificó meta o flujo de archivo
        if (request()->routeIs('archivo.retroactiva.store') || request()->hasFile('archivo_pdf_completo')) {
            return;
        }

        $tipoActo = strtolower($acto->tipo_acto ?? '');
        $anioActo = $gaceta->anio ?? ($gaceta->fecha_publicacion ? \Carbon\Carbon::parse($gaceta->fecha_publicacion)->year : now()->year);

        if (str_contains($tipoActo, 'gaceta') || str_contains($tipoActo, 'ordinaria') || str_contains($tipoActo, 'extraordinaria')) {
            if ($anioActo <= 2025) {
                $keyword = '%históricas%'; 
                $anioPoa = now()->year; 
                $trimestrePoa = ceil(now()->month / 3); 
            } else {
                $keyword = '%gacetas%';
                $anioPoa = $anioActo;
                $trimestrePoa = ceil(($gaceta && $gaceta->fecha_emision) ? \Carbon\Carbon::parse($gaceta->fecha_emision)->month / 3 : now()->month / 3);
            }
        } else {
            // Lógica para Decretos y Convenios
            $keyword = (str_contains($tipoActo, 'convenio') || str_contains($tipoActo, 'contrato')) ? '%convenios%' : '%decretos%';
            $anioPoa = now()->year;
            $trimestrePoa = ceil(now()->month / 3);
        }

        // Trigger del POA con ILIKE para PostgreSQL
        $jefaturaId = (auth()->check() && auth()->user()->jefatura_id)
            ? auth()->user()->jefatura_id
            : \App\Models\Jefatura::where('nombre', 'ILIKE', '%Archivo%')->value('id');

        if ($jefaturaId) {
            $poa = \App\Models\Poa::withoutGlobalScopes()
                ->where('anio', $anioPoa)
                ->where('jefatura_id', $jefaturaId)
                ->where('estado', '!=', 'Cerrado')
                ->first();

            if ($poa) {
                $actividad = $poa->actividades()->where('descripcion', 'ILIKE', $keyword)->first();
                if ($actividad) {
                    $meta = $actividad->metasTrimestrales()->where('trimestre', $trimestrePoa)->first();
                    if ($meta) {
                        $meta->increment('ejecutada');
                        \App\Models\Poa::checkAndClose($poa);
                    }
                }
            }
        }
    }

    /**
     * Handle the ActoAdministrativo "updated" event.
     */
    public function updated(ActoAdministrativo $actoAdministrativo): void
    {
        //
    }

    /**
     * Handle the ActoAdministrativo "deleted" event.
     */
    public function deleted(ActoAdministrativo $actoAdministrativo): void
    {
        //
    }

    /**
     * Handle the ActoAdministrativo "restored" event.
     */
    public function restored(ActoAdministrativo $actoAdministrativo): void
    {
        //
    }

    /**
     * Handle the ActoAdministrativo "force deleted" event.
     */
    public function forceDeleted(ActoAdministrativo $actoAdministrativo): void
    {
        //
    }
}
