<?php

namespace App\Http\Requests\commercial;

use App\Enums\ClientTypeEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CreateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_demandeur' => ['required', Rule::in(TypeDemandeurEnum::values())],
            'email' => 'required|email|unique:users,email',
            'contact' => 'required|string|unique:users,contact',
            'adresse' => 'required|string|max:500',

            // Données communes pour tous les types
            'nom' => 'required_if:type_demandeur,client|string|max:255',
            'type_client' => ['required_if:type_demandeur,client', Rule::in(ClientTypeEnum::values())],

            // Données pour demandeur client physique
            'prenoms' => 'required_if:type_client,physique|string|max:255',
            'date_naissance' => 'required_if:type_client,physique|date|before:today',
            'profession' => 'nullable|string|max:255',
            'sexe' => 'required_if:type_client,physique|in:M,F',

            // Pas de photo requise pour la création par commercial
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            // Pas de mot de passe requis (généré automatiquement)
        ];
    }

    public function messages(): array
    {
        return [
            'type_demandeur.required' => 'Le type de demandeur est obligatoire.',
            'type_demandeur.in' => 'Le type de demandeur sélectionné est invalide.',
            'type_client.in' => 'Le type de client sélectionné est invalide.',
            'type_client.required_if' => 'Le type de client est obligatoire.',

            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            
            'contact.required' => 'Le champ contact est obligatoire.',
            'contact.unique' => 'Ce contact existe déjà.',
            
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',

            'nom.required_if' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            
            'prenoms.required_if' => 'Les prénoms sont obligatoires pour un client physique.',
            'prenoms.max' => 'Les prénoms ne peuvent pas dépasser 255 caractères.',
            
            'date_naissance.required_if' => 'La date de naissance est obligatoire pour un client physique.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            
            'sexe.required_if' => 'Le sexe est obligatoire pour un client physique.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            
            'profession.max' => 'La profession ne peut pas dépasser 255 caractères.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
       
        throw new HttpResponseException(
            ApiResponse::error('Erreur de validation', 422, $validator->errors())
        );
    }
}