<?php

namespace App\Http\Controllers\v1\Api\commercial;

use App\Enums\ClientTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\commercial\CreateClientRequest;
use App\Http\Resources\CommercialParrainageCodeResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailJob;
use App\Models\Client;
use App\Models\CommercialParrainageCode;
use App\Models\Entreprise;
use App\Models\Personne;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommercialController extends Controller
{
    protected AuthService $authService;
    protected NotificationService $notificationService;

    public function __construct(AuthService $authService, NotificationService $notificationService)
    {
        $this->authService = $authService;
        $this->notificationService = $notificationService;
    }

    /**
     * Générer un code parrainage unique pour le commercial connecté
     */
    public function genererCodeParrainage()
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent générer un code parrainage.', 403);
            }

            // Vérifier s'il y a déjà un code actif
            $currentCode = CommercialParrainageCode::getCurrentCode($commercial->id);

            if ($currentCode) {
                return ApiResponse::error(
                    'Vous avez déjà un code de parrainage actif. Il expire le ' . $currentCode->date_expiration->format('d/m/Y à H:i'),
                    422,
                    [
                        'code_actuel' => $currentCode->code_parrainage,
                        'date_expiration' => $currentCode->date_expiration->format('Y-m-d H:i:s'),
                        'peut_renouveler' => $currentCode->canBeRenewed(),
                        'jours_restants' => now()->diffInDays($currentCode->date_expiration, false)
                    ]
                );
            }

            // Générer un nouveau code
            $newCode = CommercialParrainageCode::generateNewCode($commercial->id);

            // Mettre à jour le code parrainage dans la table users (pour compatibilité)
            $commercial->update(['code_parrainage_commercial' => $newCode->code_parrainage]);

            return ApiResponse::success([
                'code_parrainage' => new CommercialParrainageCodeResource($newCode)
            ], 'Code parrainage généré avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération du code parrainage: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la génération du code parrainage', 500, $e->getMessage());
        }
    }

    /**
     * Créer un compte client par un commercial
     */
    public function creerCompteClient(CreateClientRequest $request)
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent créer des comptes clients.', 403);
            }

            $validated = $request->validated();
            $photoUrl = null;
            // Gestion de l'upload de la photo
            if (isset($validated['photo'])) {
                $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads', $validated['email']);
                if (!$photoUrl) {
                    return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
                }
            }
            // Vérifier que c'est bien un client (physique ou moral)
            if ($validated['type_demandeur'] !== TypeDemandeurEnum::CLIENT->value) {
                return ApiResponse::error('Seuls les comptes clients peuvent être créés par un commercial.', 400);
            }

            DB::beginTransaction();

            // Vérifier si l'email existe déjà
            if (User::where('email', $validated['email'])->exists()) {
                return ApiResponse::error('Cet email est déjà utilisé', 409);
            }

            // Générer un mot de passe automatique
            $motDePasseGenere = User::genererMotDePasse();

            // Créer d'abord la personne
            $personne = Personne::create([
                'nom' => $validated['nom'] ?? null,
                'prenoms' => $validated['prenoms'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'profession' => $validated['profession'] ?? null,
            ]);

            // Obtenir le code parrainage actuel du commercial
            $currentParrainageCode = CommercialParrainageCode::getCurrentCode($commercial->id);

            if (!$currentParrainageCode) {
                return ApiResponse::error('Vous n\'avez pas de code de parrainage actif. Veuillez en générer un d\'abord.', 422);
            }

            // Créer l'utilisateur avec le mot de passe généré
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($motDePasseGenere),
                'contact' => $validated['contact'],
                'photo_url' => $photoUrl, // Pas de photo lors de la création par commercial
                'adresse' => $validated['adresse'],
                'est_actif' => false,
                'mot_de_passe_a_changer' => true, // Le client devra changer son mot de passe
                'personne_id' => $personne->id,
            ]);

            // Créer l'entité selon le type de client
            $client = null;
            if ($validated['type_client'] === ClientTypeEnum::PHYSIQUE->value) {
                $client = $this->authService->createClientPhysique($user, $validated);
            } else {
                $client = $this->authService->createClientMoral($user, $validated);
            }

            // Mettre à jour le client avec commercial_id et code_parrainage
            if ($client) {
                $client->update([
                    'commercial_id' => $commercial->id,
                    'code_parrainage' => $currentParrainageCode->code_parrainage,
                ]);
            }

            $user->assignRole(RoleEnum::CLIENT->value);

            // Envoyer l'email avec les informations de connexion
            dispatch(new SendEmailJob(
                $user->email,
                'Votre compte SUNU Santé a été créé',
                'emails.compte_cree_par_commercial',
                [
                    'user' => $user,
                    'mot_de_passe' => $motDePasseGenere,
                    'commercial' => $commercial,
                    'type_client' => $validated['type_client']
                ]
            ));

            // Notifier les techniciens d'un nouveau compte créé par commercial
            $this->notificationService->notifyTechniciensNouveauCompte($user, 'client');

            DB::commit();

            return ApiResponse::success([
                'client' => new UserResource($user->load('client', 'assure')),
            ], 'Compte client créé avec succès. Un email a été envoyé au client avec ses informations de connexion.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du compte client: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création du compte client', 500, $e->getMessage());
        }
    }

    /**
     * Obtenir la liste des clients parrainés par le commercial connecté
     */
    public function mesClientsParraines(Request $request)
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent voir leurs clients parrainés.', 403);
            }

            $clients = $commercial->clientsParraines()
                ->with(['user.roles', 'user.assure', 'user.personne'])
                ->get()
                ->pluck('user');

            return ApiResponse::success([
                'clients' => UserResource::collection($clients),
                'total' => $clients->count(),
            ], 'Liste des clients parrainés récupérée avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des clients parrainés: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des clients parrainés', 500, $e->getMessage());
        }
    }

    /**
     * Obtenir les statistiques du commercial connecté
     */
    public function mesStatistiques()
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent voir leurs statistiques.', 403);
            }

            // Base query pour tous les clients parrainés
            $clientsQuery = $commercial->clientsParraines()
                ->whereHas('user.roles', function ($query) {
                    $query->where('name', RoleEnum::CLIENT->value);
                });

            // Statistiques générales
            $totalClients = $clientsQuery->count();
            $clientsActifs = $clientsQuery->clone()->whereHas('user', function ($query) {
                $query->where('est_actif', true);
            })->count();
            $clientsInactifs = $clientsQuery->clone()->whereHas('user', function ($query) {
                $query->where('est_actif', false);
            })->count();

            // Répartition par type
            $clientsPhysiques = $clientsQuery->clone()
                ->where('type_client', ClientTypeEnum::PHYSIQUE->value)
                ->count();

            $clientsMoraux = $clientsQuery->clone()
                ->where('type_client', ClientTypeEnum::MORAL->value)
                ->count();

            // Répartition par mois (12 derniers mois)
            $repartitionParMois = $this->getRepartitionParMois($clientsQuery->clone());

            // Statistiques du code parrainage actuel
            $currentParrainageCode = CommercialParrainageCode::getCurrentCode($commercial->id);
            $codeParrainageStats = null;

            if ($currentParrainageCode) {
                $clientsAvecCodeActuel = $clientsQuery->clone()
                    ->where('code_parrainage', $currentParrainageCode->code_parrainage)
                    ->count();

                $codeParrainageStats = [
                    'code_actuel' => $currentParrainageCode->code_parrainage,
                    'date_debut' => $currentParrainageCode->date_debut->format('Y-m-d'),
                    'date_expiration' => $currentParrainageCode->date_expiration->format('Y-m-d'),
                    'jours_restants' => now()->diffInDays($currentParrainageCode->date_expiration, false),
                    'clients_avec_ce_code' => $clientsAvecCodeActuel
                ];
            }

            return ApiResponse::success([
                'statistiques' => [
                    // Statistiques générales
                    'total_clients' => $totalClients,
                    'clients_actifs' => $clientsActifs,
                    'clients_inactifs' => $clientsInactifs,
                    'taux_activation' => $totalClients > 0 ? round(($clientsActifs / $totalClients) * 100, 2) : 0,

                    // Répartition par type
                    'repartition_par_type' => [
                        'physiques' => $clientsPhysiques,
                        'moraux' => $clientsMoraux,
                        'pourcentage_physiques' => $totalClients > 0 ? round(($clientsPhysiques / $totalClients) * 100, 2) : 0,
                        'pourcentage_moraux' => $totalClients > 0 ? round(($clientsMoraux / $totalClients) * 100, 2) : 0
                    ],

                    // Répartition par mois (pour graphiques)
                    'repartition_par_mois' => $repartitionParMois,

                    // Statistiques du code parrainage
                    'code_parrainage_stats' => $codeParrainageStats
                ],
                // 'commercial' => new UserResource($commercial)
            ], 'Statistiques récupérées avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des statistiques', 500, $e->getMessage());
        }
    }

    /**
     * Obtenir la répartition des clients par mois (12 derniers mois)
     */
    private function getRepartitionParMois($clientsQuery)
    {
        $repartition = [];
        $maintenant = now();

        // Générer les 12 derniers mois
        for ($i = 11; $i >= 0; $i--) {
            $date = $maintenant->copy()->subMonths($i);
            $moisDebut = $date->copy()->startOfMonth();
            $moisFin = $date->copy()->endOfMonth();

            $clientsCeMois = $clientsQuery->clone()
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->count();

            $clientsActifsCeMois = $clientsQuery->clone()
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('est_actif', true)
                ->count();

            $repartition[] = [
                'mois' => $date->format('Y-m'),
                'mois_nom' => $date->format('M Y'),
                'mois_complet' => $date->format('F Y'),
                'total_clients' => $clientsCeMois,
                'clients_actifs' => $clientsActifsCeMois,
                'clients_inactifs' => $clientsCeMois - $clientsActifsCeMois
            ];
        }

        return $repartition;
    }

    /**
     * Obtenir le code de parrainage actuel du commercial
     */
    public function monCodeParrainage()
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent voir leur code parrainage.', 403);
            }

            $currentCode = CommercialParrainageCode::getCurrentCode($commercial->id);

            if (!$currentCode) {
                return ApiResponse::success([
                    'code_parrainage' => null,
                    'message' => 'Aucun code de parrainage actif. Vous pouvez en générer un nouveau.'
                ], 'Aucun code de parrainage actif');
            }

            return ApiResponse::success([
                'code_parrainage' => new CommercialParrainageCodeResource($currentCode)
            ], 'Code de parrainage actuel récupéré avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération du code parrainage: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération du code parrainage', 500, $e->getMessage());
        }
    }

    /**
     * Obtenir l'historique des codes de parrainage du commercial
     */
    public function historiqueCodesParrainage()
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent voir leur historique.', 403);
            }

            $codes = CommercialParrainageCode::getHistory($commercial->id);

            return ApiResponse::success([
                'codes' => CommercialParrainageCodeResource::collection($codes),
                'total' => $codes->count(),
                'codes_actifs' => $codes->where('est_actif', true)->count(),
                'codes_expires' => $codes->where('est_actif', true)->filter(function($code) {
                    return $code->isExpired();
                })->count()
            ], 'Historique des codes de parrainage récupéré avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de l\'historique: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération de l\'historique', 500, $e->getMessage());
        }
    }

    /**
     * Renouveler le code de parrainage (après expiration)
     */
    public function renouvelerCodeParrainage()
    {
        try {
            $commercial = Auth::user();

            // Vérifier que l'utilisateur est bien un commercial
            if (!$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                return ApiResponse::error('Accès non autorisé. Seuls les commerciaux peuvent renouveler leur code parrainage.', 403);
            }

            // Vérifier s'il y a un code expiré à renouveler
            $expiredCode = CommercialParrainageCode::where('commercial_id', $commercial->id)
                ->where('est_actif', true)
                ->where('date_expiration', '<', now())
                ->first();

            if (!$expiredCode) {
                return ApiResponse::error('Aucun code expiré à renouveler. Vous devez attendre l\'expiration de votre code actuel.', 422);
            }

            // Marquer l'ancien code comme renouvelé
            $expiredCode->update(['est_renouvele' => true]);

            // Générer un nouveau code
            $newCode = CommercialParrainageCode::generateNewCode($commercial->id);

            // Mettre à jour le code parrainage dans la table users (pour compatibilité)
            $commercial->update(['code_parrainage_commercial' => $newCode->code_parrainage]);

            return ApiResponse::success([
                'nouveau_code' => new CommercialParrainageCodeResource($newCode),
                'ancien_code' => new CommercialParrainageCodeResource($expiredCode)
            ], 'Code de parrainage renouvelé avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors du renouvellement du code parrainage: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors du renouvellement du code parrainage', 500, $e->getMessage());
        }
    }

    /**
     * Générer un code parrainage unique (méthode privée conservée pour compatibilité)
     */
    private function genererCodeUnique()
    {
        do {
            $code = 'COM' . strtoupper(Str::random(6));
        } while (User::where('code_parrainage_commercial', $code)->exists());

        return $code;
    }
}
