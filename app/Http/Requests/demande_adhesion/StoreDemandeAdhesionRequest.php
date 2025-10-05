<?php

namespace App\Http\Requests\demande_adhesion;

use App\Enums\LienParenteEnum;
use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDemandeAdhesionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole([RoleEnum::PRESTATAIRE->value, RoleEnum::CLIENT->value]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        // Define a base set of rules
        $rules = [
            'type_demandeur' => 'required|in:' . implode(',', TypeDemandeurEnum::values()),
        ];

        $typeDemandeur = $this->input('type_demandeur');
        
        // If the applicant is a 'prestataire', we must validate their specific type first.
        // We return these rules early to prevent the database query from failing if 'type_prestataire' is missing.
        if ($typeDemandeur === TypeDemandeurEnum::PRESTATAIRE->value) {
            $rules['type_prestataire'] = 'required|in:' . implode(',', TypePrestataireEnum::values());
            
            // If the request data does not contain a valid 'type_prestataire', stop here.
            // This is the key fix to prevent the "null given" error.
            if (!$this->has('type_prestataire') || !in_array($this->input('type_prestataire'), TypePrestataireEnum::values())) {
                return $rules;
            }
        }
        
        // Determine the recipient for dynamic questions.
        // If it's a 'prestataire', the recipient is their specific type, otherwise it's the 'demandeur' type.
        $destinataire = ($typeDemandeur === TypeDemandeurEnum::PRESTATAIRE->value)
                        ? $this->input('type_prestataire')
                        : $typeDemandeur;

        // Get the questions from the database based on the determined recipient.
        $questions = Question::forDestinataire($destinataire)->get()->keyBy('id');
        $questionIds = $questions->pluck('id')->toArray();
        
        // Add dynamic rules for both "client" and "prestataire" applications.
        $rules = array_merge($rules, [
            'reponses' => ['required', 'array'],
            'reponses.*.question_id' => ['required', Rule::in($questionIds)],
            'beneficiaires' => ['nullable', 'array'],
            'beneficiaires.*.nom' => ['required', 'string'],
            'beneficiaires.*.prenoms' => ['nullable', 'string'],
            'beneficiaires.*.date_naissance' => ['required', 'date'],
            'beneficiaires.*.sexe' => ['required', 'in:M,F'],
            'beneficiaires.*.email' => ['nullable', 'email'],
            'beneficiaires.*.contact' => ['nullable', 'string'],
            'beneficiaires.*.profession' => ['nullable', 'string'],
            'beneficiaires.*.photo_url' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'beneficiaires.*.lien_parente' => ['required', 'in:' . implode(',', LienParenteEnum::values())],
            'beneficiaires.*.reponses' => ['required', 'array'],
        ]);
        
        // Dynamically add validation for each question's response
        foreach ($this->input('reponses', []) as $index => $reponse) {
            $questionId = $reponse['question_id'] ?? null;
            if (!$questionId || !$questions->has($questionId)) {
                continue;
            }

            $question = $questions->get($questionId);
            $ruleKey = 'reponses.' . $index;
            $required = $question->isRequired() ? 'required' : 'nullable';

            switch ($question->type_de_donnee) {
                case TypeDonneeEnum::TEXT:
                case TypeDonneeEnum::RADIO:
                    $rules[$ruleKey . '.reponse'] = [$required, 'string'];
                    break;
                case TypeDonneeEnum::NUMBER:
                    $rules[$ruleKey . '.reponse'] = [$required, 'numeric'];
                    break;
                case TypeDonneeEnum::BOOLEAN:
                    $rules[$ruleKey . '.reponse'] = [$required, 'boolean'];
                    break;
                case TypeDonneeEnum::DATE:
                    $rules[$ruleKey . '.reponse'] = [$required, 'date'];
                    break;
                case TypeDonneeEnum::FILE:
                    $rules[$ruleKey . '.reponse'] = [$required, 'file', 'mimes:jpeg,png,pdf,jpg', 'max:2048'];
                    break;
            }
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type_demandeur.required' => 'Le champ type_demandeur est obligatoire.',
            'type_prestataire.required' => 'Le champ type_prestataire est obligatoire.',
            'type_prestataire.in' => 'Le type de prestataire choix est invalide ',
            'type_demandeur.in' => 'Le type de demandeur sélectionné est invalide.',
            'reponses.required' => 'Les réponses au questionnaire sont requises.',
            'reponses.array' => 'Les réponses au questionnaire doivent être un tableau.',
            'reponses.*.question_id.required' => 'L\'ID de la question est requis.',
            'reponses.*.question_id.in' => 'L\'ID de la question doit être un ID valide.',
            'reponses.*.reponse.required' => 'Cette réponse est obligatoire.',
            'reponses.*.reponse.string' => 'Cette réponse doit être une chaîne de caractères.',
            'reponses.*.reponse.numeric' => 'Cette réponse doit être un nombre.',
            'reponses.*.reponse.boolean' => 'Cette réponse doit être un booléen.',
            'reponses.*.reponse.date' => 'Cette réponse doit être une date valide.',
            'reponses.*.reponse.file' => 'Cette réponse doit être un fichier.',
            'reponses.*.reponse.mimes' => 'Le fichier doit être de type :values.',
            'reponses.*.reponse.max' => 'La taille du fichier ne doit pas dépasser 2048 Ko.',
            'beneficiaires.array' => 'Les bénéficiaires doivent être un tableau.',
            'beneficiaires.*.nom.required' => 'Le nom du bénéficiaire est requis.',
            'beneficiaires.*.date_naissance.required' => 'La date de naissance du bénéficiaire est requise.',
            'beneficiaires.*.sexe.required' => 'Le sexe du bénéficiaire est requis.',
            'beneficiaires.*.photo_url.required' => 'La photo du bénéficiaire est requise.',
            'beneficiaires.*.photo_url.mimes' => 'La photo doit être au format jpeg, png, ou jpg.',
            'beneficiaires.*.photo_url.max' => 'La taille de la photo ne doit pas dépasser 5120 Ko.',
            'beneficiaires.*.lien_parente.required' => 'Le lien de parenté du bénéficiaire est requis.',
        ];
    }
}
