<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GestionnaireMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->hasRole(RoleEnum::GESTIONNAIRE->value)) {
            return ApiResponse::error('Accès non autorisé.', 403, 'unauthorized');
        }
        return $next($request);
    }
}
