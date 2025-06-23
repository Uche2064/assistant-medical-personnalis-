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
            'adresse' => ['required', 'string', 'max:255'],
            'nombre_employes' => ['required', 'integer', 'min:1'],
            
            // Champs pour les questionnaires
            'reponses' => ['required', 'array'],
            'reponses.*.question_id' => ['required_with:reponses', 'exists:questions,id'],
            'reponses.*.reponse' => ['required_with:reponses', 'string']
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
            'statuts.required' => 'Les statuts sont obligatoires',
            'reponses.*.question_id.exists' => 'Une question spécifiée n\'existe pas',
            'reponses.*.reponse.required_with' => 'Toutes les questions doivent avoir une réponse'
        ];
    }
}
