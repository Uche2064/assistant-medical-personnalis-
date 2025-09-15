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
            $query->whereIn('type_demandeur', [TypeDemandeurEnum::PHYSIQUE->value, TypeDemandeurEnum::ENTREPRISE->value]);
        } elseif ($user->hasRole('medecin_controleur')) {
            $query->whereIn('type_demandeur', TypePrestataireEnum::values());
        }

        return $query;
    }

    /**
     * Appliquer les filtres de statut sur les demandes d'adhésion
     */
    public function applyStatusFilters($query, Request $request)
    {
        $status = $request->input('statut');
        if ($status) {
            $query->where('statut', match ($status) {
                'en_attente' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'validee'    => StatutDemandeAdhesionEnum::VALIDEE->value,
                'rejetee'    => StatutDemandeAdhesionEnum::REJETEE->value,
                'proposee' => StatutDemandeAdhesionEnum::PROPOSEE->value,
                'acceptee' => StatutDemandeAdhesionEnum::ACCEPTEE->value,
                default      => null
            });
        }

        return $query;
    }

    public function applySearchFilter($query, $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                // User (nom/email)
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('email', 'like', "%{$search}%");
                })
                    // Assuré (nom/prenoms)
                    ->orWhereHas('user.assure', function ($a) use ($search) {
                        $a->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenoms', 'like', "%{$search}%");
                    })
                    // Entreprise (raison sociale)
                    ->orWhereHas('user.entreprise', function ($e) use ($search) {
                        $e->where('raison_sociale', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Vérifier si un fichier est uploadé
     */
    public function isUploadedFile($value): bool
    {
        return is_object($value) && method_exists($value, 'getClientOriginalName');
    }

    /**
     * Enregistrer une réponse de personne
     */
    public function enregistrerReponsePersonne($personneType, $personneId, array $reponseData, $demandeId): void
    {
        $question = Question::find($reponseData['question_id']);
        if (!$question) return;

        $data = [
            'question_id' => $question->id,
            'personne_type' => $personneType,
            'personne_id' => $personneId,
            'demande_adhesion_id' => $demandeId,
        ];

        switch ($question->type_donnee) {
            case TypeDonneeEnum::BOOLEAN:
                $data['reponse_bool'] = filter_var($reponseData['reponse_bool'] ?? false, FILTER_VALIDATE_BOOLEAN);
                break;
            case TypeDonneeEnum::NUMBER:
                $data['reponse_number'] = floatval($reponseData['reponse_number'] ?? 0);
                break;
            case TypeDonneeEnum::DATE:
                $data['reponse_date'] = $reponseData['reponse_date'] ?? null;
                break;
            case TypeDonneeEnum::FILE:
                if (isset($reponseData['reponse_fichier']) && $this->isUploadedFile($reponseData['reponse_fichier'])) {
                    $data['reponse_fichier'] = ImageUploadHelper::uploadImage($reponseData['reponse_fichier'], 'uploads/demandes_adhesion/' . $demandeId);
                }
                break;
            case TypeDonneeEnum::TEXT:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
            case TypeDonneeEnum::SELECT:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
            case TypeDonneeEnum::CHECKBOX:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
            case TypeDonneeEnum::RADIO:
            default:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
        }

        ReponseQuestionnaire::create($data);
    }

    /**
     * Enregistrer un bénéficiaire et ses réponses
     */
    public function enregistrerBeneficiaire($demande, array $beneficiaire, Assure $assurePrincipal): void
    {
        // Créer le bénéficiaire dans la table assures (PAS de compte utilisateur)
        $benefAssure = Assure::create([
            'user_id' => null,
            'assure_principal_id' => $assurePrincipal->id,
            'nom' => $beneficiaire['nom'],
            'prenoms' => $beneficiaire['prenoms'],
            'date_naissance' => $beneficiaire['date_naissance'],
            'sexe' => $beneficiaire['sexe'],
            'lien_parente' => $beneficiaire['lien_parente'],
            'est_principal' => false,
            'photo' => $beneficiaire['photo'] ?? null,
        ]);

        // Enregistrer les réponses du bénéficiaire
        foreach ($beneficiaire['reponses'] as $reponse) {
            $this->enregistrerReponsePersonne('beneficiaire', $benefAssure->id, $reponse, $demande->id);
        }
    }

    /**
     * Récupérer les contrats disponibles pour proposition
     */
    public function getContratsDisponibles()
    {
        try {
            $contrats = TypeContrat::with(['garanties.categorieGarantie'])
                ->where('est_actif', true)
                ->get()
                ->map(function ($contrat) {
                    // Grouper les garanties par catégorie
                    $categoriesGaranties = $contrat->garanties->groupBy('categorie_garantie_id')
                        ->map(function ($garanties, $categorieId) {
                            $categorie = $garanties->first()->categorieGarantie;
                            $garantiesList = $garanties->pluck('nom')->implode(', ');

                            return [
                                'id' => $categorie->id,
                                'libelle' => $categorie->nom,
                                'garanties' => $garantiesList
                            ];
                        })->values();

                    return [
                        'id' => $contrat->id,
                        'nom' => $contrat->nom,
                        'libelle' => $contrat->libelle,
                        'description' => $contrat->description,
                        'prime_de_base' => $contrat->prime_de_base,
                        'categories_garanties' => $categoriesGaranties
                    ];
                });

            return ApiResponse::success($contrats, 'Contrats disponibles récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats disponibles', [
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Erreur lors de la récupération des contrats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les liens d'invitation pour une entreprise
     */
    public function getLiensInvitation(User $user)
    {
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent consulter leurs liens d\'invitation.', 403);
        }

        $entreprise = $user->entreprise;

        $invitations = InvitationEmploye::with(['assure'])
            ->where('entreprise_id', $entreprise->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $liens = $invitations->map(function ($invitation) {
            return [
                'invitation_id' => $invitation->id,
                'employe_id' => $invitation->assure_id,
                'nom' => $invitation->assure ? $invitation->assure->nom . ' ' . $invitation->assure->prenoms : 'N/A',
                'email' => $invitation->email,
                'lien' => config('app.frontend_url') . "/employes/formulaire/{$invitation->token}",
                'token' => $invitation->token,
                'statut' => $invitation->expire_at > now() ? 'actif' : 'expire',
                'envoye_le' => $invitation->created_at,
                'expire_le' => $invitation->expire_at
            ];
        });

        return ApiResponse::success([
            'entreprise_id' => $entreprise->id,
            'raison_sociale' => $entreprise->raison_sociale,
            'total_liens' => $liens->count(),
            'liens_actifs' => $liens->where('statut', 'actif')->count(),
            'liens_expires' => $liens->where('statut', 'expire')->count(),
            'liens' => $liens
        ], 'Liens d\'invitation récupérés avec succès.');
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
     * Transformer une demande d'adhésion pour l'API
     */
    public function transformDemandeAdhesion($demande)
    {
        return [
            'id' => $demande->id,
            'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
            'statut' => $demande->statut?->value ?? $demande->statut,
            'created_at' => $demande->created_at,
            'updated_at' => $demande->updated_at,
            'motif_rejet' => $demande->motif_rejet,
            'valide_par' => $demande->validePar ? [
                'id' => $demande->validePar->id,
                'nom' => $demande->validePar->nom,
                'prenoms' => $demande->validePar->prenoms,
            ] : null,
            'valider_a' => $demande->valider_a,
        ];
    }

    /**
     * Notifier selon le type de demandeur
     */
    public function notifyByDemandeurType($demande, $typeDemandeur)
    {
        if ($typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value || $typeDemandeur === TypeDemandeurEnum::ENTREPRISE->value) {
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

        $demande->user->prestataire->update([
            'statut' => StatutPrestataireEnum::ACTIF->value,
            'medecin_controleur_id' => $validateur->id
        ]);

        // Notifier le client
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
            if (!in_array($typeDemandeur, [TypeDemandeurEnum::PHYSIQUE->value, TypeDemandeurEnum::ENTREPRISE->value])) {
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
