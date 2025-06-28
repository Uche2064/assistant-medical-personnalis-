<?php

namespace App\Http\Middleware;

use App\Enums\LienEnum;
use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Models\Assure;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AssurePrincipalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $assurePrincipal = Assure::where('user_id', $user->id)
        ->where('lien_parente', LienEnum::PRINCIPAL)
        ->first();

        if (!$assurePrincipal) {
            return ApiResponse::error('Accès authorisé qu\'au assurés principaux', 401);
        }

        return $next($request);
}
}
