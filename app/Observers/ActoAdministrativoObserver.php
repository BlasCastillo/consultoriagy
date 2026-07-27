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
        $tipoActo = strtolower($acto->tipo_acto ?? '');
        $anioActo = now()->year; // Por defecto

        // Interceptar Gacetas y buscar su año real en la BD
        if (str_contains($tipoActo, 'gaceta') || str_contains($tipoActo, 'ordinaria') || str_contains($tipoActo, 'extraordinaria')) {
            $gaceta = \App\Models\Gaceta::where('numero', $acto->numero)->orderBy('id', 'desc')->first();

            if ($gaceta) {
                $anioActo = $gaceta->anio ?? \Carbon\Carbon::parse($gaceta->fecha_emision)->year;
            }

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
        if (auth()->check() && auth()->user()->jefatura_id) {
            $poa = \App\Models\Poa::where('anio', $anioPoa)
                ->where('jefatura_id', auth()->user()->jefatura_id)
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
