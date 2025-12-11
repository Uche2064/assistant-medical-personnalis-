<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exclure les routes d'authentification, la page de changement de mot de passe et les pages de réinitialisation
        if ($request->routeIs('filament.admin.auth.*') ||
            $request->routeIs('filament.admin.pages.change-password') ||
            $request->routeIs('filament.admin.pages.forgot-password') ||
            $request->routeIs('filament.admin.pages.verify-otp') ||
            $request->routeIs('filament.admin.pages.reset-password')) {
            return $next($request);
        }

        $user = Auth::guard('web')->user();

        // Si l'utilisateur est connecté et doit changer son mot de passe, rediriger
        if ($user && $user->mot_de_passe_a_changer) {
            return redirect()->route('filament.admin.pages.change-password');
        }

        return $next($request);
    }
}

