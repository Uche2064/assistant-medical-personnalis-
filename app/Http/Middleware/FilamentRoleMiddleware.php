<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilamentRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exclure les routes d'authentification et les pages de réinitialisation de mot de passe
        if ($request->routeIs('filament.admin.auth.*') || 
            $request->routeIs('filament.admin.pages.forgot-password') ||
            $request->routeIs('filament.admin.pages.verify-otp') ||
            $request->routeIs('filament.admin.pages.reset-password')) {
            return $next($request);
        }

        $user = auth('web')->user();

        // Si pas d'utilisateur, laisser Filament gérer la redirection
        if (!$user) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a un des rôles internes
        $internalRoles = RoleEnum::getInternalRoles();
        $hasInternalRole = $user->roles()->whereIn('name', $internalRoles)->exists();

        if (!$hasInternalRole) {
            abort(403, 'Accès refusé. Vous devez avoir un rôle interne pour accéder à cette section.');
        }

        return $next($request);
    }
}

