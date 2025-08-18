<?php

namespace App\Http\Controllers\v1\Api\Assure;

use App\Enums\LienParenteEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiaireResource;
use App\Services\NotificationService;
use App\Models\Assure;
use App\Models\ReponseQuestionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BeneficiaireController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Liste tous les bénéficiaires de l'assuré principal connecté.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // Récupérer l'assuré principal
            $assurePrincipal = Assure::where('user_id', $user->id)
                ->where('est_principal', true)
                ->whereNull('assure_principal_id')
                ->first();

            if (!$assurePrincipal) {
                return ApiResponse::error('Assuré principal non trouvé', 404);
            }

            // Paramètres de pagination
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            // Construction de la requête de base
            $query = Assure::where('assure_principal_id', $assurePrincipal->id)
                ->where('est_principal', false);

            // Filtres
            if ($request->filled('sexe')) {
                $query->where('sexe', $request->sexe);
            }

            if ($request->filled('lien_parente')) {
                $query->where('lien_parente', $request->lien_parente);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenoms', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $beneficiaires = $query->paginate($perPage);

            // Transformer les données
            $data = $beneficiaires->getCollection()->map(function ($beneficiaire) {
                return [
                    'id' => $beneficiaire->id,
                    'nom' => $beneficiaire->nom,
                    'prenoms' => $beneficiaire->prenoms,
                    'date_naissance' => $beneficiaire->date_naissance,
                    'age' => $beneficiaire->date_naissance ? now()->diffInYears($beneficiaire->date_naissance) : null,
                    'sexe' => $beneficiaire->sexe,
                    'lien_parente' => $beneficiaire->lien_parente,
                    'profession' => $beneficiaire->profession,
                    'contact' => $beneficiaire->contact,
                    'email' => $beneficiaire->email,
                    'photo' => $beneficiaire->photo,
                    'est_principal' => $beneficiaire->est_principal,
                    'created_at' => $beneficiaire->created_at,
                    'updated_at' => $beneficiaire->updated_at,
                ];
            });

            // Créer un nouveau paginator avec les données transformées
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $data,
                $beneficiaires->total(),
                $beneficiaires->perPage(),
                $beneficiaires->currentPage(),
                [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );

            Log::info('Bénéficiaires récupérés pour l\'assuré principal', [
                'assure_principal_id' => $assurePrincipal->id,
                'nombre_beneficiaires' => $beneficiaires->total(),
                'page' => $beneficiaires->currentPage(),
                'per_page' => $beneficiaires->perPage(),
                'filters' => $request->only(['sexe', 'lien_parente', 'profession', 'search', 'age_min', 'age_max'])
            ]);

            return ApiResponse::success(
                $paginatedData,
                'Bénéficiaires récupérés avec succès'
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des bénéficiaires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des bénéficiaires', 500);
        }
    }

    /**
     * Affiche les informations d'un bénéficiaire spécifique.
     *
     * @param int $id Identifiant du bénéficiaire
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est un client physique
            if ($user->entreprise) {
                return ApiResponse::error('Cette fonctionnalité est réservée aux clients physiques', 403);
            }

            // Récupérer l'assuré principal
            $assurePrincipal = Assure::where('user_id', $user->id)
                ->where('est_principal', true)
                ->whereNull('assure_principal_id')
                ->first();

            if (!$assurePrincipal) {
                return ApiResponse::error('Assuré principal non trouvé', 404);
            }

            // Récupérer le bénéficiaire et vérifier qu'il appartient à cet assuré principal
            $beneficiaire = Assure::where('id', $id)
                ->where('assure_principal_id', $assurePrincipal->id)
                ->where('est_principal', false)
                ->first();

            if (!$beneficiaire) {
                return ApiResponse::error('Bénéficiaire non trouvé ou non autorisé', 404);
            }

            Log::info('Bénéficiaire récupéré', [
                'beneficiaire_id' => $beneficiaire->id,
                'assure_principal_id' => $assurePrincipal->id
            ]);

            return ApiResponse::success(
                new BeneficiaireResource($beneficiaire),
                'Bénéficiaire récupéré avec succès'
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du bénéficiaire', [
                'error' => $e->getMessage(),
                'beneficiaire_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération du bénéficiaire', 500);
        }
    }

    /**
     * Ajoute un nouveau bénéficiaire à l'assuré principal.
     *
     * @param StoreBeneficiaireRequest $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est un client physique
            if ($user->entreprise) {
                return ApiResponse::error('Cette fonctionnalité est réservée aux clients physiques', 403);
            }

            // Récupérer l'assuré principal
            $assurePrincipal = Assure::where('user_id', $user->id)
                ->where('est_principal', true)
                ->whereNull('assure_principal_id')
                ->first();

            if (!$assurePrincipal) {
                return ApiResponse::error('Assuré principal non trouvé', 404);
            }

            // Valider les données
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenoms' => 'required|string|max:255',
                'date_naissance' => 'required|date|before:today',
                'sexe' => 'required|string|in:M,F',
                'lien_parente' => 'required|string|in:' . implode(',', LienParenteEnum::values()),
                'profession' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            if ($validator->fails()) {
                return ApiResponse::error($validator->errors(), 422);
            }

            $validatedData = $validator->validated();

            // Vérifier si le bénéficiaire existe déjà (même nom, prénoms et date de naissance)
            $beneficiaireExistant = Assure::where('assure_principal_id', $assurePrincipal->id)
                ->where('nom', $validatedData['nom'])
                ->where('prenoms', $validatedData['prenoms'])
                ->where('date_naissance', $validatedData['date_naissance'])
                ->where('est_principal', false)
                ->first();

            if ($beneficiaireExistant) {
                return ApiResponse::error('Un bénéficiaire avec ces informations existe déjà', 422);
            }

            // Gérer l'upload de photo si fournie
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = ImageUploadHelper::uploadImage(
                    $request->file('photo'),
                    'users/' . $user->email . '/beneficiaires'
                );
            }

            // Créer le bénéficiaire
            $beneficiaire = new Assure();
            $beneficiaire->assure_principal_id = $assurePrincipal->id;
            $beneficiaire->nom = $validatedData['nom'];
            $beneficiaire->prenoms = $validatedData['prenoms'];
            $beneficiaire->date_naissance = $validatedData['date_naissance'];
            $beneficiaire->sexe = $validatedData['sexe'];
            $beneficiaire->lien_parente = $validatedData['lien_parente'];
            $beneficiaire->profession = $validatedData['profession'] ?? null;
            $beneficiaire->contact = $validatedData['contact'] ?? null;
            $beneficiaire->email = $validatedData['email'] ?? null;
            $beneficiaire->photo = $photoPath;
            $beneficiaire->est_principal = false;
            $beneficiaire->save();

            // Notifier les techniciens, prestataires et médecins contrôleurs
            try {
                $this->notificationService->notifyBeneficiaireAjoute($beneficiaire, $user);
            } catch (\Exception $e) {
                Log::warning('Erreur lors de l\'envoi des notifications pour le nouveau bénéficiaire', [
                    'error' => $e->getMessage(),
                    'beneficiaire_id' => $beneficiaire->id
                ]);
            }

            Log::info('Bénéficiaire créé avec succès', [
                'beneficiaire_id' => $beneficiaire->id,
                'assure_principal_id' => $assurePrincipal->id,
                'nom' => $beneficiaire->nom,
                'prenoms' => $beneficiaire->prenoms
            ]);

            return ApiResponse::success(
                new BeneficiaireResource($beneficiaire),
                'Bénéficiaire ajouté avec succès'
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du bénéficiaire', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->validated()
            ]);

            return ApiResponse::error('Erreur lors de la création du bénéficiaire', 500);
        }
    }

    /**
     * Mise à jour des informations d'un bénéficiaire.
     *
     * @param int $id Identifiant du bénéficiaire
     * @param UpdateBeneficiaireRequest $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est un client physique
            if ($user->entreprise) {
                return ApiResponse::error('Cette fonctionnalité est réservée aux clients physiques', 403);
            }

            // Récupérer l'assuré principal
            $assurePrincipal = Assure::where('user_id', $user->id)
                ->where('est_principal', true)
                ->whereNull('assure_principal_id')
                ->first();

            if (!$assurePrincipal) {
                return ApiResponse::error('Assuré principal non trouvé', 404);
            }

            // Récupérer le bénéficiaire et vérifier qu'il appartient à cet assuré principal
            $beneficiaire = Assure::where('id', $id)
                ->where('assure_principal_id', $assurePrincipal->id)
                ->where('est_principal', false)
                ->first();

            if (!$beneficiaire) {
                return ApiResponse::error('Bénéficiaire non trouvé ou non autorisé', 404);
            }

            // Valider les données
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'nom' => 'sometimes|required|string|max:255',
                'prenoms' => 'sometimes|required|string|max:255',
                'date_naissance' => 'sometimes|required|date|before:today',
                'sexe' => 'sometimes|required|string|in:M,F',
                'lien_parente' => 'sometimes|required|string|in:' . implode(',', LienParenteEnum::values()),
                'profession' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            if ($validator->fails()) {
                return ApiResponse::error($validator->errors(), 422);
            }

            $validatedData = $validator->validated();

            // Vérifier si les nouvelles informations ne créent pas un doublon
            if (isset($validatedData['nom']) && isset($validatedData['prenoms']) && isset($validatedData['date_naissance'])) {
                $beneficiaireExistant = Assure::where('assure_principal_id', $assurePrincipal->id)
                    ->where('nom', $validatedData['nom'])
                    ->where('prenoms', $validatedData['prenoms'])
                    ->where('date_naissance', $validatedData['date_naissance'])
                    ->where('est_principal', false)
                    ->where('id', '!=', $id)
                    ->first();

                if ($beneficiaireExistant) {
                    return ApiResponse::error('Un bénéficiaire avec ces informations existe déjà', 422);
                }
            }

            // Gérer l'upload de nouvelle photo si fournie
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($beneficiaire->photo && Storage::disk('public')->exists($beneficiaire->photo)) {
                    Storage::disk('public')->delete($beneficiaire->photo);
                }

                $photoPath = ImageUploadHelper::uploadImage(
                    $request->file('photo'),
                    'users/' . $user->email . '/beneficiaires'
                );
                $beneficiaire->photo = $photoPath;
            }

            // Mettre à jour les autres champs
            $beneficiaire->fill($validatedData);
            $beneficiaire->save();

            Log::info('Bénéficiaire mis à jour avec succès', [
                'beneficiaire_id' => $beneficiaire->id,
                'assure_principal_id' => $assurePrincipal->id,
                'updated_fields' => array_keys($request->validated())
            ]);

            return ApiResponse::success(
                new BeneficiaireResource($beneficiaire),
                'Bénéficiaire mis à jour avec succès'
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du bénéficiaire', [
                'error' => $e->getMessage(),
                'beneficiaire_id' => $id,
                'user_id' => Auth::id(),
                'request_data' => $request->validated()
            ]);

            return ApiResponse::error('Erreur lors de la mise à jour du bénéficiaire', 500);
        }
    }

    /**
     * Supprime un bénéficiaire.
     *
     * @param int $id Identifiant du bénéficiaire
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Vérifier que l'utilisateur est un client physique
            if ($user->entreprise) {
                return ApiResponse::error('Cette fonctionnalité est réservée aux clients physiques', 403);
            }

            // Récupérer l'assuré principal
            $assurePrincipal = Assure::where('user_id', $user->id)
                ->where('est_principal', true)
                ->whereNull('assure_principal_id')
                ->first();

            if (!$assurePrincipal) {
                return ApiResponse::error('Assuré principal non trouvé', 404);
            }

            // Récupérer le bénéficiaire et vérifier qu'il appartient à cet assuré principal
            $beneficiaire = Assure::where('id', $id)
                ->where('assure_principal_id', $assurePrincipal->id)
                ->where('est_principal', false)
                ->first();

            if (!$beneficiaire) {
                return ApiResponse::error('Bénéficiaire non trouvé ou non autorisé', 404);
            }

            // Notifier les techniciens, prestataires et médecins contrôleurs AVANT la suppression
            try {
                $this->notificationService->notifyBeneficiaireSupprime($beneficiaire, $user);
            } catch (\Exception $e) {
                Log::warning('Erreur lors de l\'envoi des notifications pour la suppression du bénéficiaire', [
                    'error' => $e->getMessage(),
                    'beneficiaire_id' => $beneficiaire->id
                ]);
            }

            // Supprimer la photo si elle existe
            if ($beneficiaire->photo && Storage::disk('public')->exists($beneficiaire->photo)) {
                Storage::disk('public')->delete($beneficiaire->photo);
            }

            // Supprimer les réponses du questionnaire associées
            ReponseQuestionnaire::where('personne_type', Assure::class)
                ->where('personne_id', $beneficiaire->id)
                ->delete();

            // Supprimer le bénéficiaire
            $beneficiaire->delete();

            Log::info('Bénéficiaire supprimé avec succès', [
                'beneficiaire_id' => $id,
                'assure_principal_id' => $assurePrincipal->id,
                'nom' => $beneficiaire->nom,
                'prenoms' => $beneficiaire->prenoms
            ]);

            return ApiResponse::success(null, 'Bénéficiaire supprimé avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du bénéficiaire', [
                'error' => $e->getMessage(),
                'beneficiaire_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la suppression du bénéficiaire', 500);
        }
    }
}
