<?php

namespace App\Http\Requests;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class DemandeAdhesionPrestataireFormRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nom' => ['nullable', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'raison_sociale' => ['nullable', 'string', 'max:255', 'unique:demandes_adhesions,raison_sociale'],
            'adresse' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
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
            'type_demande' => [
                'required',
                Rule::in([
                    TypeDemandeurEnum::CENTRE_DE_SOINS->value,
                    TypeDemandeurEnum::MEDECIN_LIBERAL->value,
                    TypeDemandeurEnum::PHARMACIE->value,
                    TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value,
                    TypeDemandeurEnum::OPTIQUE->value,
                ])
            ],
            'reponses'=> ['required', 'array'],
            'reponses.*'=> ['required', 'file', 'mimes:jpeg,png,pdf']
        ];

        return $rules;
    }

    
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'nom.string' => 'Le nom doit être une chaine de caractères',
            'nom.max' => 'Le nom ne doit pas d passer 255 caractères',

            'raison_sociale.string' => 'La raison sociale doit être une chaine de caractères',
            'raison_sociale.max' => 'La raison sociale ne doit pas d passer 255 caractères',
            'raison_sociale.unique' => 'La raison sociale est déjà utilisée',

            'adresse.required' => 'L\'adresse est obligatoire',
            'adresse.string' => 'L\'adresse doit  être une chaine de caractères',
            'adresse.max' => 'L\'adresse ne doit pas d passer 255 caractères',

            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email n\'est pas valide',
            'email.max' => 'L\'email ne doit pas d passer 255 caractères',
            'email.unique' => 'L\'email est déjà utilisé',

            'contact.required' => 'Le contact est obligatoire',
            'contact.string' => 'Le contact doit  être une chaine de caractères',
            'contact.max' => 'Le contact ne doit pas d passer 50 caractères',
            'contact.unique' => 'Le contact est déjà utilisé',

            'type_demande.required' => 'Le type de demande est obligatoire',
            'type_demande.in' => 'Le type de demande n\'est pas valide',

            'reponses.required' => 'Les réponses sont obligatoires',
            'reponses.array' => 'Les réponses doivent  être un tableau',
            
        ];
    }
}
