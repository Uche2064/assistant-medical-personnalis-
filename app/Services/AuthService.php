<?php

namespace App\Services;

use App\Enums\StatutClientEnum;
use App\Enums\TypeClientEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\UserResource;
use App\Models\Client;
use App\Models\Entreprise;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
    public function respondWithToken(string $token, User $user): JsonResponse
    {
        return ApiResponse::success([
            'access_token' => $token,
            'user' => new UserResource($user->load(['roles', 'client', 'entreprise', 'assure', 'personnel', 'prestataire'])),
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 'Authentification réussie');
    }

    /**
     * Crée un client physique.
     */
    public function createClientPhysique(User $user, array $validated): void
    {
        Client::create([
            'user_id' => $user->id,
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'] ?? null,
            'date_naissance' => $validated['date_naissance'],
            'sexe' => $validated['sexe'],
            'profession' => $validated['profession'] ?? null,
            'type_client' => TypeClientEnum::PHYSIQUE->value,
            'statut' => StatutClientEnum::ASSURE->value,
            'code_parrainage' => $validated['code_parrainage'] ?? null,
        ]);
    }

    
    /**
     * Créer une entreprise
     */
    public function createEntreprise(User $user, array $validated): void
    {
        Entreprise::create([
            'user_id' => $user->id,
            'raison_sociale' => $validated['raison_sociale'],
        ]);
    }

        /**
     * Créer un prestataire de soins
     */
    public function createPrestataire(User $user, array $validated): void
    {
        $typePrestataire = match ($validated['type_demandeur']) {
            TypeDemandeurEnum::CENTRE_DE_SOINS->value => TypePrestataireEnum::CENTRE_DE_SOINS,
            TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value => TypePrestataireEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            TypeDemandeurEnum::PHARMACIE->value => TypePrestataireEnum::PHARMACIE,
            TypeDemandeurEnum::OPTIQUE->value => TypePrestataireEnum::OPTIQUE,
            default => TypePrestataireEnum::CENTRE_DE_SOINS, // Fallback
        };

        Prestataire::create([
            'user_id' => $user->id,
            'type_prestataire' => $typePrestataire,
            'raison_sociale' => $validated['raison_sociale'],
        ]);
    }

}
