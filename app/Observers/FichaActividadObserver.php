<?php

namespace App\Observers;

use App\Models\FichaActividad;

class FichaActividadObserver
{
    /**
     * Handle the FichaActividad "created" event.
     */
    public function created(FichaActividad $fichaActividad): void
    {
        $meta = $fichaActividad->metaTrimestral;
        if ($meta) {
            $meta->increment('ejecutada');
            
            $userName = auth()->user() ? auth()->user()->name : 'Sistema';
            activity()
                ->performedOn($fichaActividad)
                ->causedBy(auth()->user())
                ->log("El usuario {$userName} registró una nueva actividad y sumó 1 a la meta trimestral.");
        }
    }

    /**
     * Handle the FichaActividad "updated" event.
     */
    public function updated(FichaActividad $fichaActividad): void
    {
        //
    }

    /**
     * Handle the FichaActividad "deleted" event.
     */
    public function deleted(FichaActividad $fichaActividad): void
    {
        //
    }

    /**
     * Handle the FichaActividad "restored" event.
     */
    public function restored(FichaActividad $fichaActividad): void
    {
        //
    }

    /**
     * Handle the FichaActividad "force deleted" event.
     */
    public function forceDeleted(FichaActividad $fichaActividad): void
    {
        //
    }
}
