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
            'reponses.*.question_id' => ['required_with:reponses', 'exists:questions,id'],
        ];
        
        // Récupérer les questions obligatoires pour les prospects physiques
        $obligatoryQuestionIds = Question::where('destinataire', TypeDemandeurEnum::PROSPECT_PHYSIQUE->value)
            ->where('obligatoire', true)
            ->where('est_actif', true)
            ->pluck('id')
            ->toArray();
        
        if (!empty($obligatoryQuestionIds)) {
            // Vérifier que toutes les questions obligatoires ont une réponse
            $baseRules['reponses'] = [
                'required',
                'array',
                function ($attribute, $value, $fail) use ($obligatoryQuestionIds) {
                    $providedQuestionIds = array_column($value, 'question_id');
                    $missingQuestionIds = array_diff($obligatoryQuestionIds, $providedQuestionIds);
                    
                    if (!empty($missingQuestionIds)) {
                        $fail('Des réponses aux questions obligatoires sont manquantes.');
                    }
                }
            ];
        }
        
        // Récupérer toutes les questions du prospect physique
        $prospectQuestions = Question::where('destinataire', TypeDemandeurEnum::PROSPECT_PHYSIQUE->value)
            ->where('est_actif', true)
            ->pluck('id')
            ->toArray();
        
        // Ajouter des règles de validation dynamiques pour les réponses en fonction du type de données
        if ($this->has('reponses') && is_array($this->input('reponses'))) {
            foreach ($this->input('reponses') as $index => $reponse) {
                if (isset($reponse['question_id'])) {
                    $questionId = $reponse['question_id'];
                    
                    // Vérifier que la question appartient bien au prospect physique
                    if (!in_array($questionId, $prospectQuestions)) {
                        continue; // Ignorer les questions qui ne sont pas pour les prospects physiques
                    }
                    
                    $question = Question::find($questionId);
                    
                    if ($question) {
                        $validationRules = ['required_with:reponses'];
                        
                        switch ($question->type_donnees) {
                            case TypeDonneeEnum::DATE:
                                $validationRules[] = 'date';
                                break;
                            case TypeDonneeEnum::NUMBER:
                                $validationRules[] = 'numeric';
                                break;
                            case TypeDonneeEnum::BOOLEAN:
                                $validationRules[] = 'boolean';
                                break;
                            case TypeDonneeEnum::TEXT:
                                $validationRules[] = 'string';
                                break;
                            case TypeDonneeEnum::FILE:
                                $validationRules = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];
                                break;
                            default: 
                                $validationRules[] = 'string';
                                break;
                        }
                        
                        // Ajouter la règle de validation pour cette réponse spécifique
                        $baseRules["reponses.{$index}.reponse"] = $validationRules;
                    }
                }
            }
        }
        
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
            'email.email' => 'L\'email doit être une adresse valide',
            'contact.required' => 'Le numéro de téléphone est obligatoire',
            'date_naissance.date' => 'La date de naissance doit être une date valide',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'adresse.required' => 'L\'adresse est obligatoire',
            'sexe.required' => 'Le sexe est obligatoire',
            'sexe.in' => 'Le sexe doit être m ou f',
            'piece_identite.required' => 'La pièce d\'identité est obligatoire',
            'situation_familiale.in' => 'La situation familiale doit être l\'une des valeurs suivantes: célibataire, marié(e), divorcé(e), veuf(ve)',
            'reponses.required' => 'Les réponses au questionnaire sont obligatoires',
            'reponses.*.question_id.exists' => 'Une question spécifiée n\'existe pas',
            'reponses.*.reponse.required_with' => 'Toutes les questions doivent avoir une réponse',
            'reponses.*.reponse.date' => 'La réponse doit être une date valide',
            'reponses.*.reponse.numeric' => 'La réponse doit être un nombre',
            'reponses.*.reponse.boolean' => 'La réponse doit être un booléen (vrai/faux)',
        ];
    }
}
