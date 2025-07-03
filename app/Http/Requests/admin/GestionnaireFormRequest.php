<?php

namespace App\Http\Requests\admin;

use App\Enums\RoleEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GestionnaireFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->roles[0]->name === RoleEnum::ADMIN_GLOBAL->value;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact' => ['nullable', 'string', 'max:50', 'unique:users,contact'],
            'username' => [
                'nullable',
                'string',
                'unique:users,username'
            ],
            'adresse' => ['required', 'json'],
            'sexe' => ['nullable', Rule::in(SexeEnum::values())],
            'date_naissance' => ['nullable', 'date'],
            'photo' => ['nullable', 'file'],
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit  être une chaîne de caractères.',
            'nom.max' => 'Le champ nom ne doit pas faire plus de 255 caractères.',
            'prenoms.string' => 'Le champ prenoms doit  être une chaîne de caractères.',
            'prenoms.max' => 'Le champ prenoms ne doit pas faire plus de 255 caractères.',
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit  être un email valide.',
            'email.max' => 'Le champ email ne doit pas faire plus de 255 caractères.',
            'email.unique' => 'Le champ email doit  être unique.',
            'contact.string' => 'Le champ contact doit  être une chaîne de caractères.',
            'contact.max' => 'Le champ contact ne doit pas faire plus de 50 caractères.',
            'contact.unique' => 'Le champ contact doit  être unique.',
            'username.string' => 'Le champ username doit  être une chaîne de caractères.',
            'username.unique' => 'Le champ username doit  être unique.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.json' => 'Le champ adresse doit  être un JSON.',
            'sexe.in' => 'Le champ sexe doit  être l\'un des suivants : ' . implode(', ', SexeEnum::values()),
            'date_naissance.date' => 'Le champ date de naissance doit  être une date valide.',
            'est_actif.boolean' => 'Le champ est actif doit  être un booléen.',
            'photo.string' => 'Le champ photo doit  être une chaîne de caractères.',
          
        ];
    }
}
