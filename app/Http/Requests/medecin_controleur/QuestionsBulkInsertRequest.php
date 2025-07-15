<?php

namespace App\Http\Requests\medecin_controleur;

use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePersonnelEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class QuestionsBulkInsertRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value);
    }


    public function rules(): array
    {
        return [
            '*' => 'required|array|min:1',
            '*.libelle' => 'required|string|max:255',
            '*.type_donnees' => 'required|string|in:' . implode(',', TypeDonneeEnum::values()),
            '*.destinataire' => 'required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            '*.obligatoire' => 'boolean',
            '*.est_actif' => 'boolean',
            '*.options' => 'nullable|array',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }


   public function messages()
{
    return [
        '*.libelle.required' => 'Le libellé est obligatoire.',
        '*.libelle.string' => 'Le libellé doit être une chaîne de caractères.',
        '*.libelle.max' => 'Le libellé ne doit pas dépasser :max caractères.',

        '*.type_donnees.required' => 'Le type de donnée est obligatoire.',
        '*.type_donnees.string' => 'Le type de donnée doit être une chaîne de caractères.',
        '*.type_donnees.in' => 'Le type de donnée non trouvé.',

        '*.destinataire.required' => 'Le destinataire est obligatoire.',
        '*.destinataire.string' => 'Le destinataire doit être une chaîne de caractères.',
        '*.destinataire.in' => 'Le destinataire non trouvé',

        '*.obligatoire.boolean' => 'La valeur du champ "obligatoire" doit être un booléen.',
        '*.est_actif.boolean' => 'La valeur du champ "est actif" doit être un booléen.',
        '*.options.array' => 'Le champ options doit être un tableau ou un objet JSON.',
    ];
}

}
