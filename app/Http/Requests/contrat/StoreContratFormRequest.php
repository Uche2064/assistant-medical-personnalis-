<?php

namespace App\Http\Requests\contrat;

use App\Enums\RoleEnum;
use App\Enums\TypeContratEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreContratFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::TECHNICIEN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     */
    public function rules(): array
    {
        return [
            'libelle' => ['required'],
            'prime_standard' => ['required', 'numeric', 'min:0'],
            'couverture' => ['required', 'numeric', 'min:0', 'max:100'],
            'frais_gestion' => ['required', 'numeric', 'min:0'],
            'categories_garanties' => ['required', 'array', 'min:1'],
            'categories_garanties.*.categorie_garantie_id' => ['required', 'integer', 'exists:categories_garanties,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'technicien_id.required' => 'Le champ technicien est obligatoire.',
            'technicien_id.integer' => 'Le champ technicien doit être un entier.',
            'technicien_id.exists' => 'Le technicien sélectionné n\'existe pas.',
            'libelle.required' => 'Le type de contrat est obligatoire.',
            'libelle.in' => 'Le type de contrat sélectionné est invalide.',
            'prime_standard.required' => 'La prime standard est obligatoire.',
            'prime_standard.numeric' => 'La prime standard doit être un nombre.',
            'prime_standard.min' => 'La prime standard doit être supérieure ou égale à 0.',
            'frais_gestion.required' => 'Frais de gestion requis',
            'frais_gestion.numeric' => 'Frais de gestion doit être un nombre valide',
            'frais_gestion.min' => 'Frais de gestion doit supérieur à 0',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(
            'Erreur de validation',
            422,
            $validator->errors(),
        ));
    }
}

 