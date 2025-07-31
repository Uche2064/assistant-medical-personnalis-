<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Enums\LienParenteEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssureController extends Controller
{
    /**
     * Dashboard de l'assuré principal
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

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

        // Contrat actif
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
     * Gestion des bénéficiaires
     */
    public function beneficiaires(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        $beneficiaires = $assure->beneficiaires()
            ->with('user')
            ->paginate($request->get('per_page', 10));

        return ApiResponse::success($beneficiaires, 'Liste des bénéficiaires récupérée avec succès');
    }

    /**
     * Ajouter un bénéficiaire
     */
    public function ajouterBeneficiaire(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:M,F',
            'lien_parente' => 'required|in:' . implode(',', LienParenteEnum::values()),
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
        ]);

        // Créer le bénéficiaire
        $beneficiaire = Assure::create([
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'date_naissance' => $validated['date_naissance'],
            'sexe' => $validated['sexe'],
            'lien_parente' => $validated['lien_parente'],
            'contact' => $validated['contact'],
            'email' => $validated['email'],
            'adresse' => $validated['adresse'],
            'assure_principal_id' => $assure->id,
            'est_principal' => false,
            'statut' => 'actif',
        ]);

        return ApiResponse::success($beneficiaire, 'Bénéficiaire ajouté avec succès');
    }

    /**
     * Modifier un bénéficiaire
     */
    public function modifierBeneficiaire(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        $beneficiaire = $assure->beneficiaires()->find($id);
        
        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé', 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenoms' => 'sometimes|string|max:255',
            'date_naissance' => 'sometimes|date',
            'sexe' => 'sometimes|in:M,F',
            'lien_parente' => 'sometimes|in:' . implode(',', LienParenteEnum::values()),
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
        ]);

        $beneficiaire->update($validated);

        return ApiResponse::success($beneficiaire, 'Bénéficiaire modifié avec succès');
    }

    /**
     * Supprimer un bénéficiaire
     */
    public function supprimerBeneficiaire($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        $beneficiaire = $assure->beneficiaires()->find($id);
        
        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé', 404);
        }

        $beneficiaire->delete();

        return ApiResponse::success(null, 'Bénéficiaire supprimé avec succès');
    }

    /**
     * Centres de soins assignés
     */
    public function centresSoins(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
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
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
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
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        $contrat = $assure->contrat;
        
        if (!$contrat) {
            return ApiResponse::error('Aucun contrat actif trouvé', 404);
        }

        return ApiResponse::success($contrat, 'Contrat récupéré avec succès');
    }

    /**
     * Profil de l'assuré
     */
    public function profil()
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        return ApiResponse::success($assure, 'Profil récupéré avec succès');
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfil(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(RoleEnum::PHYSIQUE->value)) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $assure = $user->assure;
        
        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
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
} 