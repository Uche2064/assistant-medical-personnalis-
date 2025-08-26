<?php

namespace App\Providers;

use App\Models\DemandeAdhesion;
use App\Observers\DemandeAdhesionObserver;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
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
        
        // DemandeAdhesion::observe(DemandeAdhesionObserver::class);
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            Log::info(env('FRONTEND_URL') . "/reset-password?token=$token&email=" . urlencode($notifiable->email));
            return env('FRONTEND_URL') . "/auth/reset-password?token=$token&email=" . urlencode($notifiable->email);
        });
    }
}
