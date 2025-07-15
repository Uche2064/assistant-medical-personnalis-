<?php

namespace App\Http\Requests\medecin_controleur;

use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'libelle' =>  ['sometimes', 'string', 'max:255'],
            'type_donnees' => ['sometimes', 'string', Rule::in(TypeDonneeEnum::values())],
            'destinataire' => ['sometimes', 'string', Rule::in(TypeDemandeurEnum::values())],
            'obligatoire' => ['sometimes', 'boolean'],
            'est_actif' => ['sometimes', 'boolean'],
            'options' => ['nullable', 'json'],
        ];
    }

    public function failedValidation(Validator $validator) {
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'libelle.string' => 'Le libellé  doit  être une chaîne de caractères.',
            'libelle.max' => 'Le libellé  ne doit pas dépasser :max caract res.',

            'type_donnees.string' => 'Le type de donn es doit  être une cha ne de caract res.',
            'type_donnees.in' => 'Le type de données non trouvé.',

            'destinataire.string' => 'Le destinataire doit  tre une cha ne de caract res.',
            'destinataire.in' => 'Le destinataire non trouvé',

            'obligatoire.boolean' => 'La valeur de champ obligatoire doit  être un bool en.',
            'est_actif.boolean' => 'La valeur de champ est actif doit être un bool en.',

            'options.json' => 'Le champ options doit être un objet JSON.',
        ];

    }
}
