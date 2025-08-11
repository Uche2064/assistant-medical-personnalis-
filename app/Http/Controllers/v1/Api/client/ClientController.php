<?php

namespace App\Http\Controllers\v1\Api\client;

use App\Enums\TypeDemandeurEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPropositionContratEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DemandeAdhesion;
use App\Models\PropositionContrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    
    /**
     * Récupérer les contrats proposés pour un client
     */
    public function getContratsProposes()
    {
        try {
            $user = Auth::user();
            
            // Récupérer les propositions de contrats pour ce client
            $propositions = PropositionContrat::with([
                'demandeAdhesion.user.entreprise',
                'demandeAdhesion.user.assure',
                'contrat.categoriesGaranties.garanties',
            ])
            ->whereHas('demandeAdhesion', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('statut', StatutPropositionContratEnum::PROPOSEE->value)
            ->get();

            $contratsProposes = $propositions->map(function ($proposition) {
                // Grouper les garanties par catégorie
                $categoriesGaranties = $proposition->contrat->categoriesGaranties->groupBy('categorie_garantie_id')
                    ->map(function ($garanties, $categorieId) {
                        $categorie = $garanties->first()->categorieGarantie;
                        $garantiesList = $garanties->pluck('nom')->implode(', ');
                        
                        return [
                            'libelle' => $categorie->libelle,
                            'garanties' => $garantiesList
                        ];
                    })->values();

                return [
                    'proposition_id' => $proposition->id,
                    'contrat' => [
                        'id' => $proposition->contrat->id,
                        'nom' => $proposition->contrat->nom,
                        'type_contrat' => $proposition->contrat->type_contrat,
                        'description' => $proposition->contrat->description
                    ],
                    'details_proposition' => [
                        'prime_proposee' => $proposition->contrat->prime_standard,
                        'taux_couverture' => $proposition->contrat->taux_couverture,
                        'frais_gestion' => $proposition->contrat->frais_gestion,
                        'commentaires_technicien' => $proposition->commentaires_technicien,
                        'date_proposition' => $proposition->date_proposition
                    ],
                    'categories_garanties' => $categoriesGaranties,
                    'statut' => $proposition->statut?->value ?? $proposition->statut
                ];
            });

            return ApiResponse::success($contratsProposes, 'Contrats proposés récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats proposés', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des contrats proposés: ' . $e->getMessage(), 500);
        }
    }


     /**
     * Refuser une proposition de contrat
     */
    public function refuserContrat(Request $request, int $propositionId)
    {
        $validatedData = Validator::make($request->all(), [
            'raison_refus' => 'required|string|max:1000',
        ]);

        if($validatedData->fails()) {
            return ApiResponse::error('Erreur de validation: ' . $validatedData->errors()->first(), 422);
        }

        try {
            $user = Auth::user();
            
            // Récupérer la proposition
            $proposition = PropositionContrat::with([
                'demandeAdhesion.user',
                'contrat',
                'technicien.personnel'
            ])->find($propositionId);

            if(!$proposition) {
                return ApiResponse::error('Proposition de contrat non trouvée', 404);
            }

            // Vérifier que la proposition appartient à l'utilisateur connecté
            if ($proposition->demandeAdhesion->user_id !== $user->id) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Vérifier que la proposition est en statut PROPOSEE
            if ($proposition->statut !== StatutPropositionContratEnum::PROPOSEE->value) {
                return ApiResponse::error('Cette proposition a déjà été traitée', 400);
            }

            DB::beginTransaction();

            try {
                // 1. Mettre à jour la proposition
                $proposition->update([
                    'statut' => StatutPropositionContratEnum::REFUSEE->value,
                    'raison_refus' => $request->raison_refus,
                    'date_refus' => now()
                ]);

                // 2. Mettre à jour la demande d'adhésion (revenir en attente)
                $proposition->demandeAdhesion->update([
                    'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value
                ]);

                // 3. Notification au technicien
                $this->notificationService->createNotification(
                    $proposition->technicien->personnel->user_id,
                    'Proposition de contrat refusée',
                    "Le client {$proposition->demandeAdhesion->user->assure->nom} a refusé votre proposition de contrat.",
                    'contrat_refuse_technicien',
                    [
                        'client_nom' => $proposition->demandeAdhesion->user->nom,
                        'contrat_type' => $proposition->contrat->type_contrat,
                        'prime' => $proposition->prime_proposee,
                        'type' => 'contrat_refuse_technicien'
                    ]
                );

                DB::commit();

                return ApiResponse::success([
                    'proposition_id' => $proposition->id,
                    'message' => 'Proposition refusée avec succès'
                ], 'Proposition refusée avec succès');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du refus du contrat', [
                'error' => $e->getMessage(),
                'proposition_id' => $propositionId,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors du refus du contrat: ' . $e->getMessage(), 500);
        }
    }
} 