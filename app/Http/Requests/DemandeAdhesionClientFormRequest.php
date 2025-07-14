<?php

namespace App\Http\Requests;

use App\Enums\LienEnum;
use App\Enums\SexeEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use App\Utils\QuestionValidatorBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DemandeAdhesionClientFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "nom" => ['required', 'string', 'max:255'],
            "prenoms" => ['required', 'string', 'max:255'],
            "email" => ['required', 'email', 'max:255', 'unique:users,email'],
            "contact" => ['required', 'numeric', 'unique:users,contact'],
            "sexe" => ['required', Rule::in(SexeEnum::values())],
            "adresse" => ['required', 'string'],
            "date_naissance" => ['required', 'date'],
            "commercial_code" => ['nullable', 'string', 'exists:commercials,code_parainage'],
            "photo_url" => ['required', 'file'],
            "profession" => ['nullable', 'string', 'max:255'],
            "reponses" => ['nullable', 'array'], // on vérifie juste que c’est un tableau
            'beneficiaires' => ['nullable', 'array'],
            'beneficiaires.*.nom' => ['required', 'string'],
            'beneficiaires.*.prenoms' => ['required', 'string'],
            'beneficiaires.*.date_naissance' => ['required', 'date'],
            'beneficiaires.*.lien_parente' => ['required', Rule::in(LienEnum::values())],
            'beneficiaires.*.photo_url' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],
            'beneficiaires.*.sexe' => ['required', Rule::in(SexeEnum::values())],
            'beneficiaires.*.profession' => ['nullable', 'string'],
            'beneficiaires.*.reponses' => ['required', 'array',],

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        $messages = [];

        $questions = Question::forDestinataire(TypeDemandeurEnum::PROSPECT_PHYSIQUE->value)->get();

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

            if ($question->type_donnee === TypeDonneeEnum::FILE) {
                $messages[$questionKey . '.file'] = "La réponse à la question \"$label\" doit être un fichier.";
            }
        }

        return $messages;
    }
}
