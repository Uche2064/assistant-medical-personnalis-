<?php

namespace App\Http\Controllers\v1\Api\Assure;

use App\Enums\LienEnum;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class BeneficiaireController extends Controller
{
    /**
     * Liste tous les bénéficiaires d'un assuré principal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est bien connecté et a accès à ses données
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Récupérer l'assuré principal associé à cet utilisateur
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('lien_parente', LienEnum::PRINCIPAL)
            ->first();

        if (!$assurePrincipal) {
            return response()->json(['error' => 'Aucun assuré principal trouvé pour cet utilisateur'], 404);
        }

        // Récupérer tous les bénéficiaires (assurés dépendants) de cet assuré principal
        $beneficiaires = $assurePrincipal->assureEnfants()->with('user')->get();

        return response()->json([
            'status' => 'success',
            'data' => $beneficiaires
        ]);
    }

    /**
     * Affiche les informations d'un bénéficiaire spécifique.
     *
     * @param int $id Identifiant du bénéficiaire
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est bien connecté et a accès à ses données
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Récupérer l'assuré principal associé à cet utilisateur
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('lien_parente', LienEnum::PRINCIPAL)
            ->first();

        if (!$assurePrincipal) {
            return response()->json(['error' => 'Aucun assuré principal trouvé pour cet utilisateur'], 404);
        }

        // Récupérer le bénéficiaire spécifique et vérifier qu'il appartient bien à cet assuré principal
        $beneficiaire = $assurePrincipal->assureEnfants()->with(['user', 'garanties'])->find($id);

        if (!$beneficiaire) {
            return response()->json(['error' => 'Bénéficiaire non trouvé ou non autorisé'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $beneficiaire
        ]);
    }

    /**
     * Ajoute un nouveau bénéficiaire à un assuré principal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est bien connecté
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Récupérer l'assuré principal associé à cet utilisateur
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('lien_parente', LienEnum::PRINCIPAL)
            ->first();

        if (!$assurePrincipal) {
            return response()->json(['error' => 'Aucun assuré principal trouvé pour cet utilisateur'], 404);
        }

        // Valider les données du formulaire
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lien_parente' => 'required|string',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Créer un nouvel utilisateur pour le bénéficiaire si nécessaire
        // Cette logique dépendra de votre implémentation spécifique
        // Par exemple, si les bénéficiaires ont leur propre compte utilisateur ou non

        // Créer le bénéficiaire (assuré dépendant)
        $beneficiaire = new Assure();
        $beneficiaire->client_id = $assurePrincipal->client_id;
        $beneficiaire->assure_parent_id = $assurePrincipal->id;
        $beneficiaire->lien_parente = $request->lien_parente;
        
        // Si un user_id est fourni ou créé, l'associer
        if (isset($newUserId)) {
            $beneficiaire->user_id = $newUserId;
        }
        
        $beneficiaire->save();

        // Associer les garanties de l'assuré principal au bénéficiaire si nécessaire
        // Cette logique dépendra de vos règles métier spécifiques

        return response()->json([
            'status' => 'success',
            'message' => 'Bénéficiaire ajouté avec succès',
            'data' => $beneficiaire
        ], 201);
    }

    /**
     * Mise à jour des informations d'un bénéficiaire.
     *
     * @param int $id Identifiant du bénéficiaire
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est bien connecté
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Récupérer l'assuré principal associé à cet utilisateur
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('lien_parente', LienEnum::PRINCIPAL)
            ->first();

        if (!$assurePrincipal) {
            return response()->json(['error' => 'Aucun assuré principal trouvé pour cet utilisateur'], 404);
        }

        // Récupérer le bénéficiaire à mettre à jour et vérifier qu'il appartient bien à cet assuré principal
        $beneficiaire = $assurePrincipal->assureEnfants()->find($id);

        if (!$beneficiaire) {
            return response()->json(['error' => 'Bénéficiaire non trouvé ou non autorisé'], 404);
        }

        // Valider les données du formulaire
        $validator = Validator::make($request->all(), [
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'lien_parente' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mettre à jour les informations du bénéficiaire
        if ($request->has('lien_parente')) {
            $beneficiaire->lien_parente = $request->lien_parente;
        }

        // Si l'utilisateur associé au bénéficiaire existe, mettre à jour ses informations également
        if ($beneficiaire->user_id) {
            $userBeneficiaire = $beneficiaire->user;
            if ($userBeneficiaire) {
                // Mettre à jour les informations utilisateur si nécessaire
                $userBeneficiaire->save();
            }
        }

        $beneficiaire->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Informations du bénéficiaire mises à jour avec succès',
            'data' => $beneficiaire
        ]);
    }

    /**
     * Supprime un bénéficiaire.
     *
     * @param int $id Identifiant du bénéficiaire
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est bien connecté
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Récupérer l'assuré principal associé à cet utilisateur
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('lien_parente', LienEnum::PRINCIPAL)
            ->first();

        if (!$assurePrincipal) {
            return response()->json(['error' => 'Aucun assuré principal trouvé pour cet utilisateur'], 404);
        }

        // Récupérer le bénéficiaire à supprimer et vérifier qu'il appartient bien à cet assuré principal
        $beneficiaire = $assurePrincipal->assureEnfants()->find($id);

        if (!$beneficiaire) {
            return response()->json(['error' => 'Bénéficiaire non trouvé ou non autorisé'], 404);
        }

        // Supprimer le bénéficiaire (utilise soft delete si configuré dans le modèle)
        $beneficiaire->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Bénéficiaire supprimé avec succès'
        ]);
    }
}