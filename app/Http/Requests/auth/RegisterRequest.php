<?php

namespace App\Http\Requests\auth;

use App\Enums\ClientTypeEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'password' => 'required|string|min:8',
            'contact' => 'required|string|unique:users,contact',
            'adresse' => 'required|string|max:500',

            // Données communes pour tous les types
            'nom' => 'required_if:type_demandeur,client|string|max:255',
            'type_client' => ['required_if:type_demandeur,client', Rule::in(ClientTypeEnum::values())],

            // Données pour demandeur client
            'prenoms' => 'required_if:type_client,physique|string|max:255',
            'date_naissance' => 'required_if:type_client,physique|date|before:today',
            'profession' => 'nullable|string|max:255',
            'sexe' => 'required_if:type_client,physique|in:M,F',

            'photo' => 'required_if:type_client,physique|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'type_demandeur.required' => 'Le type de demandeur est obligatoire.',
            'type_demandeur.in' => 'Le type de demandeur sélectionné est invalide.',
            
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            
            'password.required' => 'Le champ mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            
            'contact.required' => 'Le champ contact est obligatoire.',
            'contact.unique' => 'Ce contact existe déjà.',
            
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            
            'nom.required_if' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'photo.required_if' => 'La photo est obligatoire pour un demandeur client.',
            'photo.image' => 'La photo doit être une image valide.',
            'photo.mimes' => 'La photo doit être au format jpeg, png, jpg, gif ou svg.',
            'photo.max' => 'La photo ne peut pas dépasser 2048 ko.',
            
            // Messages pour demandeur client
            'prenoms.required_if' => 'Le champ prénoms est obligatoire pour un demandeur client.',
            'prenoms.string' => 'Les prénoms doivent être une chaîne de caractères.',
            'prenoms.max' => 'Les prénoms ne peuvent pas dépasser 255 caractères.',
            
            'date_naissance.required_if' => 'Le champ date de naissance est obligatoire pour un demandeur client.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            
            'profession.string' => 'La profession doit être une chaîne de caractères.',
            'profession.max' => 'La profession ne peut pas dépasser 255 caractères.',
            
            'sexe.required_if' => 'Le champ sexe est obligatoire pour un demandeur client.',
            'sexe.in' => 'Le sexe doit être M ou F.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
} 