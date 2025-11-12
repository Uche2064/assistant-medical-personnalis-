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
            'date_naissance' => ['required', 'date', 'before:today'],
            'sexe' => ['required', 'in:M,F'],
            'lien_parente' => ['required', 'in:' . implode(',', LienParenteEnum::values())],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'contact' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif|max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est requis',
            'prenoms.required' => 'Les prénoms sont requis',
            'date_naissance.required' => 'La date de naissance est requise',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'sexe.required' => 'Le sexe est requis',
            'sexe.in' => 'Le sexe doit être M ou F',
            'lien_parente.required' => 'Le lien de parenté est requis',
            'lien_parente.in' => 'Le lien de parenté doit être un des suivants : ' . implode(', ', LienParenteEnum::values()),
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'contact.string' => 'Le contact doit être une chaîne de caractères',
            'profession.string' => 'La profession doit être une chaîne de caractères',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères',
            'photo.required' => 'La photo est requise',
            'photo.image' => 'La photo doit être une image',
            'photo.mimes' => 'La photo doit être au format jpeg, png, jpg ou gif',
            'photo.max' => 'La photo ne peut pas dépasser 2MB',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error( 'Validation failed', 422, $validator->errors()));
    }
}
