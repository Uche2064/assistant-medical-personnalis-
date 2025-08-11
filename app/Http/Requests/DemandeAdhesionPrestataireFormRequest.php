<?php

namespace App\Http\Requests;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use App\Utils\QuestionValidatorBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DemandeAdhesionPrestataireFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        if (!$this->has('type_prestataire')) {
            return [];
        }

        $type = TypePrestataireEnum::tryFrom($this->input('type_prestataire'));

        if (!$type) {
            return [];
        }
        $rules = [
            "raison_sociale" => ['required', 'string', 'max:255',],
            "email" => ['required', 'email', 'max:255', 'unique:users,email'],
            "contact" => ['required', 'numeric', 'unique:users,contact', 'regex:/^\+[0-9]+$/'],
            "adresse" => ['required', 'string'],
            "type_prestataire" => ['required', Rule::in(TypePrestataireEnum::values())],
        ];


        return array_merge($rules, QuestionValidatorBuilder::buildRules($type->value));
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        $messages = [];

        $questions = Question::forDestinataire(TypeDemandeurEnum::PHYSIQUE->value)->get();

        foreach ($questions as $question) {
            $label = $question->libelle;
            $questionKey = 'reponses.' . $question->id;

            if ($question->isRequired()) {
                $messages[$questionKey . '.required'] = "La réponse à la question \"$label\" est obligatoire.";
            }

            if ($question->type_donnee === TypeDonneeEnum::NUMBER) {
                $messages[$questionKey . '.numeric'] = "La réponse à la question \"$label\" doit être un nombre.";
            }

            if ($question->type_donnee === TypeDonneeEnum::DATE) {
                $messages[$questionKey . '.date'] = "La réponse à la question \"$label\" doit être une date valide.";
            }

            if ($question->type_donnee === TypeDonneeEnum::BOOLEAN || $question->type_donnee === TypeDonneeEnum::RADIO) {
                $messages[$questionKey . '.in'] = "La réponse à la question \"$label\" n'est pas valide.";
            }
        }

        return $messages;
    }
}
