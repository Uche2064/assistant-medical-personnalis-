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
            'email' => ['email', 'max:255', 'unique:users,email'],
            'contact' => ['string', 'unique:users,contact', 'regex:/^\+[0-9]+$/'],
            'adresse' => ['required', 'string'],
            'sexe' => ['nullable', Rule::in(SexeEnum::values())],
            'date_naissance' => ['nullable', 'date'],
            'photo_url' => ['nullable', 'file'],
            'role' => ['required', array_diff(RoleEnum::values(), [RoleEnum::ADMIN_GLOBAL->value, RoleEnum::GESTIONNAIRE->value])]
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
            'contact.string' => 'Le champ contact doit être une chaine de caractères.',
            'contact.max' => 'Le champ contact ne doit pas contenir plus de 50 caractères.',
            'contact.regex' => 'Le champ contact doit être un numéro de téléphone valide au format international (ex: +1234567890).',
            'contact.unique' => 'Ce contact existe déjà dans la base de données.',
            'email.required' => 'Le champ email est obligatoire.',
            'email.unique' => 'Ce email existe déjà dans la base de données.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.json' => 'Le champ adresse doit être un objet JSON.',
            'sexe.in' => 'Le champ sexe n\'est pas dans la liste des valeurs acceptées.',
            'date_naissance.date' => 'Le champ date de naissance est invalide.',
            'photo.file' => 'Le champ photo doit être un fichier.',
        ];
    }
}
