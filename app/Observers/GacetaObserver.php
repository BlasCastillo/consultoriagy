<?php

namespace App\Observers;

use App\Models\Gaceta;
use App\Models\Jefatura;
use App\Models\Poa;
use App\Models\ActividadPoa;
use App\Models\MetaTrimestral;
use Illuminate\Support\Carbon;

class GacetaObserver
{
    /**
     * Handle the Gaceta "created" event.
     */
    public function created(Gaceta $gaceta): void
    {
        //
    }

    /**
     * Handle the Gaceta "updated" event.
     */
    public function updated(Gaceta $gaceta): void
    {
        if ($gaceta->wasChanged('estado') && $gaceta->estado === 'Publicada') {
            $jefatura = Jefatura::where('nombre', 'Jefatura de Publicaciones')->first();
            if ($jefatura) {
                $poa = Poa::where('jefatura_id', $jefatura->id)
                    ->where('anio', Carbon::now()->year)
                    ->first();
                
                if ($poa) {
                    $actividad = ActividadPoa::where('poa_id', $poa->id)
                        ->where('descripcion', 'LIKE', '%Publicar%')
                        ->first();
                        
                    if ($actividad) {
                        $trimestreActual = ceil(Carbon::now()->month / 3);
                        $meta = MetaTrimestral::where('actividad_poa_id', $actividad->id)
                            ->where('trimestre', $trimestreActual)
                            ->first();
                            
                        if ($meta) {
                            $meta->increment('ejecutada');
                            
                            activity()
                                ->performedOn($gaceta)
                                ->causedBy(auth()->user())
                                ->log('Gaceta ' . $gaceta->numero . ' publicada. Se sumó 1 a la meta trimestral de la Jefatura de Publicaciones.');
                        }
                    }
                }
            }
        }
    }

    /**
     * Handle the Gaceta "deleted" event.
     */
    public function deleted(Gaceta $gaceta): void
    {
        //
    }

    /**
     * Handle the Gaceta "restored" event.
     */
    public function restored(Gaceta $gaceta): void
    {
        //
    }

    /**
     * Handle the Gaceta "force deleted" event.
     */
    public function forceDeleted(Gaceta $gaceta): void
    {
        //
    }
}
