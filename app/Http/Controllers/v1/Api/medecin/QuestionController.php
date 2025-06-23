<?php

namespace App\Http\Controllers\v1\Api\medecin;

use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\medecin\QuestionsFormRequest;
use App\Http\Requests\medecin\QuestionsUpdateFormRequest;
use App\Http\Requests\medecin\QuestionsBulkInsertRequest;
use App\Http\Requests\medecin\QuestionsBulkUpdateRequest;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Liste toutes les questions.
     */
    public function index(Request $request)
    {
        $query = Question::query();

        // Filtres optionnels
        if ($request->has('destinataire')) {
            $query->where('destinataire', $request->destinataire);
        }

        if ($request->has('obligatoire')) {
            $query->where('obligatoire', $request->obligatoire == 'true');
        }

        $questions = $query->orderBy('id', 'asc')->get();

        return ApiResponse::success($questions, 'Questions récupérées avec succès');
    }

    /**
     * Récupère les questions pour les prospects physiques.
     */
    public function getProspectPhysiqueQuestions()
    {
        $questions = Question::where('destinataire', TypeDemandeurEnum::PROSPECT_PHYSIQUE->value)
            ->where('est_actif', true)
            ->orderBy('id', 'asc')
            ->get();

        return ApiResponse::success($questions, 'Questions pour prospects physiques récupérées avec succès');
    }

    public function getProspectMoralQuestions()
    {
        $questions = Question::where('destinataire', TypeDemandeurEnum::PROSPECT_MORAL->value)
            ->where('est_actif', true)
            ->orderBy('id', 'asc')
            ->get();

        return ApiResponse::success($questions, 'Questions pour prospects moral récupérées avec succès');
    }

    /**
     * Récupère une question par son ID.
     */
    public function show($id)
    {
        $question = Question::findOrFail($id);
        return ApiResponse::success($question, 'Question récupérée avec succès');
    }


    /**
     * Stocke plusieurs questions en une seule opération.
     * Utilise l'insertion en masse pour de meilleures performances.
     */
    public function bulkInsert(QuestionsBulkInsertRequest $request)
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
                    'type_donnees' => $questionData['type_donnees'],
                    'destinataire' => $questionData['destinataire'],
                    'obligatoire' => $questionData['obligatoire'] ?? false,
                    'est_actif' => $questionData['est_actif'] ?? true,
                    'options' => $questionData['options'] ?? null,
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
            return ApiResponse::success($createdQuestions, count($createdQuestions) . ' questions créées avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la création des questions: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Met à jour plusieurs questions en une seule opération.
     * Utilise des requêtes optimisées pour de meilleures performances.
     */
    public function bulkUpdate(QuestionsBulkUpdateRequest $request)
    {
        $data = $request->validated();
        $updatedIds = [];

        try {
            DB::beginTransaction();

            // Regrouper les mises à jour par champs communs pour réduire le nombre de requêtes
            $updateGroups = [];
            foreach ($data as $questionData) {
                $id = $questionData['id'];
                $updatedIds[] = $id;

                // Filtrer les données pour ne garder que les champs à mettre à jour
                $updateData = array_filter($questionData, function ($key) {
                    return $key !== 'id';
                }, ARRAY_FILTER_USE_KEY);

                // Créer une signature des champs à mettre à jour
                $updateSignature = md5(json_encode(array_keys($updateData)));

                if (!isset($updateGroups[$updateSignature])) {
                    $updateGroups[$updateSignature] = [
                        'fields' => $updateData,
                        'ids' => []
                    ];
                }

                $updateGroups[$updateSignature]['ids'][] = $id;
            }

            // Exécuter les mises à jour groupées
            foreach ($updateGroups as $group) {
                Question::whereIn('id', $group['ids'])->update($group['fields']);
            }

            // Récupérer toutes les questions mises à jour en une seule requête
            $updatedQuestions = Question::whereIn('id', $updatedIds)->get();

            DB::commit();
            return ApiResponse::success($updatedQuestions, count($updatedQuestions) . ' questions mises à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la mise à jour des questions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Supprime une question.
     */
    public function destroy($id)
    {
        $question = Question::find($id);
        if ($question == null) {
            return ApiResponse::error('Question non trouvée', 404);
        }
        $question->delete();
        $question->update([
            'est_actif' => false
        ]);

        return ApiResponse::success(null, 'Question supprimée avec succès');
    }

    /**
     * Supprime plusieurs questions en une seule opération (bulk delete).
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return ApiResponse::error('Aucun identifiant fourni pour la suppression.', 422);
        }

        $questions = Question::whereIn('id', $ids)->get();

        if ($questions->isEmpty()) {
            return ApiResponse::error('Aucune question trouvée pour les identifiants fournis.', 404);
        }

        foreach ($questions as $question) {
            // $question->delete();
            $question->update(['est_actif' => false]);
        }

        return ApiResponse::success(null, 'Questions supprimées avec succès');
    }
}
