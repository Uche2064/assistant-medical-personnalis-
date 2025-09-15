<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Enums\LienParenteEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\beneficiaire\RegisterBeneficiaireRequest;
use App\Http\Requests\beneficiaire\UpdateBeneficiaireRequest;
use App\Http\Resources\BeneficiaireResource;
use App\Http\Resources\EmployeAssureResource;
use App\Models\Assure;
use App\Models\Facture;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssureController extends Controller
{
    
    /**
     * Gestion des bénéficiaires
     */
    public function beneficiaires(Request $request)
    {
        $user = Auth::user();
        $assure = $user->assure;

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $perPage = $request->input('per_page', 10);

        $beneficiaires = Assure::query()
            ->where('assure_principal_id', $assure->id)
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('nom', 'like', '%' . $request->search . '%')
                    ->orWhere('prenoms', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('lien_parente'), function ($query) use ($request) {
                $query->where('lien_parente', $request->lien_parente);
            })
            ->when($request->filled('sexe'), function ($query) use ($request) { 
                $query->where('sexe', $request->sexe);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $beneficiairesCollection = $beneficiaires->getCollection()->map(fn ($beneficiaire) => $beneficiaire);


        $paginatedData = new LengthAwarePaginator(
            BeneficiaireResource::collection($beneficiairesCollection),
            $beneficiaires->total(),
            $beneficiaires->perPage(),
            $beneficiaires->currentPage(),
            [
                'path' => Paginator::resolveCurrentPath(),
            ]
        );

        return ApiResponse::success($paginatedData, 'Liste des bénéficiaires récupérée avec succès');
    }

    public function beneficiaire($id)
    {
        $user = Auth::user();
        $assure = $user->assure;
        
        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $beneficiaire = $assure->beneficiaires()->find($id);
        
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

        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $validated = $request->validated();

        // Créer le bénéficiaire
        $beneficiaire = Assure::create([
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'date_naissance' => $validated['date_naissance'],
            'sexe' => $validated['sexe'],
            'lien_parente' => $validated['lien_parente'],
            'adresse' => $validated['adresse'],
            'contact' => $validated['contact'] ?? null,
            'profession' => $validated['profession'] ?? null,
            'assure_principal_id' => $assure->id,
            'est_principal' => false,
        ]);

        return ApiResponse::success(new BeneficiaireResource($beneficiaire), 'Bénéficiaire ajouté avec succès');
    }

    /**
     * Modifier un bénéficiaire
     */
    public function modifierBeneficiaire(UpdateBeneficiaireRequest $request, $id)
    {
        $user = Auth::user();
        
        $assure = $user->assure;
        
        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $beneficiaire = $assure->beneficiaires()->find($id);
        
        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé', 404);
        }

        $validated = $request->validated();

        $beneficiaire->update($validated);

        return ApiResponse::success(new BeneficiaireResource($beneficiaire), 'Bénéficiaire modifié avec succès');
    }

    /**
     * Supprimer un bénéficiaire
     */
    public function supprimerBeneficiaire($id)
    {
        $user = Auth::user();
        
        $assure = $user->assure;
            
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
     * Centres de soins assignés
     */
    public function centresSoins(Request $request)
    {
        $user = Auth::user();
        
        $assure = $user->assure;
        
        if (!$assure->est_principal) {
            return ApiResponse::error('Vous n\'êtes pas un assuré principal', 403);
        }

        $query = Prestataire::where('statut', 'valide');

        // Filtres
        if ($request->has('type_prestataire')) {
            $query->where('type_prestataire', $request->type_prestataire);
        }

        if ($request->has('ville')) {
            $query->where('ville', 'like', '%' . $request->ville . '%');
        }

        $prestataires = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($prestataires, 'Liste des centres de soins récupérée avec succès');
    }

    /**
     * Historique des remboursements
     */
    public function historiqueRemboursements(Request $request)
    {
        $user = Auth::user();
        
        $assure = $user->assure;
        
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