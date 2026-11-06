<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FichaActividad;
use App\Models\Gaceta;
use App\Observers\FichaActividadObserver;
use App\Observers\GacetaObserver;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FichaActividad::observe(FichaActividadObserver::class);
        Gaceta::observe(GacetaObserver::class);
        \App\Models\ActoAdministrativo::observe(\App\Observers\ActoAdministrativoObserver::class);
        if (class_exists(\App\Models\Atencion::class)) {
            \App\Models\Atencion::observe(\App\Observers\AtencionObserver::class);
        }

        Gate::before(function ($user, $ability) {
            if ($user->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador'])) {
                return true;
            }
            return null;
        });
    }
}
