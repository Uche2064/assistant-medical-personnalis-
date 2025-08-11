<?php

namespace App\Http\Requests\gestionnaire;

use App\Enums\RoleEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePersonnelRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact' => ['nullable', 'string', 'unique:users,contact', 'regex:/^[0-9]+$/'],
            'adresse' => ['required', 'string', 'max:500'],
            'sexe' => ['nullable', Rule::in(SexeEnum::values())],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'role' => ['required', Rule::in(array_diff(RoleEnum::values(), [RoleEnum::ADMIN_GLOBAL->value, RoleEnum::GESTIONNAIRE->value]))]
        ];

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error("Erreur lors de la validation", 422, $validator->errors())
        );
    }

    public function messages() {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit être une chaine de caractères.',
            'nom.max' => 'Le champ nom ne doit pas contenir plus de 255 caractères.',
            'prenoms.string' => 'Le champ prenom doit être une chaine de caractères.',
            'prenoms.max' => 'Le champ prenom ne doit pas contenir plus de 255 caractères.',
            'email.email' => 'L\'adresse e-mail est invalide.',
            'email.max' => 'L\'adresse e-mail ne doit pas contenir plus de 255 caractères.',
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'L\'adresse e-mail est invalide.',
            'email.max' => 'L\'adresse e-mail ne doit pas contenir plus de 255 caractères.',
            'email.unique' => 'Cet email existe déjà dans la base de données.',
            'contact.string' => 'Le champ contact doit être une chaîne de caractères.',
            'contact.regex' => 'Le champ contact doit être un numéro de téléphone valide.',
            'contact.unique' => 'Ce contact existe déjà dans la base de données.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'sexe.in' => 'Le champ sexe n\'est pas dans la liste des valeurs acceptées.',
            'date_naissance.date' => 'Le champ date de naissance est invalide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'photo_url.file' => 'Le champ photo doit être un fichier.',
            'photo_url.mimes' => 'Le champ photo doit être un fichier de type jpg, jpeg, png, gif ou webp.',
            'photo_url.max' => 'Le fichier photo ne peut pas dépasser 2MB.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné n\'est pas autorisé.',
        ];
    }
}
