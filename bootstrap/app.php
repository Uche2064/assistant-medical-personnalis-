<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AssurePrincipalMiddleware;
use App\Http\Middleware\GestionnaireMiddleware;
use App\Http\Middleware\MedecinControleurMiddleware;
use App\Http\Middleware\TechnicienMiddleware;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verifyApiKey' => VerifyApiKey::class,
            'admin' => AdminMiddleware::class,
            'gestionnaire' => GestionnaireMiddleware::class,
            'medecin_controleur' => MedecinControleurMiddleware::class,
            'assure_principal' => AssurePrincipalMiddleware::class,
            'technicien' => TechnicienMiddleware::class,
            'role' => RoleMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
