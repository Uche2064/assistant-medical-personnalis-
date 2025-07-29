<?php

namespace App\Http\Requests\auth;

use App\Enums\TypeClientEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RegisterProspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_demandeur' => ['required', 'in:' . implode(',', TypeDemandeurEnum::values())],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'contact' => 'required|string|unique:users,contact',
            'adresse' => 'required|string',

            // Données pour demandeur physique
            'nom' => 'required_if:type_demandeur,physique|string|max:255',
            'prenoms' => 'required_if:type_demandeur,physique|string|max:255',
            'date_naissance' => 'required_if:type_demandeur,physique|date|before:today',
            'profession' => 'nullable|string|max:255',
            'sexe' => 'required_if:type_demandeur,physique|in:M,F',
            
            // Données pour demandeur moral (entreprise)
            'raison_sociale' => 'required_if:type_demandeur,moral|string|max:255|unique:entreprises,raison_sociale',
            'nombre_employes' => 'required_if:type_demandeur,moral|integer|min:1',
            'secteur_activite' => 'required_if:type_demandeur,moral|string|max:255',
            
            // Code de parrainage (optionnel)
            'code_parrainage' => 'nullable|string|exists:personnels,code_parainage',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            
            'password.required' => 'Le champ mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            
            'contact.required' => 'Le champ contact est obligatoire.',
            'contact.unique' => 'Ce contact existe déjà.',
            
            'adresse.required' => 'Le champ adresse est obligatoire.',
            
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            
            'prenoms.required' => 'Le champ prénoms est obligatoire.',
            'prenoms.string' => 'Les prénoms doivent être une chaîne de caractères.',
            'prenoms.max' => 'Les prénoms ne peuvent pas dépasser 255 caractères.',
            
            'date_naissance.required' => 'Le champ date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            
            'profession.string' => 'La profession doit être une chaîne de caractères.',
            'profession.max' => 'La profession ne peut pas dépasser 255 caractères.',
            
            'sexe.required' => 'Le champ sexe est obligatoire.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            
            'code_parrainage.exists' => 'Le code de parrainage fourni n\'est pas valide.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}