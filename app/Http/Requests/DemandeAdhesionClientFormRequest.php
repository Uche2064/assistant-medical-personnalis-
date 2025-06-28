<?php

namespace App\Http\Requests;

use App\Enums\SexeEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class DemandeAdhesionClientFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $baseRules = [
            'nom' => ['required', 'string', 'max:255',],
            'prenoms' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'email', 
                'max:255', 
                'unique:demandes_adhesions,email',
                'unique:users,email'
            ],
            'contact' => [
                'required', 
                'string', 
                'max:50', 
                'unique:demandes_adhesions,contact',
                'unique:users,contact'
            ],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'adresse' => ['required', 'string', 'max:255'],
            'profession' => ['required', 'string', 'max:255'],
            'reponses' => ['required', 'array'],
            'sexe' => ['required', Rule::in(SexeEnum::values())],
        ];
        
        return $baseRules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenoms.required' => 'Les prénoms sont obligatoires',
            'email.required' => 'L\'email est obligatoire',
            'profession.required' => 'La prefession est obligatoire',
            'email.email' => 'L\'email doit être une adresse valide',
            'contact.required' => 'Le numéro de téléphone est obligatoire',
            'date_naissance.date' => 'La date de naissance doit être une date valide',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'adresse.required' => 'L\'adresse est obligatoire',
            'sexe.required' => 'Le sexe est obligatoire',
            'sexe.in' => 'Le champ sexe doit  être l\'un des suivants : ' . implode(', ', SexeEnum::values()),
            'reponses.required' => 'Les réponses au questionnaire sont obligatoires',
        ];
    }
}
