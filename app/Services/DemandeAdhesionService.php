<?php

namespace App\Services;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePrestataireEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\StatutPropositionContratEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Models\Assure;
use App\Models\TypeContrat;
use App\Models\DemandeAdhesion;
use App\Models\InvitationEmploye;
use App\Models\LienInvitation;
use App\Models\Question;
use App\Models\ReponseQuestionnaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemandeAdhesionService
{
    protected NotificationService $notificationService;
    protected DemandeValidatorService $demandeValidatorService;
    protected DemandeAdhesionStatsService $statsService;



    public function __construct(
        NotificationService $notificationService,
        DemandeValidatorService $demandeValidatorService,
        DemandeAdhesionStatsService $statsService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeValidatorService = $demandeValidatorService;
        $this->statsService = $statsService;
    }


    /**
     * Appliquer les filtres de rôle sur les demandes d'adhésion
     */
    public function applyRoleFilters($query, User $user)
    {
        if ($user->hasRole('technicien')) {
            $query->whereIn('type_demandeur', [TypeDemandeurEnum::CLIENT->value]);
        } elseif ($user->hasRole('medecin_controleur')) {
            $query->whereIn('type_demandeur', TypePrestataireEnum::values());
        } else {
            $query;
        }

        return $query;
    }


    /**
     * Vérifier si un fichier est uploadé
     */
    public function isUploadedFile($value): bool
    {
        return is_object($value) && method_exists($value, 'getClientOriginalName');
    }


    /**
     * Récupérer les statistiques des demandes d'adhésion
     */
    public function getStats(User $user)
    {
        $driver = DB::getDriverName();

        $query = DemandeAdhesion::query();
        $monthExpression = match ($driver) {
            'pgsql' => "EXTRACT(MONTH FROM created_at)",
            'mysql' => "MONTH(created_at)",
            default => "MONTH(created_at)" // fallback
        };

        // Appliquer les filtres de rôle
        // $this->applyRoleFilters($query, $user);

        $stats = [
            'total' => $query->count(),
            'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
            'validees' => (clone $query)->where('statut', 'validee')->count(),
            'rejetees' => (clone $query)->where('statut', 'rejetee')->count(),
            'proposees' => (clone $query)->where('statut', StatutPropositionContratEnum::PROPOSEE->value)->count(),
            'acceptees' => (clone $query)->where('statut', 'acceptee')->count(),
            'repartition_par_type_demandeur' => (clone $query)->selectRaw('type_demandeur, COUNT(*) as count')
                ->groupBy('type_demandeur')
                ->get()
                ->mapWithKeys(function ($item) {
                    $typeDemandeur = $item->type_demandeur?->value ?? $item->type_demandeur;
                    return [(string) $typeDemandeur => $item->count];
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques récupérées avec succès');
    }


    /**
     * Notifier selon le type de demandeur
     */
    public function notifyByDemandeurType($demande, $typeDemandeur)
    {
        if ($typeDemandeur === TypeDemandeurEnum::CLIENT->value) {
            // Notifier les techniciens pour les demandes physiques et entreprises
            $this->notificationService->notifyTechniciensNouvelleDemande($demande);

        } else {

            // Notifier les médecins contrôleurs pour les demandes prestataires
            $this->notificationService->notifyMedecinsControleursDemandePrestataire($demande);
        }
    }

    /**
     * Valider une demande d'adhésion
     */
    public function validerDemande($demande, $validateur, $motifValidation = null, $notesTechniques = null)
    {
        $demande->update([
            'statut' => StatutDemandeAdhesionEnum::VALIDEE,
            'valide_par_id' => $validateur->id,
            'valider_a' => now(),
            'motif_validation' => $motifValidation,
            'notes_techniques' => $notesTechniques,
        ]);

        // Si c'est un prestataire, mettre à jour son statut
        if ($demande->type_demandeur === TypeDemandeurEnum::PRESTATAIRE && $demande->user->prestataire) {
        $demande->user->prestataire->update([
            'statut' => StatutPrestataireEnum::ACTIF->value,
            'medecin_controleur_id' => $validateur->id
        ]);
        }

        // Notifier le client/prestataire
        $this->notificationService->createNotification(
            $demande->user->id,
            'Demande d\'adhésion validée',
            "Votre demande d'adhésion a été validée par notre équipe technique. Vous pouvez maintenant procéder à la souscription.",
            'demande_validee',
            [
                'demande_id' => $demande->id,
                'valide_par' => $validateur->nom . ' ' . ($validateur->prenoms ?? ''),
                'date_validation' => now()->format('d/m/Y à H:i'),
                'motif_validation' => $motifValidation ?? null,
                'type' => 'demande_validee'
            ]
        );

        return $demande;
    }

    /**
     * Rejeter une demande d'adhésion
     */
    public function rejeterDemande($demande, $rejeteur, $motifRejet, $notesTechniques = null)
    {
        $demande->update([
            'statut' => StatutDemandeAdhesionEnum::REJETEE,
            'rejetee_par_id' => $rejeteur->id,
            'rejetee_a' => now(),
            'motif_rejet' => $motifRejet,
            'notes_techniques' => $notesTechniques,
        ]);

        // Notifier le client
        $this->notificationService->createNotification(
            $demande->user->id,
            'Demande d\'adhésion rejetée',
            "Votre demande d'adhésion a été rejetée. Consultez votre email pour plus de détails.",
            'demande_rejetee',
            [
                'demande_id' => $demande->id,
                'motif_rejet' => $motifRejet,
                'rejetee_par' => $rejeteur->nom . ' ' . ($rejeteur->prenoms ?? ''),
                'date_rejet' => now()->format('d/m/Y à H:i'),
                'type' => 'demande_rejetee'
            ]
        );

        return $demande;
    }

    /**
     * Vérifier les permissions d'accès à une demande
     */
    public function checkDemandeAccess($demande, User $user)
    {
        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        // Vérifier les permissions selon le rôle
        if ($user->hasRole('technicien')) {
            $typeDemandeur = $demande->type_demandeur?->value ?? $demande->type_demandeur;
            if (!in_array($typeDemandeur, [TypeDemandeurEnum::CLIENT->value])) {
                return ApiResponse::error('Accès non autorisé', 403);
            }
        } elseif ($user->hasRole('medecin_controleur')) {
            $typeDemandeur = $demande->type_demandeur?->value ?? $demande->type_demandeur;
            if (!in_array($typeDemandeur, TypePrestataireEnum::values())) {
                return ApiResponse::error('Accès non autorisé', 403);
            }
        }

        return null; // Pas d'erreur
    }
}
