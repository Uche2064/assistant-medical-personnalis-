<?php

namespace App\Http\Requests\auth;

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
            'nom' => 'required_if:type_demandeur,physique|string|max:255',
            'code_parrainage' => 'nullable|string|exists:personnels,code_parainage',

            // Données pour demandeur physique
            'prenoms' => 'required_if:type_demandeur,physique|string|max:255',
            'date_naissance' => 'required_if:type_demandeur,physique|date|before:today',
            'profession' => 'nullable|string|max:255',
            'sexe' => 'required_if:type_demandeur,physique|in:M,F',

            // Données pour demandeur moral (entreprise)
            'raison_sociale' => 'required_if:type_demandeur,autre,pharmacie,centre_de_soins,laboratoire_de_biologie_medicale,optique|string|max:255|unique:entreprises,raison_sociale',

            'photo_url' => 'required_if:type_demandeur,physique|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            'photo_url.required_if' => 'La photo est obligatoire pour un demandeur physique.',
            'photo_url.image' => 'La photo doit être une image valide.',
            'photo_url.mimes' => 'La photo doit être au format jpeg, png, jpg, gif ou svg.',
            'photo_url.max' => 'La photo ne peut pas dépasser 2048 ko.',
            
            // Messages pour demandeur physique
            'prenoms.required_if' => 'Le champ prénoms est obligatoire pour un demandeur physique.',
            'prenoms.string' => 'Les prénoms doivent être une chaîne de caractères.',
            'prenoms.max' => 'Les prénoms ne peuvent pas dépasser 255 caractères.',
            
            'date_naissance.required_if' => 'Le champ date de naissance est obligatoire pour un demandeur physique.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            
            'profession.string' => 'La profession doit être une chaîne de caractères.',
            'profession.max' => 'La profession ne peut pas dépasser 255 caractères.',
            
            'sexe.required_if' => 'Le champ sexe est obligatoire pour un demandeur physique.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            
            // Messages pour demandeur moral
            'raison_sociale.required_if' => 'La raison sociale est obligatoire pour une entreprise.',
            'raison_sociale.string' => 'La raison sociale doit être une chaîne de caractères.',
            'raison_sociale.max' => 'La raison sociale ne peut pas dépasser 255 caractères.',
            'raison_sociale.unique' => 'Cette raison sociale existe déjà.',
            
            'nombre_employes.required_if' => 'Le nombre d\'employés est obligatoire pour une entreprise.',
            'nombre_employes.integer' => 'Le nombre d\'employés doit être un nombre entier.',
            'nombre_employes.min' => 'Le nombre d\'employés doit être au moins 1.',
            
            'secteur_activite.required_if' => 'Le secteur d\'activité est obligatoire pour une entreprise.',
            'secteur_activite.string' => 'Le secteur d\'activité doit être une chaîne de caractères.',
            'secteur_activite.max' => 'Le secteur d\'activité ne peut pas dépasser 255 caractères.',
            
            // Messages pour prestataires
            'prenoms_prestataire.string' => 'Les prénoms du prestataire doivent être une chaîne de caractères.',
            'prenoms_prestataire.max' => 'Les prénoms du prestataire ne peuvent pas dépasser 255 caractères.',
            
            'raison_sociale_prestataire.string' => 'La raison sociale du prestataire doit être une chaîne de caractères.',
            'raison_sociale_prestataire.max' => 'La raison sociale du prestataire ne peut pas dépasser 255 caractères.',
            
            'code_parrainage.exists' => 'Le code de parrainage fourni n\'est pas valide.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
} 