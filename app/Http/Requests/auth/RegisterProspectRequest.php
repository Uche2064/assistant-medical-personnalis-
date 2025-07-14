<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterProspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_prospect' => 'required|in:physique,moral',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'contact' => 'required|string|unique:users,contact',
            'adresse' => 'required|string',

            // Physique
            'nom' => 'required_if:type_prospect,physique|string',
            'prenoms' => 'required_if:type_prospect,physique|string',
            'date_naissance' => 'required_if:type_prospect,physique|date',
            'profession' => 'nullable|string',

            // Moral
            'raison_sociale' => 'required_if:type_prospect,moral|string|unique:prospects,raison_sociale',
            'nombre_employes' => 'required_if:type_prospect,moral|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le champ mot de passe est obligatoire.',
            'password.min' => 'Le champ mot de passe doit contenir au moins 6 caractères.',
            'contact.required' => 'Le champ contact est obligatoire.',
            'contact.unique' => 'Ce contact existe déjà dans la base de données.',
            'adresse.required' => 'Le champ adresse est obligatoire.',

            // Physique
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit être une chaine de caractères.',
            'prenoms.required' => 'Le champ prenom est obligatoire.',
            'prenoms.string' => 'Le champ prenom doit être une chaine de caractères.',
            'date_naissance.required' => 'Le champ date de naissance est obligatoire.',
            'date_naissance.date' => 'Le champ date de naissance doit être une date valide.',
            'profession.nullable' => 'Le champ profession est obligatoire.',
            'profession.string' => 'Le champ profession doit être une chaine de caractères.',

            // Moral
            'raison_sociale.required' => 'Le champ raison sociale est obligatoire.',
            'raison_sociale.string' => 'Le champ raison sociale doit être une chaine de caractères.',
            'raison_sociale.unique' => 'Ce raison sociale existe déjà dans la base de données.',
            'nombre_employes.required' => 'Le champ nombre d\'employes est obligatoire.',
            'nombre_employes.integer' => 'Le champ nombre d\'employes doit être un entier.',
            'nombre_employes.min' => 'Le champ nombre d\'employes doit être supérieur ou égale à 1.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}