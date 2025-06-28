<?php

namespace App\Http\Requests\admin;

use App\Enums\RoleEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use App\Models\Gestionnaire;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GestionnaireUpdateFormRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return Auth::user()->roles[0]->name === RoleEnum::ADMIN_GLOBAL->value;
    }

    public function rules(): array
    {

        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenoms' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email'],
            'contact' => ['sometimes', 'string', 'max:50'],
            'username' => ['sometimes', 'string', 'unique:users,username'],
            'adresse' => ['sometimes', 'json'],
            'sexe' => ['sometimes', 'string', Rule::in(SexeEnum::values())],
            'date_naissance' => ['sometimes', 'date'],
            'photo' => ['sometimes', 'string', 'max:255'],
            'est_actif' => ['sometimes', 'boolean'],
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'nom.required' => 'Le champ nom est obligatoire',
            'nom.string' => 'Le champ nom doit être une chaine de caractères',
            'nom.max' => 'Le champ nom ne doit pas dépasser 255 caractères',
            
            'prenoms.required' => 'Le champ prenoms est obligatoire',
            'prenoms.string' => 'Le champ prenoms doit être une chaine de caractères',
            'prenoms.max' => 'Le champ prenoms ne doit pas dépasser 255 caractères',
            
            'email.required' => 'Le champ email est obligatoire',
            'email.email' => 'Le champ email doit être un email',
            'email.max' => 'Le champ email ne doit pas dépasser 255 caractères',
            'email.unique' => 'Le champ email doit être unique',
            
            'contact.required' => 'Le champ contact est obligatoire',
            'contact.string' => 'Le champ contact doit être une chaine de caractères',
            
            'username.required' => 'Le champ username est obligatoire',
            'username.string' => 'Le champ username doit être une chaine de caractères',
            'username.max' => 'Le champ username ne doit pas dépasser 255 caractères',
            'username.unique' => 'Le champ username doit être unique',
            
            'adresse.required' => 'Le champ adresse est obligatoire',
            'adresse.json' => 'Le champ adresse doit être un objet JSON',
            
            'sexe.required' => 'Le champ sexe est obligatoire',
            'sexe.string' => 'Le champ sexe doit être une chaine de caractères',
            'sexe.in' => 'Le champ sexe doit  être l\'un des suivants : ' . implode(', ', SexeEnum::values()),
            'date_naissance.required' => 'Le champ date de naissance est obligatoire',
            'date_naissance.date' => 'Le champ date de naissance doit être une date',
            
            'photo.required' => 'Le champ photo est obligatoire',
            'photo.string' => 'Le champ photo doit être une chaine de caractères',
            
            'est_actif.required' => 'Le champ est actif est obligatoire',
            'est_actif.boolean' => 'Le champ est actif doit être un boolean',
            

        ];
    }
}
