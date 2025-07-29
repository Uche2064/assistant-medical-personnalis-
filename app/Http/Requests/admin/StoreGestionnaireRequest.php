<?php

namespace App\Http\Requests\admin;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreGestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('admin_global');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => 'required|string',
            'prenoms' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact' => 'required|string|unique:users,contact|regex:/^[0-9]+$/',
            'adresse' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */

    public function messages(): array
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit  être une chaîne de caractères.',

            'prenoms.string' => 'Le champ prenoms doit  être une chaîne de caractères.',

            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit  être un email valide.',
            'email.unique' => 'Le champ email doit  être unique.',

            'contact.required' => 'Le champ contact est obligatoire.',            
            'contact.string' => 'Le champ contact doit  être une chaîne de caractères.',
            'contact.unique' => 'Le champ contact doit  être unique.',

            'adresse.string' => 'Le champ adresse doit  être une chaîne de caractères.',
            'sexe.in' => 'Le champ sexe doit  être l\'un des suivants : M, F',

            'date_naissance.date' => 'Le champ date de naissance doit  être une date valide.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}
