<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Enums\TypePersonnelEnum;
use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MedecinControleurMiddleware
{
    
    public function handle(Request $request, Closure $next): Response
    {
       if (!Auth::check() || 
            !Auth::user()->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)) {
            return ApiResponse::error('Accès réservé au médecin contrôleur.', 401, 'unauthorized');
        }

        return $next($request);
    }
}