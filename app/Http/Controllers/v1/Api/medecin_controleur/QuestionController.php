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
    $perPage = $request->input('per_page', 10);

    $query = Question::query()
        ->when($request->filled('type_donnee'), fn($q) => $q->where('type_donnee', $request->type_donnee))
        ->when($request->filled('destinataire'), fn($q) => $q->where('destinataire', $request->destinataire))
        ->when($request->has('obligatoire'), fn($q) => 
            $q->where('obligatoire', filter_var($request->obligatoire, FILTER_VALIDATE_BOOLEAN))
        )
        ->when($request->has('est_actif'), fn($q) => 
            $q->where('est_actif', filter_var($request->est_actif, FILTER_VALIDATE_BOOLEAN))
        )
        ->orderBy('created_at', 'desc');

    $questions = $query->paginate($perPage);

    return ApiResponse::success(
        QuestionResource::collection($questions), 
        'Questions récupérées avec succès'
    );
}


    public function getQuestionsByDestinataire(Request $request)
    {

        Log::info('getQuestionsByDestinataire', ['request' => $request->all()]);
        $destinataire = $request->query('destinataire');

        $questions = Question::where('destinataire', $destinataire)
            ->where('est_actif', true)
            ->get();

        Log::info('questions', ['questions' => $questions]);
        return ApiResponse::success(QuestionResource::collection($questions), "Questions pour le type $destinataire récupérées avec succès");
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
                    'type_donnee' => $questionData['type_donnee'],
                    'destinataire' => $questionData['destinataire'],
                    'obligatoire' => $questionData['obligatoire'] ?? false,
                    'est_actif' => $questionData['est_actif'] ?? true,
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
            return ApiResponse::success(null, 'Question mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la mise à jour de la question: ' . $e->getMessage(), 500);
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

        return ApiResponse::success(null, "Question $etat avec succès.");
    }


    /**
     * Supprimer une question (hard delete)
     */
    public function destroyQuestion($id)
    {
        $question = Question::find($id);
        if (!$question) {
            return ApiResponse::error('Question non trouvée', 404);
        }
        $question->delete();
        Log::info("Question supprimée - ID: {$id}");
        return ApiResponse::success(null, 'Question supprimée avec succès', 204);
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
     * Statistiques des questions
     */
    public function questionStats()
    {
        $stats = [
            'total' => Question::count(),
            
            'actives' => Question::where('est_actif', true)->count(),
            
            'inactives' => Question::where('est_actif', false)->count(),
            
            'obligatoires' => Question::where('obligatoire', true)->count(),
            
            'optionnelles' => Question::where('obligatoire', false)->count(),
            
            // Gestion des valeurs nulles
            'repartition_par_destinataire' => Question::selectRaw('destinataire, COUNT(*) as count')
                ->groupBy('destinataire')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [TypeDemandeurEnum::getLabelKey($item->destinataire->value) ?? 'Non spécifié' => $item->count];
                }),
            
            'repartition_par_type_donnee' => Question::selectRaw('type_donnee, COUNT(*) as count')
                ->groupBy('type_donnee')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [TypeDonneeEnum::getLabelKey($item->type_donnee->value) ?? 'Non spécifié' => $item->count];
                }),
           
        ];

        return ApiResponse::success($stats, 'Statistiques des questions récupérées avec succès');
    }
}
