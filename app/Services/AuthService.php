<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Génère un token JWT pour un utilisateur donné.
     */
    public function generateToken(User $user): string
    {
        return JWTAuth::claims($user->getJWTCustomClaims())->fromUser($user);
    }

    /**
     * Répond avec les données du token JWT.
     */
    public function respondWithToken(string $token, User $user): \Illuminate\Http\JsonResponse
    {
        return ApiResponse::success([
            'access_token' => $token,
            'user' => $user,
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 'Authentification réussie');
    }
}
