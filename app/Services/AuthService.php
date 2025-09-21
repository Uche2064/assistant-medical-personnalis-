<?php

namespace App\Services;

use App\Enums\ClientTypeEnum;
use App\Enums\LienParenteEnum;
use App\Enums\StatutClientEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\UserResource;
use App\Models\Assure;
use App\Models\Client;
use App\Models\Entreprise;
use App\Models\InvitationEmploye;
use App\Models\LienInvitation;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new UserResource($user->load(['roles', 'assure', 'personne', 'personnel', 'prestataire'])),
        ], 'Authentification réussie');
    }

    /**
     * Crée un client physique.
     */
    public function createClientPhysique(User $user, array $validated): void
    {
        // Créer d'abord le client
        $client = Client::create([
            'user_id' => $user->id,
            'type_client' => $validated['type_client'],
        ]);

        // Puis créer l'assuré lié au client
        Assure::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'est_principal' => true,
            'lien_parente' => LienParenteEnum::PRINCIPAL,
            'assure_principal_id'=> null
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

    /**
     * Vérifie si le compte est bloqué (temporairement ou définitivement).
     */
    public function checkAccountStatus($user)
    {
        $now = Carbon::now();

        if ($user->permanently_blocked) {
            throw new HttpException(
                403,
                "Compte bloqué définitivement. Contactez le support."
            );
        }

        if ($user->lock_until && $user->lock_until->isFuture()) {
            throw new HttpException(
                403,
                "Compte bloqué jusqu’à " . $user->lock_until->format('d/m/Y H:i')
            );
        }
    }

    /**
     * Gestion d’un échec de connexion.
     */
    public function handleFailedAttempt($user)
    {
        $now = Carbon::now();
        $user->failed_attempts += 1;

        if ($user->phase === 1) { // Phase 1 → 5 essais
            if ($user->failed_attempts >= 5) {
                $user->lock_until = $now->copy()->addHour(); // Bloqué 1h
                $user->failed_attempts = 0; // reset pour la phase suivante
                $user->phase = 2;
            }
        } elseif ($user->phase === 2) { // Phase 2 → 3 essais
            if ($user->failed_attempts >= 3) {
                $user->permanently_blocked = true; // Blocage définitif
            }
        }

        $user->save();
    }

    /**
     * Reset des tentatives après un succès.
     */
    public function resetAttempts($user)
    {
        $user->failed_attempts = 0;
        $user->lock_until = null;
        $user->phase = 1; // On repart toujours à la phase 1 après un succès
        $user->save();
    }

}
