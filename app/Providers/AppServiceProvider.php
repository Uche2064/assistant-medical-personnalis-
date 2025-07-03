<?php

namespace App\Providers;

use App\Models\Contrat;
use App\Models\DemandeAdhesion;
use App\Observers\ContratObserver;
use App\Observers\DemandeAdhesionObserver;
use Illuminate\Support\ServiceProvider;

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
    public function boot()
    {
        DemandeAdhesion::observe(DemandeAdhesionObserver::class);
        Contrat::observe(ContratObserver::class);
    }
}
