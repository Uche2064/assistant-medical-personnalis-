<?php

namespace App\Http\Controllers\v1\Api\medecin_controleur;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\medecin_controleur\QuestionsBulkInsertRequest;
use App\Http\Requests\medecin_controleur\UpdateQuestionRequest;
use App\Models\Question;
use App\Http\Resources\QuestionResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    /**
     * Liste toutes les questions.
     */
    public function indexQuestions(Request $request)
    {
        $destinataire = $request->query('destinataire');
    
        $query = Question::with('creeePar.user.personne')->orderBy('created_at', 'desc');
    
        if ($destinataire) {
            $query->where('destinataire', $destinataire)
                  ->where('est_active', true);
        }
    
        $questions = $query->get();
    
        return ApiResponse::success(
            QuestionResource::collection($questions),
            $destinataire
                ? "Questions pour le destinataire $destinataire récupérées avec succès"
                : "Toutes les questions récupérées avec succès"
        );
    }
    

    /**
     * Récupère une question par son ID.
     */
    public function showQuestion($id)
    {
        $question = Question::find($id);
        if (!$question) {
            return ApiResponse::error('Question non trouvée', 404);
        }
        return ApiResponse::success(new QuestionResource($question), 'Question récupérée avec succès');
    }


    /**
     * Stocke plusieurs questions en une seule opération.
     * Utilise l'insertion en masse pour de meilleures performances.
     */
    public function bulkInsertQuestions(QuestionsBulkInsertRequest $request)
    {
        $data = $request->validated();
        $personnelId = Auth::user()->personnel->id;
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            // Préparer les données pour l'insertion en masse
            $questionsToInsert = [];
            foreach ($data as $questionData) {
                $questionsToInsert[] = [
                    'libelle' => $questionData['libelle'],
                    'type_de_donnee' => $questionData['type_de_donnee'],
                    'destinataire' => $questionData['destinataire'],
                    'est_obligatoire' => $questionData['obligatoire'] ?? false,
                    'est_active' => $questionData['est_active'] ?? true,
                    'options' => isset($questionData['options']) ? json_encode($questionData['options']) : null,
                    'cree_par_id' => $personnelId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            Question::insert($questionsToInsert);

            // Récupérer les questions insérées pour la réponse
            // Utiliser orderBy et limit pour s'assurer de récupérer les bonnes questions
            $createdQuestions = Question::where('cree_par_id', $personnelId)
                ->where('created_at', $now)
                ->orderBy('id', 'desc')
                ->limit(count($questionsToInsert))
                ->get();

            DB::commit();
            return ApiResponse::success(QuestionResource::collection($createdQuestions), count($createdQuestions) . ' questions créées avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la création des questions: ', 500, $e->getMessage());
        }
    }



    /*
        Mise à jour d'une question spécifique
    */

    public function updateQuestion(UpdateQuestionRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $question = Question::find($id);
            if (!$question) {
                return ApiResponse::error('Question non trouvée', 404);
            }

            $question->update($data);

            DB::commit();
            return ApiResponse::success(new QuestionResource($question), 'Question mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la mise à jour de la question: ' . $e->getMessage(), 500);
        }
    }

    // public function toggleQuestionStatus($id)
    // {
    //     $question = Question::find($id);
    //     if (!$question) {
    //         return ApiResponse::error('Question non trouvée', 404);
    //     }

    //     $question->update(['est_active' => !$question->est_active]);

    //     $etat = $question->est_active ? 'activée' : 'désactivée';

    //     return ApiResponse::success(null, "Question $etat avec succès.");
    // }


    /**
     * Supprimer une question (hard delete)
     */
    public function destroyQuestion($id)
    {   Log::info("Suppression d'une question - ID: {$id}");
        $question = Question::find($id);
        if (!$question) {
            return ApiResponse::error('Question non trouvée', 404);
        }
        $question->delete();
        Log::info("Question supprimée - ID: {$id}");
        return ApiResponse::success(null, 'Question supprimée avec succès', 200);
    }

    /**
     * Suppression en masse de questions
     * @param Request $request (attend un tableau d'ids)
     */
    public function bulkDestroyQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:questions,id',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Erreur de validation', 422, $validator->errors());
        }
        $ids = $request->input('ids');
        $deleted = Question::whereIn('id', $ids)->delete();
        Log::info("Suppression en masse de questions - IDs: [" . implode(',', $ids) . "]");
        return ApiResponse::success(null, "$deleted questions supprimées avec succès");
    }

    /**
     * Statistiques complètes du médecin contrôleur
     */
    public function medecinControleurStats()
    {
        try {
            // Statistiques des questions
            $statsQuestions = $this->getStatsQuestions();
            
            // Statistiques des garanties
            $statsGaranties = $this->getStatsGaranties();
            
            // Statistiques des catégories de garanties
            $statsCategoriesGaranties = $this->getStatsCategoriesGaranties();
            
            // Statistiques des demandes prestataires
            $statsDemandesPrestataires = $this->getStatsDemandesPrestataires();
            
            // Statistiques des factures à valider
            $statsFactures = $this->getStatsFactures();
            
            // Évolutions mensuelles (pour graphiques)
            $evolutionsMensuelles = $this->getEvolutionsMensuelles();
            
            // Top garanties par montant (pour graphiques)
            $topGaranties = $this->getTopGaranties();
            
            // Répartition des garanties par catégorie (pour graphiques)
            $garantiesParCategorie = $this->getGarantiesParCategorie();

            return ApiResponse::success([
                'questions' => $statsQuestions,
                'garanties' => $statsGaranties,
                'categories_garanties' => $statsCategoriesGaranties,
                'demandes_prestataires' => $statsDemandesPrestataires,
                'factures' => $statsFactures,
                'evolutions_mensuelles' => $evolutionsMensuelles,
                'top_garanties' => $topGaranties,
                'garanties_par_categorie' => $garantiesParCategorie
            ], 'Statistiques du médecin contrôleur récupérées avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des statistiques', 500, $e->getMessage());
        }
    }

    /**
     * Statistiques détaillées des questions
     */
    private function getStatsQuestions()
    {
        $questions = Question::all();
        $total = $questions->count();

        // Répartition par destinataire
        $parDestinataire = $questions->groupBy(function ($question) {
            return $question->destinataire?->value ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                'actives' => $group->where('est_active', true)->count(),
                'inactives' => $group->where('est_active', false)->count()
            ];
        });

        // Répartition par type de données
        $parTypeDonnee = $questions->groupBy(function ($question) {
            return $question->type_de_donnee?->value ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        });

        return [
            'total' => $total,
            'actives' => $questions->where('est_active', true)->count(),
            'inactives' => $questions->where('est_active', false)->count(),
            'obligatoires' => $questions->where('est_obligatoire', true)->count(),
            'optionnelles' => $questions->where('est_obligatoire', false)->count(),
            'taux_activation' => $total > 0 ? round(($questions->where('est_active', true)->count() / $total) * 100, 2) : 0,
            'repartition_par_destinataire' => $parDestinataire,
            'repartition_par_type_donnee' => $parTypeDonnee
        ];
    }

    /**
     * Statistiques des garanties
     */
    private function getStatsGaranties()
    {
        $garanties = \App\Models\Garantie::all();
        $total = $garanties->count();

        return [
            'total' => $total,
            'actives' => $garanties->where('est_active', true)->count(),
            'inactives' => $garanties->where('est_active', false)->count(),
            'taux_activation' => $total > 0 ? round(($garanties->where('est_active', true)->count() / $total) * 100, 2) : 0,
            'plafond_total' => $garanties->sum('plafond'),
            'plafond_moyen' => $total > 0 ? round($garanties->avg('plafond'), 2) : 0,
            'prix_standard_total' => $garanties->sum('prix_standard'),
            'prix_standard_moyen' => $total > 0 ? round($garanties->avg('prix_standard'), 2) : 0,
            'taux_couverture_moyen' => $total > 0 ? round($garanties->avg('taux_couverture'), 2) : 0,
            'garantie_plafond_max' => $garanties->sortByDesc('plafond')->first() ? [
                'libelle' => $garanties->sortByDesc('plafond')->first()->libelle,
                'plafond' => $garanties->sortByDesc('plafond')->first()->plafond
            ] : null,
            'garantie_plafond_min' => $garanties->sortBy('plafond')->first() ? [
                'libelle' => $garanties->sortBy('plafond')->first()->libelle,
                'plafond' => $garanties->sortBy('plafond')->first()->plafond
            ] : null
        ];
    }

    /**
     * Statistiques des catégories de garanties
     */
    private function getStatsCategoriesGaranties()
    {
        $categories = \App\Models\CategorieGarantie::withCount('garanties')->get();
        $total = $categories->count();

        return [
            'total' => $total,
            'avec_garanties' => $categories->where('garanties_count', '>', 0)->count(),
            'sans_garanties' => $categories->where('garanties_count', 0)->count(),
            'nombre_moyen_garanties' => $total > 0 ? round($categories->avg('garanties_count'), 2) : 0,
            'categorie_plus_fournie' => $categories->sortByDesc('garanties_count')->first() ? [
                'nom' => $categories->sortByDesc('garanties_count')->first()->nom,
                'nombre_garanties' => $categories->sortByDesc('garanties_count')->first()->garanties_count
            ] : null
        ];
    }

    /**
     * Statistiques des demandes d'adhésion prestataires
     */
    private function getStatsDemandesPrestataires()
    {
        $demandes = \App\Models\DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE)->get();
        $total = $demandes->count();

        $parStatut = $demandes->groupBy(function ($demande) {
            return $demande->statut?->value ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        });

        return [
            'total' => $total,
            'en_attente' => $demandes->where('statut.value', 'en_attente')->count(),
            'validees' => $demandes->where('statut.value', 'validee')->count(),
            'rejetees' => $demandes->where('statut.value', 'rejetee')->count(),
            'repartition_par_statut' => $parStatut,
            'nouvelles_ce_mois' => $demandes->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()
        ];
    }

    /**
     * Statistiques des factures à valider
     */
    private function getStatsFactures()
    {
        $factures = \App\Models\Facture::all();
        $total = $factures->count();

        // Factures validées par technicien mais pas encore par médecin
        $aValiderParMedecin = $factures->filter(function ($facture) {
            return $facture->isValidatedByTechnicien() && !$facture->isValidatedByMedecin();
        })->count();

        // Factures validées par médecin
        $valideesParMedecin = $factures->filter(function ($facture) {
            return $facture->isValidatedByMedecin();
        })->count();

        return [
            'total' => $total,
            'a_valider_par_medecin' => $aValiderParMedecin,
            'validees_par_medecin' => $valideesParMedecin,
            'en_attente_technicien' => $factures->filter(function ($facture) {
                return !$facture->isValidatedByTechnicien();
            })->count()
        ];
    }

    /**
     * Statistiques des questions (ancienne méthode conservée pour compatibilité)
     */
    public function questionStats()
    {
        $stats = [
            'total' => Question::count(),
            
            'actives' => Question::where('est_active', true)->count(),
            
            'inactives' => Question::where('est_active', false)->count(),
            
            'obligatoires' => Question::where('est_obligatoire', true)->count(),
            
            'optionnelles' => Question::where('est_obligatoire', false)->count(),
            
            // Gestion des valeurs nulles
            'repartition_par_destinataire' => Question::selectRaw('destinataire, COUNT(*) as count')
                ->groupBy('destinataire')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [TypeDemandeurEnum::getLabelKey($item->destinataire->value) ?? 'Non spécifié' => $item->count];
                }),           
        ];

        return ApiResponse::success($stats, 'Statistiques des questions récupérées avec succès');
    }

    /**
     * Évolution mensuelle des demandes d'adhésion prestataires (12 derniers mois) - Pour graphiques
     */
    private function getEvolutionsMensuelles()
    {
        $evolution = [];
        $maintenant = now();
        
        for ($i = 11; $i >= 0; $i--) {
            $date = $maintenant->copy()->subMonths($i);
            $moisDebut = $date->copy()->startOfMonth();
            $moisFin = $date->copy()->endOfMonth();
            
            // Demandes prestataires ce mois
            $demandesCeMois = \App\Models\DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE)
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->count();
            
            $demandesEnAttenteCeMois = \App\Models\DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE)
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('statut', \App\Enums\StatutDemandeAdhesionEnum::EN_ATTENTE)
                ->count();
            
            $demandesValideesCeMois = \App\Models\DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE)
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('statut', \App\Enums\StatutDemandeAdhesionEnum::VALIDEE)
                ->count();
            
            $demandesRejeteesCeMois = \App\Models\DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE)
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('statut', \App\Enums\StatutDemandeAdhesionEnum::REJETEE)
                ->count();
            
            // Factures validées par médecin ce mois
            $facturesValideesCeMois = \App\Models\Facture::whereBetween('valide_par_medecin_a', [$moisDebut, $moisFin])
                ->whereNotNull('valide_par_medecin_a')
                ->count();
            
            $evolution[] = [
                'mois' => $date->format('Y-m'),
                'mois_nom' => $date->format('M Y'),
                'mois_complet' => $date->format('F Y'),
                'demandes_recues' => $demandesCeMois,
                'demandes_en_attente' => $demandesEnAttenteCeMois,
                'demandes_validees' => $demandesValideesCeMois,
                'demandes_rejetees' => $demandesRejeteesCeMois,
                'factures_validees' => $facturesValideesCeMois,
                'taux_validation' => $demandesCeMois > 0 
                    ? round(($demandesValideesCeMois / $demandesCeMois) * 100, 2) 
                    : 0,
                'taux_rejet' => $demandesCeMois > 0 
                    ? round(($demandesRejeteesCeMois / $demandesCeMois) * 100, 2) 
                    : 0
            ];
        }
        
        return $evolution;
    }

    /**
     * Top 10 garanties par plafond - Pour graphiques
     */
    private function getTopGaranties()
    {
        return \App\Models\Garantie::where('est_active', true)
            ->orderBy('plafond', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($garantie, $index) {
                return [
                    'position' => $index + 1,
                    'id' => $garantie->id,
                    'libelle' => $garantie->libelle,
                    'plafond' => $garantie->plafond,
                    'prix_standard' => $garantie->prix_standard,
                    'taux_couverture' => $garantie->taux_couverture,
                    'montant_couverture' => $garantie->prix_standard * ($garantie->taux_couverture / 100),
                    'est_active' => $garantie->est_active
                ];
            });
    }

    /**
     * Répartition des garanties par catégorie - Pour graphiques
     */
    private function getGarantiesParCategorie()
    {
        $categories = \App\Models\CategorieGarantie::with('garanties')->get();
        
        return $categories->map(function ($categorie) {
            $garantiesActives = $categorie->garanties->where('est_active', true)->count();
            $plafondTotal = $categorie->garanties->sum('plafond');
            $prixStandardTotal = $categorie->garanties->sum('prix_standard');
            
            return [
                'id' => $categorie->id,
                'nom' => $categorie->nom,
                'nombre_garanties' => $categorie->garanties->count(),
                'garanties_actives' => $garantiesActives,
                'garanties_inactives' => $categorie->garanties->count() - $garantiesActives,
                'plafond_total' => $plafondTotal,
                'plafond_moyen' => $categorie->garanties->count() > 0 
                    ? round($plafondTotal / $categorie->garanties->count(), 2) 
                    : 0,
                'prix_standard_total' => $prixStandardTotal,
                'prix_standard_moyen' => $categorie->garanties->count() > 0 
                    ? round($prixStandardTotal / $categorie->garanties->count(), 2) 
                    : 0
            ];
        });
    }
}
