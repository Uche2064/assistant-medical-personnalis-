<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Enums\LienParenteEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\beneficiaire\RegisterBeneficiaireRequest;
use App\Http\Requests\beneficiaire\UpdateBeneficiaireRequest;
use App\Http\Resources\BeneficiaireResource;
use App\Http\Resources\EmployeAssureResource;
use App\Models\Assure;
use App\Models\Facture;
use App\Models\Personne;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssureController extends Controller
{

    /**
     * Gestion des bénéficiaires
     */
    public function beneficiaires(Request $request)
    {
        $user = Auth::user();
        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $beneficiaires = Assure::where('assure_principal_id', $assure->id)
            ->with(['user.personne'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success(
            BeneficiaireResource::collection($beneficiaires),
            'Liste des bénéficiaires récupérée avec succès'
        );
    }

    public function beneficiaire($id)
    {
        $user = Auth::user();
        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $beneficiaire = $assure->beneficiaires()->with(['user.personne'])->find($id);

        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé', 404);
        }

        return ApiResponse::success(new BeneficiaireResource($beneficiaire), 'Bénéficiaire récupéré avec succès');
    }

    /**
     * Ajouter un bénéficiaire
     */
    public function ajouterBeneficiaire(RegisterBeneficiaireRequest $request)
    {
        $user = Auth::user();
        $assure = $user->assure;


        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $photoUrl = null;
        $validated = $request->validated();
        // Gestion de l'upload de la photo
        if (isset($validated['photo'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads', $validated['email'], 'user_photo');
            if (!$photoUrl) {
                return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
            }
        }

        DB::beginTransaction();
        try {
            // 1. Créer la personne
            $personne = Personne::create([
                'nom' => $validated['nom'],
                'prenoms' => $validated['prenoms'],
                'date_naissance' => $validated['date_naissance'],
                'sexe' => $validated['sexe'],
                'profession' => $validated['profession'] ?? null,
            ]);

            // 2. Créer l'utilisateur (sans compte de connexion)
            $beneficiaireUser = User::create([
                'email' => $validated['email'] ?? $validated['nom'] . '_' . time() . '@beneficiaire.local',
                'password' => Hash::make(Str::random(32)), // Mot de passe aléatoire
                'contact' => $validated['contact'] ?? null,
                'adresse' => $validated['adresse'] ?? null,
                'est_actif' => true,
                'personne_id' => $personne->id,
                'photo_url' => $photoUrl,
            ]);

            // Créer l'assuré bénéficiaire
            $beneficiaire = Assure::create([
                'user_id' => $beneficiaireUser->id,
                'client_id' => $assure->client_id,
                'lien_parente' => $validated['lien_parente'],
                'est_principal' => false,
                'assure_principal_id' => $assure->id,
            ]);
            DB::commit();
            return ApiResponse::success(null, 'Bénéficiaire ajouté avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de l\'ajout du bénéficiaire', 500, $e->getMessage());
        }
    }

    /**
     * Modifier un bénéficiaire
     */
    public function modifierBeneficiaire(UpdateBeneficiaireRequest $request, $id)
    {
        $user = Auth::user();

        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $beneficiaire = $assure->beneficiaires()->find($id);

        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé', 404);
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Mettre à jour le lien de parenté si fourni
            if (isset($validated['lien_parente'])) {
                $beneficiaire->update([
                    'lien_parente' => $validated['lien_parente']
                ]);
            }

            // Mettre à jour les informations de la personne
            if ($beneficiaire->user && $beneficiaire->user->personne) {
                $personneData = [];
                if (isset($validated['nom'])) $personneData['nom'] = $validated['nom'];
                if (isset($validated['prenoms'])) $personneData['prenoms'] = $validated['prenoms'];
                if (isset($validated['date_naissance'])) $personneData['date_naissance'] = $validated['date_naissance'];
                if (isset($validated['sexe'])) $personneData['sexe'] = $validated['sexe'];
                if (isset($validated['profession'])) $personneData['profession'] = $validated['profession'];

                if (!empty($personneData)) {
                    $beneficiaire->user->personne->update($personneData);
                }
            }

            // Mettre à jour les informations de l'utilisateur
            if ($beneficiaire->user) {
                $userData = [];
                if (isset($validated['email'])) $userData['email'] = $validated['email'];
                if (isset($validated['contact'])) $userData['contact'] = $validated['contact'];
                if (isset($validated['adresse'])) $userData['adresse'] = $validated['adresse'];

                if (!empty($userData)) {
                    $beneficiaire->user->update($userData);
                }
            }

            DB::commit();

            return ApiResponse::success(
                new BeneficiaireResource($beneficiaire->load('user.personne')),
                'Bénéficiaire modifié avec succès'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du bénéficiaire: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la modification du bénéficiaire', 500, $e->getMessage());
        }
    }

    /**
     * Supprimer un bénéficiaire
     */
    public function supprimerBeneficiaire($id)
    {
        $user = Auth::user();

        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $beneficiaire = $assure->beneficiaires()->find($id);

        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé', 404);
        }

        $beneficiaire->delete();

        return ApiResponse::success(null, 'Bénéficiaire supprimé avec succès');
    }

    /**
     * Vérifier si l'assuré principal a un contrat en cours de validité
     */
    public function hasActiveContrat()
    {
        $user = Auth::user();
        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $contrat = $assure->contrat;

        if (!$contrat) {
            return ApiResponse::error('Aucun contrat trouvé', 404, ['existing' => false]);
        }

        return ApiResponse::success($contrat, 'TypeContrat récupéré avec succès');
    }

    /**
     * Récupérer le profil de l'assuré
     */
    public function profil()
    {
        $user = Auth::user();
        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        return ApiResponse::success([
            'id' => $assure->id,
            'nom' => $user->personne->nom ?? null,
            'prenoms' => $user->personne->prenoms ?? null,
            'date_naissance' => $user->personne->date_naissance ?? null,
            'sexe' => $user->personne->sexe ?? null,
            'contact' => $user->contact,
            'email' => $user->email,
            'adresse' => $user->adresse,
            'photo_url' => $user->photo_url,
            'lien_parente' => $assure->lien_parente,
            'est_principal' => $assure->est_principal,
        ], 'Profil récupéré avec succès');
    }

    /**
     * Centres de soins assignés (alias de prestataires)
     */
    public function centresSoins(Request $request)
    {
        return $this->prestataires($request);
    }

    /**
     * Centres de soins assignés
     */
    public function prestataires(Request $request)
    {
        $user = Auth::user();

        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $query = Prestataire::where('statut', 'valide');


        $prestataires = $query->get();

        return ApiResponse::success($prestataires, 'Liste des centres de soins récupérée avec succès');
    }

    /**
     * Historique des remboursements
     */
    public function historiqueRemboursements(Request $request)
    {
        $user = Auth::user();

        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $query = Facture::where('assure_id', $assure->id)
            ->with(['prestataire']);

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->has('montant_min')) {
            $query->where('montant', '>=', $request->montant_min);
        }

        if ($request->has('montant_max')) {
            $query->where('montant', '<=', $request->montant_max);
        }

        $factures = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($factures, 'Historique des remboursements récupéré avec succès');
    }

    /**
     * Détails du contrat
     */
    public function contrat()
    {
        $user = Auth::user();

        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $contrat = $assure->contrat;

        if (!$contrat) {
            return ApiResponse::error('Aucun contrat actif trouvé', 404);
        }

        return ApiResponse::success($contrat, 'TypeContrat récupéré avec succès');
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Aucun profil d\'assuré trouvé. Veuillez d\'abord soumettre une demande d\'adhésion.', 404);
        }

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenoms' => 'sometimes|string|max:255',
            'date_naissance' => 'sometimes|date',
            'sexe' => 'sometimes|in:M,F',
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
        ]);

        $assure->update($validated);

        return ApiResponse::success($assure, 'Profil mis à jour avec succès');
    }

    /**
     * Dashboard de l'assuré principal
     */
    public function dashboard()
    {
        $user = Auth::user();


        $assure = $user->assure;

        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        // Statistiques de l'assuré
        $stats = [
            'total_beneficiaires' => $assure->beneficiaires()->count(),
            'contrats_actifs' => $assure->contrat ? 1 : 0,
            'factures_total' => Facture::where('assure_id', $assure->id)->count(),
            'factures_remboursees' => Facture::where('assure_id', $assure->id)
                ->where('statut', 'remboursee')
                ->count(),
            'montant_total_rembourse' => Facture::where('assure_id', $assure->id)
                ->where('statut', 'remboursee')
                ->sum('montant'),
        ];

        // TypeContrat actif
        $contratActif = $assure->contrat;

        // Factures récentes
        $facturesRecentes = Facture::where('assure_id', $assure->id)
            ->with(['prestataire'])
            ->latest()
            ->take(5)
            ->get();

        // Bénéficiaires
        $beneficiaires = $assure->beneficiaires()
            ->with('user')
            ->get();

        return ApiResponse::success([
            'assure' => [
                'id' => $assure->id,
                'nom' => $assure->nom,
                'prenoms' => $assure->prenoms,
                'date_naissance' => $assure->date_naissance,
                'sexe' => $assure->sexe,
                'contact' => $assure->contact,
                'email' => $assure->email,
                'adresse' => $assure->adresse,
            ],
            'statistiques' => $stats,
            'contrat_actif' => $contratActif,
            'factures_recentes' => $facturesRecentes,
            'beneficiaires' => $beneficiaires,
        ], 'Dashboard assuré récupéré avec succès');
    }

    /**
     * Afficher les employés assurés d'une entreprise
     */
    public function getEmployeAssure(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return ApiResponse::error('Aucun profil d\'entreprise trouvé.', 404);
        }

        $perPage = $request->input('per_page', 10);

        $employes = Assure::query()
            ->where('entreprise_id', $entreprise->id)
            ->with(['user', 'contrat'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('nom', 'like', '%' . $request->search . '%')
                        ->orWhere('prenoms', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('sexe'), function ($query) use ($request) {
                $query->where('sexe', $request->sexe);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $employesCollection = $employes->getCollection();

        $paginatedData = new LengthAwarePaginator(
            EmployeAssureResource::collection($employesCollection),
            $employes->total(),
            $employes->perPage(),
            $employes->currentPage(),
            [
                'path' => Paginator::resolveCurrentPath(),
            ]
        );

        return ApiResponse::success($paginatedData, 'Liste des employés assurés récupérée avec succès');
    }
}
