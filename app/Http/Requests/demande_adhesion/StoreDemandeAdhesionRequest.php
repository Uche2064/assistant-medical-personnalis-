<?php

namespace App\Http\Requests\demande_adhesion;

use App\Enums\LienParenteEnum;
use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class StoreDemandeAdhesionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->hasRole(RoleEnum::CLIENT->value) && Auth::check();
    }

    public function rules(): array
    {
        $typeDemandeur = $this->input('type_demandeur');
        if (!$typeDemandeur) {
            return ['type_demandeur' => 'required|in:' . implode(',', TypeDemandeurEnum::values())];
        }

        $questions = Question::forDestinataire($typeDemandeur)->get()->keyBy('id');
        $questionIds = $questions->pluck('id')->toArray();

        Log::info('Questions', ['questionsIds' => $questionIds]);


        $rules = [
            'type_demandeur' => 'required|in:' . implode(',', TypeDemandeurEnum::values()),
            'reponses' => ['required', 'array'],
            'reponses.*.question_id' => ['required', Rule::in($questionIds)],
            'beneficiaires' => ['nullable', 'array'],
            'beneficiaires.*.nom' => ['required', 'string'],
            'beneficiaires.*.prenoms' => ['nullable', 'string'],
            'beneficiaires.*.date_naissance' => ['required', 'date'],
            'beneficiaires.*.sexe' => ['required', 'in:M,F'],
            'beneficiaires.*.profession' => ['nullable', 'string'],
            'beneficiaires.*.email' => ['nullable', 'email'],
            'beneficiaires.*.contact' => ['nullable', 'string'],
            'beneficiaires.*.profession' => ['nullable', 'string'],
            'beneficiaires.*.photo_url' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'beneficiaires.*.lien_parente' => ['required', 'in:' . implode(',', LienParenteEnum::values())],
            'beneficiaires.*.reponses' => ['required', 'array'],
        ];

        foreach ($this->input('reponses', []) as $index => $reponse) {
            $questionId = $reponse['question_id'] ?? null;
            if (!$questionId || !$questions->has($questionId)) continue;
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


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'type_demandeur.required' => 'Le champ type_demandeur est obligatoire.',
            'reponses.required' => 'Les réponses au questionnaire sont requises.',
            'reponses.array' => 'Les réponses au questionnaire doivent être un tableau.',
            'reponses.*.question_id.required' => 'L\'ID de la question est requis.',
            'reponses.*.question_id.integer' => 'L\'ID de la question doit être un entier.',
            'reponses.*.question_id.in' => 'L\'ID de la question doit être un ID valide.',
        ];
    }
}
