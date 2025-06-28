<?php

namespace App\Http\Requests\gestionnaire;

use App\Enums\SexeEnum;
use App\Enums\TypePersonnelEnum;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PersonnelFormRequest extends FormRequest
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
            'email' => ['email', 'max:255',],
            'contact' => ['string', 'max:50', 'unique:users,contact'],
            'username' => ['nullable', 'string', 'unique:users,username'],
            'adresse' => ['required', 'json'],
            'sexe' => ['nullable', Rule::in(SexeEnum::values())],
            'date_naissance' => ['nullable', 'date'],
            'photo' => ['nullable', 'string'],
            'type_personnel' => ['required', Rule::in(TypePersonnelEnum::values())]
        ];

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error($validator->errors()->first(), 422)
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
            'contact.unique' => 'Ce contact existe déjà dans la base de données.',
            'username.string' => 'Le champ username doit être une chaine de caractères.',
            'username.unique' => 'Ce username existe déjà dans la base de données.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.json' => 'Le champ adresse doit être un objet JSON.',
            'sexe.in' => 'Le champ sexe n\'est pas dans la liste des valeurs acceptées.',
            'date_naissance.date' => 'Le champ date de naissance est invalide.',
            'photo.string' => 'Le champ photo doit être une chaine de caractères.',
            'type_personnel.required' => 'Le champ type de personnel est obligatoire.',
            'type_personnel.in' => 'Le champ type de personnel n\'est pas dans la liste des valeurs acceptées.',

        ];
    }
}
