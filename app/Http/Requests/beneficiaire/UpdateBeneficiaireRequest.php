<?php

namespace App\Http\Requests\beneficiaire;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\LienParenteEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
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
     */
    public function rules(): array
    {
        return [
            'nom' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'date_naissance' => 'sometimes|required|date|before:today',
            'sexe' => 'sometimes|required|string|in:' . implode(',', SexeEnum::values()),
            'lien_parente' => 'sometimes|required|string|in:' . implode(',', LienParenteEnum::values()),
            'profession' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenoms.required' => 'Les prénoms sont obligatoires',
            'date_naissance.required' => 'La date de naissance est obligatoire',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'sexe.required' => 'Le sexe est obligatoire',
            'sexe.in' => 'Le sexe doit être M ou F',
            'lien_parente.required' => 'Le lien de parenté est obligatoire',
            'lien_parente.in' => 'Le lien de parenté n\'est pas valide',
            'profession.max' => 'La profession ne peut pas dépasser 255 caractères',
            'contact.max' => 'Le contact ne peut pas dépasser 20 caractères',
            'email.email' => 'L\'email doit être valide',
            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'L\'image doit être au format jpeg, png, jpg ou gif',
            'photo.max' => 'L\'image ne peut pas dépasser 2MB',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Validation failed', 422, $validator->errors()));
    }
}
