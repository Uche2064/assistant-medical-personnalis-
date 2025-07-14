<?php

namespace App\Http\Requests\medecin;

use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuestionUpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === RoleEnum::MEDECIN_CONTROLEUR;
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
            'libelle.string' => 'Le libell  doit  tre une cha ne de caract res.',
            'libelle.max' => 'Le libell  ne doit pas d passer :max caract res.',

            'type_donnees.string' => 'Le type de donn es doit  tre une cha ne de caract res.',
            'type_donnees.in' => 'Le type de donn es doit  tre l\'un des suivants : ' . implode(', ', TypeDonneeEnum::values()),

            'destinataire.string' => 'Le destinataire doit  tre une cha ne de caract res.',
            'destinataire.in' => 'Le destinataire doit  tre l\'un des suivants : ' . implode(', ', TypeDemandeurEnum::values()),

            'obligatoire.boolean' => 'La valeur de champ obligatoire doit  tre un bool en.',
            'est_actif.boolean' => 'La valeur de champ est actif doit  tre un bool en.',

            'options.json' => 'Le champ options doit  tre un objet JSON.',
        ];

    }
}
