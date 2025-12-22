<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Exclure la route /v1/files de la vérification de clé API (accessible depuis Filament)
        if ($request->is('api/v1/files/*')) {
            return $next($request);
        }

        $apiKey = $request->header('x-api-key');
        // Utiliser config() au lieu de env() pour supporter le cache de configuration
        $validApiKey = config('app.api_key');

        // Log::info('apiKey: ' . $apiKey);
        // Log::info('validApiKey: : ' . $validApiKey);

        if (!$apiKey || !$validApiKey || $apiKey !== $validApiKey) {
            return ApiResponse::error('Clef API invalide', 401);
        }
        return $next($request);
    }
}
