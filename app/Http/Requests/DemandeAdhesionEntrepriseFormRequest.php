<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DemandeAdhesionEntrepriseFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raison_sociale' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'max:255',
                'unique:demandes_adhesions,email',
                'unique:users,email'
            ],
            'contact' => [
                'required', 
                'string', 
                'max:50',
                'unique:demandes_adhesions,contact',
                'unique:users,contact'
            ],
            'adresse' => ['required', 'json'],

            'reponses' => ['nullable', 'array'],
            'reponses.*' => ['file', 'mimes:jpeg,png,pdf,jpg'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        return [
            'raison_sociale.required' => 'La raison sociale de l\'entreprise est obligatoire',
            'email.required' => 'L\'email de l\'entreprise est obligatoire',
            'contact.required' => 'Le numéro de téléphone de l\'entreprise est obligatoire',
            'adresse.required' => 'L\'adresse de l\'entreprise est obligatoire',
            'email.email' => 'L\'email de l\'entreprise doit être une adresse valide',
            'contact.string' => 'Le numéro de téléphone de l\'entreprise doit être une chaine de caractères',
            'contact.max' => 'Le numéro de téléphone de l\'entreprise est trop long',
            'nombre_employes.integer' => 'Le nombre d\'employés de l\'entreprise doit être un entier',
            'nombre_employes.min' => 'Le nombre d\'employés de l\'entreprise doit être supérieur ou égal à 1',
            'reponses.array' => 'Les réponses au questionnaire doivent être un tableau',
            'reponses.*.file' => 'Les réponses au questionnaire doivent être des fichiers',
            'reponses.*.mimes' => 'Les réponses au questionnaire doivent être des fichiers de type jpeg, png, pdf, jpg',

        ];
    }
}
