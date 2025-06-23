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
                    TypeDemandeurEnum::LABORATOIRE->value,
                    TypeDemandeurEnum::OPTIQUE->value,
                ])
            ],
        ];

        $typeDemande = $this->input('type_demande');
        
        // Récupérer les questions obligatoires pour ce type de demandeur
        if ($typeDemande) {
            $questions = Question::where('destinataire', $typeDemande)
                ->where('est_actif', true)
                ->get();

            if ($questions->isNotEmpty()) {
                // ajout des règles de validation des réponses
                $rules['reponses'] = ['required', 'array'];
                
                foreach ($questions as $question) {
                    $questionId = $question->id;
                    $rules["reponses.{$questionId}.question_id"] = ['required'];
                    
                    // Règles spécifiques selon le type de données de la question
                    $validationRules = ['required'];
                    
                    switch ($question->type_donnees) {
                        case TypeDonneeEnum::BOOLEAN:
                            $validationRules[] = 'boolean';
                            break;
                        case TypeDonneeEnum::NUMBER:
                            $validationRules[] = 'numeric';
                            break;
                        case TypeDonneeEnum::DATE:
                            $validationRules[] = 'date';
                            break;
                        case TypeDonneeEnum::TEXT:
                            $validationRules[] = 'string';
                            break;
                        case TypeDonneeEnum::FILE:
                            $validationRules = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];
                            break;
                        case TypeDonneeEnum::SELECT:
                        case TypeDonneeEnum::CHECKBOX:
                        case TypeDonneeEnum::RADIO:
                            // Pour les types avec options prédéfinies, valider contre ces options
                            if ($question->options) {
                                $options = json_decode($question->options, true);
                                if (isset($options['options']) && is_array($options['options'])) {
                                    if ($question->type_donnees === TypeDonneeEnum::CHECKBOX) {
                                        $validationRules[] = 'array';
                                        $rules["reponses.{$questionId}.reponse.*"] = ['in:' . implode(',', $options['options'])];
                                    } else {
                                        $validationRules[] = Rule::in($options['options']);
                                    }
                                }
                            }
                            break;
                        default: 
                            $validationRules[] = 'string';
                            break;
                    }
                    
                    $rules["reponses.{$questionId}.reponse"] = $validationRules;
                }
            }
        }
        return $rules;
    }

    
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages()
    {
        return [
            'type_demande.in' => 'Le type de demande n\'est pas valide. Types valides : Centre de soins, Médecin libéral, Pharmacie, Laboratoire, Optique',
            'raison_sociale.required' => 'La raison sociale est obligatoire pour ce type de prestataire',
            'nom.required' => 'Le nom est obligatoire pour un médecin libéral',
            'prenoms.required' => 'Les prénoms sont obligatoires pour un médecin libéral',
            'reponses.required' => 'Les réponses au questionnaire sont obligatoires',
            'reponses.array' => 'Les réponses doivent être envoyées sous forme de tableau',
            'reponses.*.question_id.required' => 'L\'identifiant de la question est requis pour chaque réponse',
            'reponses.*.question_id.exists' => 'Une question spécifiée n\'existe pas',
            'reponses.*.reponse.required' => 'Toutes les questions doivent avoir une réponse',
            'reponses.*.reponse.file' => 'Le document doit être un fichier valide',
            'reponses.*.reponse.mimes' => 'Le document doit être au format PDF, JPG, JPEG ou PNG',
            'reponses.*.reponse.max' => 'La taille du document ne doit pas dépasser 10 Mo',
            'email.unique' => 'L\'adresse email est déjà utilisée',
            'contact.unique' => 'Le contact est déjà utilisé'
        ];
    }
}
