<?php

namespace App\Http\Controllers\v1\Api\prestataire;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\EmailType;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\demande_adhesion\StoreDemandeAdhesionRequest;
use App\Jobs\SendEmailJob;
use App\Models\Assure;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\Contrat;
use App\Models\DemandeAdhesion;
use App\Models\Prestataire;
use App\Models\User;
use App\Services\DemandeAdhesionService;
use App\Services\DemandeValidatorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrestataireController extends Controller
{
    protected NotificationService $notificationService;
    protected DemandeValidatorService $demandeValidatorService;
    protected DemandeAdhesionService $demandeAdhesionService;

    public function __construct(
        NotificationService $notificationService,
        DemandeValidatorService $demandeValidatorService,
        DemandeAdhesionService $demandeAdhesionService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeAdhesionService = $demandeAdhesionService;
    }

        /**
     * Soumission d'une demande d'adhésion pour une personne physique
     */
    public function storeDemande(StoreDemandeAdhesionRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $typeDemandeur = $data['type_demandeur'];


        Log::info('Demande d\'adhésion soumise', ['data' => $data]);

        // Vérifier si l'utilisateur a déjà une demande en cours ou validée (optionnel)
        if ($this->demandeValidatorService->hasPendingDemande($data)) {
            return ApiResponse::error('Vous avez déjà une demande d\'adhésion en cours de traitement. Veuillez attendre la réponse.', 400);
        }
        if ($this->demandeValidatorService->hasValidatedDemande($data)) {
            return ApiResponse::error('Vous avez déjà une demande d\'adhésion validée. Vous ne pouvez plus soumettre une nouvelle demande.', 400);
        }

        DB::beginTransaction();
        try {
           if($typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value || $typeDemandeur === TypeDemandeurEnum::ENTREPRISE->value){
            $demande = $this->demandeValidatorService->createDemandeAdhesionPhysique($data, $user);
           }else{
            $demande = $this->demandeValidatorService->createDemandeAdhesionPrestataire($data, $user);
           }

            // Notifier selon le type de demandeur via le service
            $this->demandeAdhesionService->notifyByDemandeurType($demande, $typeDemandeur);

            DB::commit();
            return ApiResponse::success(null, 'Demande d\'adhésion soumise avec succès.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la demande d\'adhésion : ' . $e->getMessage(), 500);
        }
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
            $demande = $this->demandeAdhesionService->validerDemande($demande, $medecinControleur);
            $prestataire = Prestataire::where('user_id', $demande->user_id)->where('statut', StatutPrestataireEnum::ACTIF->value);

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


    public function getAssure(Request $request) {

        $query = Assure::query();

        if ($request->filled('search')) {
            $search = $request->search;
        
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                ->orWhere('prenoms', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $assures = $query->orderByDesc('created_at')->paginate($perPage);

        $paginatedData = new LengthAwarePaginator(
            $assures,
            $assures->total(),
            $assures->perPage(),
            $assures->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success($assures, "Liste des assurés récupérée");
    }
} 