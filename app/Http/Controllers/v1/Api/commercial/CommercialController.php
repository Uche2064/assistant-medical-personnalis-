<?php

namespace App\Http\Controllers\v1\Api\commercial;

use App\Enums\ClientTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\commercial\CreateClientRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailJob;
use App\Models\Client;
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

            // Générer un code parrainage unique
            $codeParrainage = $this->genererCodeUnique();

            // Mettre à jour le code parrainage du commercial
            $commercial->update(['code_parrainage_commercial' => $codeParrainage]);

            return ApiResponse::success([
                'code_parrainage' => $codeParrainage,
                // 'commercial' => new UserResource($commercial)
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
                $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads/users/' . $validated['email']);
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

            // Créer l'utilisateur avec le mot de passe généré
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($motDePasseGenere),
                'contact' => $validated['contact'],
                'photo_url' => $photoUrl, // Pas de photo lors de la création par commercial
                'adresse' => $validated['adresse'],
                'mot_de_passe_a_changer' => true, // Le client devra changer son mot de passe
                'personne_id' => $personne->id,
                'commercial_id' => $commercial->id,
                'compte_cree_par_commercial' => true,
                'code_parrainage' => $commercial->code_parrainage_commercial
            ]);

            // Créer l'entité selon le type de client
            if ($validated['type_client'] === ClientTypeEnum::PHYSIQUE->value) {
                $this->authService->createClientPhysique($user, $validated);
            } else {
                $this->authService->createClientMoral($user, $validated);
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
                ->with(['client', 'assure', 'personne'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', RoleEnum::CLIENT->value);
                })
                ->get();

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

            $totalClients = $commercial->clientsParraines()
                ->whereHas('roles', function ($query) {
                    $query->where('name', RoleEnum::CLIENT->value);
                })
                ->count();

            $clientsActifs = $commercial->clientsParraines()
                ->whereHas('roles', function ($query) {
                    $query->where('name', RoleEnum::CLIENT->value);
                })
                ->where('est_actif', true)
                ->count();

            $clientsPhysiques = $commercial->clientsParraines()
                ->whereHas('roles', function ($query) {
                    $query->where('name', RoleEnum::CLIENT->value);
                })
                ->whereHas('client', function ($query) {
                    $query->where('type_client', ClientTypeEnum::PHYSIQUE->value);
                })
                ->count();

            $clientsMoraux = $commercial->clientsParraines()
                ->whereHas('roles', function ($query) {
                    $query->where('name', RoleEnum::CLIENT->value);
                })
                ->whereHas('client', function ($query) {
                    $query->where('type_client', ClientTypeEnum::MORAL->value);
                })
                ->count();

            return ApiResponse::success([
                'statistiques' => [
                    'total_clients' => $totalClients,
                    'clients_actifs' => $clientsActifs,
                    'clients_physiques' => $clientsPhysiques,
                    'clients_moraux' => $clientsMoraux,
                    'taux_activation' => $totalClients > 0 ? round(($clientsActifs / $totalClients) * 100, 2) : 0
                ],
                'commercial' => new UserResource($commercial)
            ], 'Statistiques récupérées avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des statistiques', 500, $e->getMessage());
        }
    }

    /**
     * Générer un code parrainage unique
     */
    private function genererCodeUnique()
    {
        do {
            $code = 'COM' . strtoupper(Str::random(6));
        } while (User::where('code_parrainage_commercial', $code)->exists());

        return $code;
    }
}
