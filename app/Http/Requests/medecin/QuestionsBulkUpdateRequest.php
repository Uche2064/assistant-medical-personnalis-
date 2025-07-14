<?php

namespace App\Http\Requests\medecin;

use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePersonnelEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class QuestionsBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === RoleEnum::MEDECIN_CONTROLEUR;
    }

    public function rules(): array
    {
        return [
            '*.id' => 'required|integer|exists:questions,id',
            '*.libelle' => 'sometimes|required|string|max:255',
            '*.type_donnees' => 'sometimes|required|string|in:' . implode(',', TypeDonneeEnum::values()),
            '*.destinataire' => 'sometimes|required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            '*.obligatoire' => 'sometimes|boolean',
            '*.est_actif' => 'sometimes|boolean',
            '*.options' => 'sometimes|nullable|json',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
    
    public function messages() {
        return [
            '*.id.required' => 'L\'id est obligatoire.',
            '*.libelle.required' => 'Le libell  est obligatoire.',
            '*.libelle.string' => 'Le libell  doit  tre une cha ne de caract res.',
            '*.libelle.max' => 'Le libell  ne doit pas d passer :max caract res.',

            '*.type_donnees.required' => 'Le type de donn es est obligatoire.',
            '*.type_donnees.string' => 'Le type de donn es doit  tre une cha ne de caract res.',
            '*.type_donnees.in' => 'Le type de donn es doit  tre l\'un des suivants : ' . implode(', ', TypeDonneeEnum::values()),

            '*.destinataire.required' => 'Le destinataire est obligatoire.',
            '*.destinataire.string' => 'Le destinataire doit  tre une cha ne de caract res.',
            '*.destinataire.in' => 'Le destinataire doit  tre l\'un des suivants : ' . implode(', ', TypeDemandeurEnum::values()),

            '*.obligatoire.boolean' => 'La valeur de champ obligatoire doit  tre un bool en.',
            '*.est_actif.boolean' => 'La valeur de champ est actif doit  tre un bool en.',

            '*.options.json' => 'Le champ options doit  tre un objet JSON.',
        ];

    }

}
