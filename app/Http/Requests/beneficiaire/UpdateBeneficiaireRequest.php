<?php

namespace App\Http\Requests\beneficiaire;

use App\Enums\LienParenteEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBeneficiaireRequest extends FormRequest
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
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenoms' => ['sometimes', 'string', 'max:255'],
            'date_naissance' => ['sometimes', 'date'],
            'sexe' => ['sometimes', 'in:M,F'],
            'lien_parente' => ['sometimes', 'in:' . implode(',', LienParenteEnum::values())],
            'contact' => ['nullable', 'string', 'max:255', 'unique:assures,contact'],
            'profession' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.string' => 'Le nom doit être une chaîne de caractères',
            'prenoms.string' => 'Les prénoms doivent être une chaîne de caractères',
            'date_naissance.date' => 'La date de naissance doit être une date valide',
            'sexe.in' => 'Le sexe doit être un des suivants : ' . implode(', ', SexeEnum::values()),
            'lien_parente.in' => 'Le lien de parenté doit être un des suivants : ' . implode(', ', LienParenteEnum::values()),
            'contact.string' => 'Le contact doit être une chaîne de caractères',
            'contact.unique' => 'Le contact doit être unique',
            'profession.string' => 'La profession doit être une chaîne de caractères',
            'email.email' => 'L\'email doit être une adresse email valide',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères',
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error( 'Validation failed', 422, $validator->errors()));
    }
}
