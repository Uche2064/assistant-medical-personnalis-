<?php

namespace App\Http\Controllers\v1\Api\prestataire;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\EmailType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\Contrat;
use App\Models\DemandeAdhesion;
use App\Models\Prestataire;
use App\Models\User;
use App\Services\DemandeAdhesionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrestataireController extends Controller
{
    protected NotificationService $notificationService;
    protected DemandeAdhesionService $demandeAdhesionService;

    public function __construct(
        NotificationService $notificationService,
        DemandeAdhesionService $demandeAdhesionService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeAdhesionService = $demandeAdhesionService;
    }

    /**
     * Valider une demande d'adhésion prestataire par un médecin contrôleur
     */
    public function validerPrestataire(int $id)
    {
        try {
            $medecinControleur = Auth::user();
            $demande = DemandeAdhesion::find($id);

            if (!$demande) {
                return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
            }

            // Vérifier que la demande est en attente
            if (!$demande->isPending()) {
                return ApiResponse::error('Cette demande a déjà été traitée', 400);
            }

            DB::beginTransaction();

            // Valider la demande via le service
            $demande = $this->demandeAdhesionService->validerDemande($demande, $medecinControleur->personnel);

            // Envoyer l'email
            dispatch(new SendEmailJob($demande->user->email, 'Demande d\'adhésion prestataire validée', EmailType::ACCEPTED->value, [
                'demande' => $demande,
                'medecin_controleur' => $medecinControleur->personnel,
            ]));

            DB::commit();

            return ApiResponse::success([
                'demande_id' => $demande,
                'statut' => $demande->statut?->value ?? $demande->statut,
                'valide_par' => $medecinControleur->personnel->nom . ' ' . ($medecinControleur->personnel->prenoms ?? ''),
            ], 'Demande d\'adhésion prestataire validée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de la demande prestataire', [
                'error' => $e->getMessage(),
                'demande_id' => $id,
                'medecin_controleur_id' => Auth::user()->personnel->id,
            ]);

            return ApiResponse::error('Erreur lors de la validation de la demande: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assigner un réseau de prestataires à un client
     */
    public function assignerReseauPrestataires(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $request->validate([
                'client_id' => 'required|exists:users,id',
                'contrat_id' => 'required|exists:contrats,id',
                'prestataires' => 'required|array',
                'prestataires.pharmacies' => 'array',
                'prestataires.centres_soins' => 'array',
                'prestataires.optiques' => 'array',
                'prestataires.laboratoires' => 'array',
                'prestataires.centres_diagnostic' => 'array',
            ]);

            $client = User::findOrFail($request->client_id);
            $contrat = Contrat::findOrFail($request->contrat_id);

            // Vérifier que le contrat appartient au client
            if ($contrat->user_id !== $client->id) {
                return ApiResponse::error('Ce contrat n\'appartient pas au client spécifié', 400);
            }

            DB::beginTransaction();

            try {
                // 1. Créer l'entrée dans la table client_contrat
                $clientContrat = ClientContrat::create([
                    'client_id' => $client->id,
                    'contrat_id' => $contrat->id,
                    'type_client' => $client->type_demandeur ?? 'physique',
                    'date_debut' => now(),
                    'date_fin' => now()->addYear(),
                    'statut' => 'ACTIF'
                ]);

                // 2. Assigner les prestataires
                $prestatairesAssignes = [];
                foreach ($request->prestataires as $type => $prestataireIds) {
                    foreach ($prestataireIds as $prestataireId) {
                        // Vérifier que le prestataire existe
                        $prestataire = Prestataire::find($prestataireId);
                        if (!$prestataire) {
                            throw new \Exception("Prestataire ID {$prestataireId} non trouvé");
                        }

                        $clientPrestataire = ClientPrestataire::create([
                            'client_contrat_id' => $clientContrat->id,
                            'prestataire_id' => $prestataireId,
                            'type_prestataire' => $type,
                            'statut' => 'ACTIF'
                        ]);

                        $prestatairesAssignes[] = [
                            'id' => $prestataire->id,
                            'nom' => $prestataire->nom,
                            'type' => $type,
                            'adresse' => $prestataire->adresse
                        ];
                    }
                }

                // 3. Notification au client
                $this->notificationService->createNotification(
                    $client->id,
                    'Réseau de prestataires assigné',
                    "Un réseau de prestataires vous a été assigné. Vous pouvez maintenant vous soigner chez ces prestataires.",
                    'reseau_assigne',
                    [
                        'client_contrat_id' => $clientContrat->id,
                        'nombre_prestataires' => count($prestatairesAssignes),
                        'type' => 'reseau_assigne'
                    ]
                );

                DB::commit();

                return ApiResponse::success([
                    'client_contrat_id' => $clientContrat->id,
                    'prestataires_assignes' => $prestatairesAssignes,
                    'message' => 'Réseau de prestataires assigné avec succès'
                ], 'Réseau de prestataires assigné avec succès');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'assignation du réseau de prestataires', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de l\'assignation du réseau: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer la liste des prestataires pour le technicien (avec recherche)
     */
    public function getPrestatairesTechnicien(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $query = Prestataire::where('statut', StatutPrestataireEnum::VALIDE->value);

            // Recherche par nom ou adresse
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%");
                });
            }

            // Filtrer par type de prestataire
            if ($request->has('type_prestataire')) {
                $query->where('type_prestataire', $request->type_prestataire);
            }

            $prestataires = $query->get()->map(function ($prestataire) {
                return [
                    'id' => $prestataire->id,
                    'nom' => $prestataire->nom,
                    'type_prestataire' => $prestataire->type_prestataire?->value ?? $prestataire->type_prestataire,
                    'adresse' => $prestataire->adresse,
                    'telephone' => $prestataire->telephone,
                    'email' => $prestataire->email,
                    'statut' => $prestataire->statut?->value ?? $prestataire->statut
                ];
            });

            return ApiResponse::success($prestataires, 'Liste des prestataires récupérée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prestataires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des prestataires: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer tous les prestataires (pour les médecins contrôleurs)
     */
    public function index(Request $request)
    {
        try {
            $query = Prestataire::query();

            // Recherche par nom ou adresse
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtrer par type de prestataire
            if ($request->has('type_prestataire')) {
                $query->where('type_prestataire', $request->type_prestataire);
            }

            // Filtrer par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            $prestataires = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

            return ApiResponse::success($prestataires, 'Liste des prestataires récupérée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prestataires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des prestataires: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher les détails d'un prestataire
     */
    public function show(int $id)
    {
        try {
            $prestataire = Prestataire::find($id);

            if (!$prestataire) {
                return ApiResponse::error('Prestataire non trouvé', 404);
            }

            return ApiResponse::success($prestataire, 'Détails du prestataire récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du prestataire', [
                'error' => $e->getMessage(),
                'prestataire_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération du prestataire: ' . $e->getMessage(), 500);
        }
    }
} 