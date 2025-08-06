<?php

namespace App\Http\Requests\beneficiaire;

use App\Enums\LienParenteEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterBeneficiaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'date_naissance' => ['required', 'date'],
            'sexe' => ['required', 'in:M,F'],
            'lien_parente' => ['required', 'in:' . implode(',', LienParenteEnum::values())],
            'contact' => ['nullable', 'string', 'max:255', 'unique:assures,contact'],
            'profession' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est requis',
            'prenoms.required' => 'Les prénoms sont requis',
            'date_naissance.required' => 'La date de naissance est requise',
            'sexe.required' => 'Le sexe est requis',
            'lien_parente.required' => 'Le lien de parenté est requis',
            'lien_parente.in' => 'Le lien de parenté doit être un des suivants : ' . implode(', ', LienParenteEnum::values()),
            'contact.string' => 'Le contact doit être une chaîne de caractères',
            'profession.string' => 'La profession doit être une chaîne de caractères',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error( 'Validation failed', 422, $validator->errors()));
    }
}
