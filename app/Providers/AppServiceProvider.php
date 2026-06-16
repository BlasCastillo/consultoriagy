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

        Gate::before(function ($user, $ability) {
            if ($user->hasAnyRole(['Súper Administrador', 'Super Admin'])) {
                return true;
            }
            return null;
        });
    }
}
