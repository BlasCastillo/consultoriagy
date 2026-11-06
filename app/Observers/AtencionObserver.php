<?php

namespace App\Observers;

class AtencionObserver
{
    /**
     * Handle the Atencion "created" event.
     *
     * @param  mixed  $atencion
     * @return void
     */
    public function created($atencion): void
    {
        if (!$atencion->jefatura_destino_id) {
            return;
        }

        // Busca el POA activo del departamento destino (sin filtro de sesión del usuario que registra la atención)
        $poa = \App\Models\Poa::withoutGlobalScopes()
            ->where('anio', now()->year)
            ->where('jefatura_id', $atencion->jefatura_destino_id)
            ->where('estado', '!=', 'Cerrado')
            ->first();

        // Si la jefatura destino es subordinada y no tiene POA directo, buscar en su jefatura superior
        if (!$poa) {
            $jefaturaDestino = \App\Models\Jefatura::find($atencion->jefatura_destino_id);
            if ($jefaturaDestino && $jefaturaDestino->parent_id) {
                $poa = \App\Models\Poa::withoutGlobalScopes()
                    ->where('anio', now()->year)
                    ->where('jefatura_id', $jefaturaDestino->parent_id)
                    ->where('estado', '!=', 'Cerrado')
                    ->first();
            }
        }

        if (!$poa) {
            return;
        }

        // Busca la actividad relacionada a personas atendidas o asesoradas
        $actividad = $poa->actividades()
            ->where(function ($q) {
                $q->where('indicador_nombre', 'ILIKE', '%personas atendidas%')
                  ->orWhere('indicador_nombre', 'ILIKE', '%beneficiadas%')
                  ->orWhere('descripcion', 'ILIKE', '%asesorar%')
                  ->orWhere('descripcion', 'ILIKE', '%atención%')
                  ->orWhere('descripcion', 'ILIKE', '%consultor%');
            })->first();

        if ($actividad) {
            $meta = $actividad->metasTrimestrales()->where('trimestre', ceil(now()->month / 3))->first();
            if ($meta) {
                $meta->increment('ejecutada');
                \App\Models\Poa::checkAndClose($poa);
            }
        }
    }
}
