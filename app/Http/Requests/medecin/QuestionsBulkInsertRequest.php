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

class QuestionsBulkInsertRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->role === RoleEnum::MEDECIN_CONTROLEUR;
    }


    public function rules(): array
    {
        return [
            '*.libelle' => 'required|string|max:255',
            '*.type_donnees' => 'required|string|in:' . implode(',', TypeDonneeEnum::values()),
            '*.destinataire' => 'required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            '*.obligatoire' => 'boolean',
            '*.est_actif' => 'boolean',
            '*.options' => 'nullable|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }


    public function messages() {
        return [
            '*.libelle.required' => 'Le libellé est obligatoire.',
            '*.libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            '*.libelle.max' => 'Le libellé ne doit pas dépasser :max caractères.',

            '*.type_donnees.required' => 'Le type de donnée est obligatoire.',
            '*.type_donnees.string' => 'Le type de donnée doit être une chaîne de caractères.',
            '*.type_donnees.in' => 'Le type de donn es doit  tre l\'un des suivants : ' . implode(', ', TypeDonneeEnum::values()),

            '*.destinataire.required' => 'Le destinataire est obligatoire.',
            '*.destinataire.string' => 'Le destinataire doit  tre une cha ne de caract res.',
            '*.destinataire.in' => 'Le destinataire doit  tre l\'un des suivants : ' . implode(', ', TypeDemandeurEnum::values()),

            '*.obligatoire.boolean' => 'La valeur de champ obligatoire doit  tre un bool en.',
            '*.est_actif.boolean' => 'La valeur de champ est actif doit  tre un bool en.',

            '*.options.string' => 'Le champ options doit  tre un objet JSON.',
        ];

    }
}
