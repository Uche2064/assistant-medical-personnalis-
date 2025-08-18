<?php

namespace App\Http\Controllers\v1\Api\facture;

use App\Enums\StatutFactureEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FactureValidationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Validation par technicien
     */
    public function validateByTechnicien(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un technicien
        if (!$user->hasRole('technicien')) {
            return ApiResponse::error('Accès non autorisé. Seuls les techniciens peuvent valider.', 403);
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture est en attente
        if (!$facture->isPending()) {
            return ApiResponse::error('Cette facture ne peut plus être validée par un technicien', 400);
        }

        try {
            $facture->validateByTechnicien($user->personnel->id);

            // Notifier les médecins contrôleurs
            $this->notificationService->notifyValidationTechnicien($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien']),
                'message' => 'Facture validée avec succès par le technicien'
            ], 'Facture validée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la validation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Rejet par technicien
     */
    public function rejectByTechnicien(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un technicien
        if (!$user->hasRole('technicien')) {
            return ApiResponse::error('Accès non autorisé. Seuls les techniciens peuvent rejeter.', 403);
        }

        $validator = Validator::make($request->all(), [
            'motif_rejet' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors());
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture est en attente
        if (!$facture->isPending()) {
            return ApiResponse::error('Cette facture ne peut plus être rejetée par un technicien', 400);
        }

        try {
            $facture->rejectByTechnicien($user->personnel->id, $request->motif_rejet);

            // Notifier le prestataire du rejet
            $this->notificationService->notifyRejetTechnicien($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien']),
                'message' => 'Facture rejetée par le technicien'
            ], 'Facture rejetée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du rejet: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validation par médecin contrôleur
     */
    public function validateByMedecin(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un médecin contrôleur
        if (!$user->hasRole('medecin_controleur')) {
            return ApiResponse::error('Accès non autorisé. Seuls les médecins contrôleurs peuvent valider.', 403);
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture a été validée par le technicien
        if (!$facture->isValidatedByTechnicien()) {
            return ApiResponse::error('Cette facture doit d\'abord être validée par un technicien', 400);
        }

        try {
            $facture->validateByMedecin($user->personnel->id);

            // Notifier les comptables
            $this->notificationService->notifyValidationMedecin($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien', 'medecin']),
                'message' => 'Facture validée avec succès par le médecin contrôleur'
            ], 'Facture validée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la validation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Rejet par médecin contrôleur
     */
    public function rejectByMedecin(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un médecin contrôleur
        if (!$user->hasRole('medecin_controleur')) {
            return ApiResponse::error('Accès non autorisé. Seuls les médecins contrôleurs peuvent rejeter.', 403);
        }

        $validator = Validator::make($request->all(), [
            'motif_rejet' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors());
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture a été validée par le technicien
        if (!$facture->isValidatedByTechnicien()) {
            return ApiResponse::error('Cette facture doit d\'abord être validée par un technicien', 400);
        }

        try {
            $facture->rejectByMedecin($user->personnel->id, $request->motif_rejet);

            // Notifier le prestataire du rejet
            $this->notificationService->notifyRejetMedecin($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien', 'medecin']),
                'message' => 'Facture rejetée par le médecin contrôleur'
            ], 'Facture rejetée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du rejet: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Autorisation par comptable
     */
    public function authorizeByComptable(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un comptable
        if (!$user->hasRole('comptable')) {
            return ApiResponse::error('Accès non autorisé. Seuls les comptables peuvent autoriser.', 403);
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture a été validée par le médecin
        if (!$facture->isValidatedByMedecin()) {
            return ApiResponse::error('Cette facture doit d\'abord être validée par un médecin contrôleur', 400);
        }

        try {
            $facture->authorizeByComptable($user->personnel->id);

            // Notifier le prestataire de l'autorisation
            $this->notificationService->notifyAutorisationComptable($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien', 'medecin', 'comptable']),
                'message' => 'Facture autorisée avec succès par le comptable'
            ], 'Facture autorisée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de l\'autorisation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Rejet par comptable
     */
    public function rejectByComptable(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un comptable
        if (!$user->hasRole('comptable')) {
            return ApiResponse::error('Accès non autorisé. Seuls les comptables peuvent rejeter.', 403);
        }

        $validator = Validator::make($request->all(), [
            'motif_rejet' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors());
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture a été validée par le médecin
        if (!$facture->isValidatedByMedecin()) {
            return ApiResponse::error('Cette facture doit d\'abord être validée par un médecin contrôleur', 400);
        }

        try {
            $facture->rejectByComptable($user->personnel->id, $request->motif_rejet);

            // Notifier le prestataire du rejet
            $this->notificationService->notifyRejetComptable($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien', 'medecin', 'comptable']),
                'message' => 'Facture rejetée par le comptable'
            ], 'Facture rejetée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du rejet: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Marquer comme remboursée
     */
    public function markAsReimbursed(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un comptable
        if (!$user->hasRole('comptable')) {
            return ApiResponse::error('Accès non autorisé. Seuls les comptables peuvent marquer comme remboursée.', 403);
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture a été autorisée par le comptable
        if (!$facture->isAuthorizedByComptable()) {
            return ApiResponse::error('Cette facture doit d\'abord être autorisée par un comptable', 400);
        }

        try {
            $facture->markAsReimbursed();

            // Notifier le prestataire du remboursement
            $this->notificationService->notifyRemboursement($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'technicien', 'medecin', 'comptable']),
                'message' => 'Facture marquée comme remboursée'
            ], 'Facture remboursée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du marquage: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Modifier une facture rejetée
     */
    public function updateRejectedFacture(Request $request, $factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est le prestataire propriétaire de la facture
        if (!$user->hasRole('prestataire') || !$user->prestataire) {
            return ApiResponse::error('Accès non autorisé. Seuls les prestataires peuvent modifier leurs factures.', 403);
        }

        $facture = Facture::with(['prestataire', 'sinistre.assure'])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture appartient au prestataire
        if ($facture->prestataire_id !== $user->prestataire->id) {
            return ApiResponse::error('Vous ne pouvez modifier que vos propres factures', 403);
        }

        // Vérifier que la facture peut être modifiée (rejetée)
        if (!$facture->canBeModified()) {
            return ApiResponse::error('Cette facture ne peut pas être modifiée', 400);
        }

        $validator = Validator::make($request->all(), [
            'montant_reclame' => 'required|numeric|min:0',
            'diagnostic' => 'required|string|min:10',
            'lignes_facture' => 'required|array|min:1',
            'lignes_facture.*.garantie_id' => 'required|exists:garanties,id',
            'lignes_facture.*.libelle_acte' => 'required|string|min:3',
            'lignes_facture.*.quantite' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors());
        }

        try {
            // Calculer les nouveaux montants
            $montantTotal = 0;
            $montantCouvert = 0;
            $ticketModerateur = 0;

            foreach ($request->lignes_facture as $ligneData) {
                $garantie = \App\Models\Garantie::find($ligneData['garantie_id']);
                $prixUnitaire = $garantie->prix_standard;
                $quantite = $ligneData['quantite'];
                $prixTotal = $prixUnitaire * $quantite;
                $tauxCouverture = $garantie->taux_couverture;
                $montantLigneCouvert = $prixTotal * ($tauxCouverture / 100);
                $ticketModérateurLigne = $prixTotal - $montantLigneCouvert;

                $montantTotal += $prixTotal;
                $montantCouvert += $montantLigneCouvert;
                $ticketModerateur += $ticketModérateurLigne;
            }

            // Mettre à jour la facture
            $facture->update([
                'montant_reclame' => $montantTotal,
                'montant_a_rembourser' => $montantCouvert,
                'ticket_moderateur' => $ticketModerateur,
                'diagnostic' => $request->diagnostic,
            ]);

            // Supprimer les anciennes lignes et créer les nouvelles
            $facture->lignesFacture()->delete();

            foreach ($request->lignes_facture as $ligneData) {
                $garantie = \App\Models\Garantie::find($ligneData['garantie_id']);
                $prixUnitaire = $garantie->prix_standard;
                $quantite = $ligneData['quantite'];
                $prixTotal = $prixUnitaire * $quantite;
                $tauxCouverture = $garantie->taux_couverture;
                $montantLigneCouvert = $prixTotal * ($tauxCouverture / 100);
                $ticketModérateurLigne = $prixTotal - $montantLigneCouvert;

                $facture->lignesFacture()->create([
                    'garantie_id' => $garantie->id,
                    'libelle_acte' => $ligneData['libelle_acte'],
                    'prix_unitaire' => $prixUnitaire,
                    'quantite' => $quantite,
                    'prix_total' => $prixTotal,
                    'taux_couverture' => $tauxCouverture,
                    'montant_couvert' => $montantLigneCouvert,
                    'ticket_moderateur' => $ticketModérateurLigne,
                ]);
            }

            // Réinitialiser le statut à "en attente"
            $facture->resetToPending();

            // Notifier les techniciens de la nouvelle facture modifiée
            $this->notificationService->notifyNouvelleFacture($facture);

            return ApiResponse::success([
                'facture' => $facture->fresh(['prestataire', 'sinistre.assure', 'lignesFacture.garantie']),
                'message' => 'Facture modifiée et remise en attente de validation'
            ], 'Facture modifiée avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la modification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtenir l'historique des validations d'une facture
     */
    public function getValidationHistory($factureId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a accès aux factures
        if (!$user->hasRole(['technicien', 'medecin_controleur', 'comptable', 'admin_global', 'gestionnaire', 'prestataire'])) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $facture = Facture::with([
            'prestataire', 
            'sinistre.assure', 
            'technicien', 
            'medecin', 
            'comptable'
        ])->find($factureId);
        
        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Si c'est un prestataire, vérifier qu'il s'agit de sa facture
        if ($user->hasRole('prestataire') && $facture->prestataire_id !== $user->prestataire->id) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $historique = [
            'facture' => $facture,
            'etapes' => [
                [
                    'etape' => 1,
                    'nom' => 'Validation Technicien',
                    'statut' => $facture->est_valide_par_technicien ? 'validé' : ($facture->isRejectedByTechnicien() ? 'rejeté' : 'en attente'),
                    'validateur' => $facture->technicien,
                    'date' => $facture->valide_par_technicien_a ?? $facture->rejet_par_technicien_a,
                    'motif_rejet' => $facture->motif_rejet_technicien,
                ],
                [
                    'etape' => 2,
                    'nom' => 'Validation Médecin Contrôleur',
                    'statut' => $facture->est_valide_par_medecin ? 'validé' : ($facture->isRejectedByMedecin() ? 'rejeté' : 'en attente'),
                    'validateur' => $facture->medecin,
                    'date' => $facture->valide_par_medecin_a ?? $facture->rejet_par_medecin_a,
                    'motif_rejet' => $facture->motif_rejet_medecin,
                ],
                [
                    'etape' => 3,
                    'nom' => 'Autorisation Comptable',
                    'statut' => $facture->est_autorise_par_comptable ? 'autorisé' : ($facture->isRejectedByComptable() ? 'rejeté' : 'en attente'),
                    'validateur' => $facture->comptable,
                    'date' => $facture->autorise_par_comptable_a ?? $facture->rejet_par_comptable_a,
                    'motif_rejet' => $facture->motif_rejet_comptable,
                ],
                [
                    'etape' => 4,
                    'nom' => 'Remboursement',
                    'statut' => $facture->isReimbursed() ? 'remboursé' : 'en attente',
                    'validateur' => null,
                    'date' => null,
                    'motif_rejet' => null,
                ],
            ]
        ];

        return ApiResponse::success($historique, 'Historique de validation récupéré avec succès');
    }
}
