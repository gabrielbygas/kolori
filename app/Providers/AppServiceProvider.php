<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Vite;
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
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // modify by claude
        // Inertia consomme les Resources comme props de page, pas comme réponses
        // d'API JSON:API — l'enveloppe "data" par défaut n'a pas de sens ici.
        JsonResource::withoutWrapping();
    }
}
