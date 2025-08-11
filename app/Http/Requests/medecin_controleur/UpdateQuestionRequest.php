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
            'type_donnee' => ['sometimes', 'string', Rule::in(TypeDonneeEnum::values())],
            'destinataire' => ['sometimes', 'string', Rule::in(TypeDemandeurEnum::values())],
            'obligatoire' => ['sometimes', 'boolean'],
            'est_actif' => ['sometimes', 'boolean'],
            // options doit être un tableau si type_donnee est select, checkbox ou radio
            'options' => ['nullable', 'array', 'sometimes:type_donnee,select,checkbox,radio'],
        ];
    }

    public function failedValidation(Validator $validator) {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne doit pas dépasser :max caractères.',

            'type_donnee.string' => 'Le type de donnée doit être une chaîne de caractères.',
            'type_donnee.in' => 'Le type de donnée non trouvé.',

            'destinataire.string' => 'Le destinataire doit être une chaîne de caractères.',
            'destinataire.in' => 'Le destinataire non trouvé',

            'obligatoire.boolean' => 'La valeur du champ obligatoire doit être un booléen.',
            'est_actif.boolean' => 'La valeur du champ est actif doit être un booléen.',

            'options.array' => 'Le champ options doit être un tableau ou un objet JSON.',
            'options.required_if' => 'Le champ options est obligatoire pour les types select, checkbox ou radio.',
        ];
    }
}
