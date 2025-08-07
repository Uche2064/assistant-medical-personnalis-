<?php

namespace App\Http\Requests\demande_adhesion;

use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class SoumissionEmployeFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = [
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'date_naissance' => 'required|date|before:today',
            'sexe' => 'required|in:M,F',
            'contact' => 'nullable|string|max:30|unique:users,contact|unique:assures,contact',
            'profession' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
            'email' => 'required|email|max:255|unique:users,email|unique:assures,email',
            'reponses' => 'required|array|min:1',
            'reponses.*.question_id' => 'required|integer|exists:questions,id',
            // Les champs de réponse sont validés dynamiquement côté contrôleur selon le type de question
            
            // Bénéficiaires optionnels
            'beneficiaires' => 'nullable|array',
            'beneficiaires.*.nom' => 'required_with:beneficiaires|string|max:255',
            'beneficiaires.*.prenoms' => 'required_with:beneficiaires|string|max:255',
            'beneficiaires.*.date_naissance' => 'required_with:beneficiaires|date|before:today',
            'beneficiaires.*.sexe' => 'required_with:beneficiaires|in:M,F',
            'beneficiaires.*.lien_parente' => 'required_with:beneficiaires|string|max:255',
            'beneficiaires.*.photo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
            'beneficiaires.*.reponses' => 'nullable|array',
            'beneficiaires.*.reponses.*.question_id' => 'required_with:beneficiaires.*.reponses|integer|exists:questions,id',
        ];

        $questions = Question::forDestinataire('physique')->get()->keyBy('id');
        $questionIds = $questions->pluck('id')->toArray();
        foreach ($this->input('reponses', []) as $index => $reponse) {
            $questionId = $reponse['question_id'] ?? null;
            if (!$questionId || !$questions->has($questionId)) continue;
            $question = $questions->get($questionId);
            $ruleKey = 'reponses.' . $index;
            $required = $question->isRequired() ? 'required' : 'nullable';
            switch ($question->type_donnee) {
                case TypeDonneeEnum::TEXT:
                case TypeDonneeEnum::RADIO:
                    $rules[$ruleKey . '.reponse_text'] = [$required, 'string'];
                    break;
                case TypeDonneeEnum::NUMBER:
                    $rules[$ruleKey . '.reponse_number'] = [$required, 'numeric'];
                    break;
                case TypeDonneeEnum::BOOLEAN:
                    $rules[$ruleKey . '.reponse_bool'] = [$required, 'boolean'];
                    break;
                case TypeDonneeEnum::DATE:
                    $rules[$ruleKey . '.reponse_date'] = [$required, 'date'];
                    break;
                case TypeDonneeEnum::FILE:
                    $rules[$ruleKey . '.reponse_fichier'] = [$required, 'file', 'mimes:jpeg,png,pdf,jpg', 'max:2048'];
                    break;
            }
        }
        // Validation des réponses des bénéficiaires
        $beneficiaires = $this->input('beneficiaires', []);
        foreach ($beneficiaires as $beneficiaireIndex => $beneficiaire) {
            if (isset($beneficiaire['reponses']) && is_array($beneficiaire['reponses'])) {
                foreach ($beneficiaire['reponses'] as $reponseIndex => $reponse) {
                    $questionId = $reponse['question_id'] ?? null;
                    if (!$questionId || !$questions->has($questionId)) continue;
                    
                    $question = $questions->get($questionId);
                    $ruleKey = "beneficiaires.{$beneficiaireIndex}.reponses.{$reponseIndex}";
                    $required = $question->isRequired() ? 'required' : 'nullable';
                    
                    switch ($question->type_donnee) {
                        case TypeDonneeEnum::TEXT:
                        case TypeDonneeEnum::RADIO:
                            $rules[$ruleKey . '.reponse_text'] = [$required, 'string'];
                            break;
                        case TypeDonneeEnum::NUMBER:
                            $rules[$ruleKey . '.reponse_number'] = [$required, 'numeric'];
                            break;
                        case TypeDonneeEnum::BOOLEAN:
                            $rules[$ruleKey . '.reponse_bool'] = [$required, 'boolean'];
                            break;
                        case TypeDonneeEnum::DATE:
                            $rules[$ruleKey . '.reponse_date'] = [$required, 'date'];
                            break;
                        case TypeDonneeEnum::FILE:
                            $rules[$ruleKey . '.reponse_fichier'] = [$required, 'file', 'mimes:jpeg,png,pdf,jpg', 'max:2048'];
                            break;
                    }
                }
            }
        }
        Log::info($rules);

        return $rules;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenoms.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            'contact.unique' => 'Le contact est déjà utilisé.',
            'email.unique' => 'L\'email est déjà utilisé.',
            'reponses.required' => 'Le questionnaire médical est obligatoire.',
            'reponses.array' => 'Le questionnaire médical doit être un tableau.',
            'reponses.*.question_id.required' => 'Chaque réponse doit référencer une question.',
            'reponses.*.question_id.exists' => 'Une des questions n\'existe pas.',
            
            // Messages pour les bénéficiaires
            'beneficiaires.array' => 'Les bénéficiaires doivent être un tableau.',
            'beneficiaires.*.nom.required_with' => 'Le nom du bénéficiaire est obligatoire.',
            'beneficiaires.*.prenoms.required_with' => 'Le prénom du bénéficiaire est obligatoire.',
            'beneficiaires.*.date_naissance.required_with' => 'La date de naissance du bénéficiaire est obligatoire.',
            'beneficiaires.*.date_naissance.date' => 'La date de naissance du bénéficiaire doit être une date valide.',
            'beneficiaires.*.date_naissance.before' => 'La date de naissance du bénéficiaire doit être antérieure à aujourd\'hui.',
            'beneficiaires.*.sexe.required_with' => 'Le sexe du bénéficiaire est obligatoire.',
            'beneficiaires.*.sexe.in' => 'Le sexe du bénéficiaire doit être M ou F.',
            'beneficiaires.*.lien_parente.required_with' => 'Le lien de parenté du bénéficiaire est obligatoire.',
            'beneficiaires.*.photo.file' => 'La photo du bénéficiaire doit être un fichier.',
            'beneficiaires.*.photo.mimes' => 'La photo du bénéficiaire doit être au format JPG, JPEG, PNG, GIF ou WEBP.',
            'beneficiaires.*.reponses.array' => 'Les réponses du bénéficiaire doivent être un tableau.',
            'beneficiaires.*.reponses.*.question_id.required_with' => 'Chaque réponse du bénéficiaire doit référencer une question.',
            'beneficiaires.*.reponses.*.question_id.exists' => 'Une des questions du bénéficiaire n\'existe pas.',
        ];
    }
}
