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
        // Busca el POA activo del departamento al que fue remitido el ciudadano
        $poa = \App\Models\Poa::where('anio', now()->year)
            ->where('jefatura_id', $atencion->jefatura_destino_id)
            ->where('estado', '!=', 'Cerrado')
            ->first();

        if ($poa) {
            // Busca la actividad relacionada a personas atendidas
            $actividad = $poa->actividades()
                ->where(function($q) {
                    $q->where('indicador_nombre', 'ILIKE', '%personas atendidas%')
                      ->orWhere('indicador_nombre', 'ILIKE', '%beneficiadas%');
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
}
