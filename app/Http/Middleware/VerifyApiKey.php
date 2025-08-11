<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key');
        $validApiKey = env('API_KEY');

        if (!$apiKey || $apiKey !== $validApiKey) {
            return ApiResponse::error('Clef API invalide', 401);
        }
        return $next($request);
    }
}
