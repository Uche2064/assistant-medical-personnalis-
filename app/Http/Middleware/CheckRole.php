<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return ApiResponse::error('Non authentifié.', 401);
        }

        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();
        
        // Vérifier si l'utilisateur a au moins un des rôles requis
        $hasRequiredRole = false;
        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            return ApiResponse::error('L\'utilisateur n\'est pas autorisé à accéder à cette ressource.', 403);
        }

        return $next($request);
    }
} 