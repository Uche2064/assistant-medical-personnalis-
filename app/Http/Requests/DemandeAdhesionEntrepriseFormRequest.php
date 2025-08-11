<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Question;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Utils\QuestionValidatorBuilder;
use Illuminate\Validation\Rule;

class DemandeAdhesionEntrepriseFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = [
            "raison_sociale" => ['required', 'string', 'max:255', ],
            "email" => ['required', 'email', 'max:255', 'unique:users,email'],
            "contact" => ['required', 'numeric', 'unique:users,contact', 'regex:/^\+[0-9]+$/'],
            "adresse" => ['required', 'string'],
            "nombre_employe" => ['required', 'integer', 'min:1'],
            "code_parainage" => ['nullable', 'string', 'exists:commercials,code_parainage'],
            'fiche_medicale_employe' => ['required', 'array'],
            'fiche_medicale_employe.*' => ['file', 'mimes:pdf,jpeg,png,jpg'],
        ];


        return array_merge($rules, QuestionValidatorBuilder::buildRules(TypeDemandeurEnum::PROSPECT_MORAL->value));
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        $messages = [
            "raison_sociale.required" => "Le champ raison sociale est obligatoire.",
            "raison_sociale.string" => "Le champ raison sociale doit être une chaîne de caractères.",
            "raison_sociale.max" => "Le champ raison sociale ne doit pas dépasser 255 caractères.",
            "raison_sociale.unique" => "Cette raison sociale est déjà utilisée.",
            "email.required" => "Le champ email est obligatoire.",
            "email.email" => "L'adresse email doit être valide.",
            "email.max" => "L'adresse email ne doit pas dépasser 255 caractères.",
            "email.unique" => "Cet email est déjà utilisé.",
            "contact.required" => "Le champ contact est obligatoire.",
            "contact.numeric" => "Le champ contact doit être un numéro de téléphone valide.",
            "contact.unique" => "Ce contact est déjà utilisé.",
            "adresse.required" => "Le champ adresse est obligatoire.",
            "adresse.string" => "Le champ adresse doit être une chaîne de caractères.",
        ];

        $questions = Question::forDestinataire(TypeDemandeurEnum::PROSPECT_MORAL->value)->get();

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
