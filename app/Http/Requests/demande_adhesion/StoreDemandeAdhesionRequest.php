<?php

namespace App\Http\Requests\demande_adhesion;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePersonneEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use App\Utils\QuestionValidatorBuilder;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDemandeAdhesionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $typeDemandeur = Auth::user()->personne->type_personne;
        if (!$typeDemandeur) {
            return ['type_demandeur' => 'required|in:' . implode(',', TypeDemandeurEnum::values())];
        }

        $questions = Question::forDestinataire($typeDemandeur)->get()->keyBy('id');
        $questionIds = $questions->pluck('id')->toArray();


        $rules = [
            'reponses' => ['required', 'array'],
            'reponses.*.question_id' => ['required', 'integer', Rule::in($questionIds)],
        ];


        foreach ($this->input('reponses', []) as $index => $reponse) {
            $questionId = $reponse['question_id'] ?? null;
            if (!$questionId || !$questions->has($questionId)) {
                continue;
            }

            $question = $questions->get($questionId);
            $ruleKey = 'reponses.' . $index;

            $required = $question->isRequired() ? 'required' : 'nullable';

            switch ($question->type_donnee) {
                case TypeDonneeEnum::TEXT:
                case TypeDonneeEnum::RADIO:
                    $rules[$ruleKey . '.reponse_text'] = [$required, 'string'];
                    break;
                case TypeDonneeEnum::NUMBER:
                    $rules[$ruleKey . '.reponse_decimal'] = [$required, 'numeric'];
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
        ];
    }
}
