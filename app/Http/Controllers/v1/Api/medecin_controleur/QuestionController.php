<?php

namespace App\Http\Controllers\v1\Api\medecin_controleur;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\medecin_controleur\QuestionsBulkInsertRequest;
use App\Http\Requests\medecin_controleur\QuestionUpdateFormRequest;
use App\Http\Requests\medecin_controleur\UpdateQuestionRequest;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    /**
     * Liste toutes les questions.
     */
    public function indexQuestions(Request $request)
    {
        $request->validate([
            'type_donnee' => 'sometimes|in:boolean,text,date,decimal,radio,file',
            'destinataire' => 'sometimes|string',
            'obligatoire' => 'sometimes|boolean',
            'est_actif' => 'sometimes|boolean',
        ]);

        $query = Question::query();
        $perPage = $request->input('per_page', 10);

        // 🔒 Restreindre à l’auteur actuel (médecin contrôleur)
        $query->where('cree_par_id', Auth::user()->personnel->id);

        if ($request->has('type_donnee')) {
            $query->where('type_donnee', $request->type_donnee);
        }

        if ($request->has('destinataire')) {
            $query->where('destinataire', $request->destinataire);
        }

        if ($request->has('obligatoire')) {
            $query->where('obligatoire', filter_var($request->obligatoire, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('est_actif')) {
            $query->where('est_actif', filter_var($request->est_actif, FILTER_VALIDATE_BOOLEAN));
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($questions, 'Questions récupérées avec succès');
    }

    /**
     * Récupère les questions pour les prospects physiques.
     */ public function getQuestionsByDestinataire(Request $request)
    {
        $request->validate([
            'destinataire' => ['required', Rule::in(TypeDemandeurEnum::values())] // ou une liste custom
        ]);

        $destinataire = $request->input('destinataire');

        $questions = Question::where('destinataire', $destinataire)
            ->where('est_actif', true)
            ->get();

        return ApiResponse::success($questions, "Questions pour le type [$destinataire] récupérées avec succès");
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
        return ApiResponse::success($question, 'Question récupérée avec succès');
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

    public function toggleQuestionStatus($id)
    {
        $question = Question::find($id);
        if (!$question) {
            return ApiResponse::error('Question non trouvée', 404);
        }

        $question->update(['est_actif' => !$question->est_actif]);

        $etat = $question->est_actif ? 'activée' : 'désactivée';

        return ApiResponse::success($question, "Question $etat avec succès.");
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
            return ApiResponse::success($question, 'Question mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la mise à jour de la question: ' . $e->getMessage(), 500);
        }
    }

}
