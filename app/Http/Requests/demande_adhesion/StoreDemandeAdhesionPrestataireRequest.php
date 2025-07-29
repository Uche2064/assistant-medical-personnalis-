<?php

namespace App\Http\Requests\demande_adhesion;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TypePrestataireEnum;

class StoreDemandeAdhesionPrestataireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Informations de base (selon le document SUNU Santé)
            'raison_sociale' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:20',
            'adresse' => 'required|string|max:500',
            'type_prestataire' => 'required|string|in:' . implode(',', TypePrestataireEnum::values()),
            
            // Documents communs (selon le document SUNU Santé)
            'autorisation_ouverture' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'plan_situation_geographique' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'presentation_structure' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            
            // Documents spécifiques par type
            'diplome_responsable' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'attestation_ordre' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'diplomes_responsables' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'grille_tarifaire' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'carte_immatriculation_fiscale' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            
            // Réponses au questionnaire
            'reponses_questionnaire' => 'required|array',
            'reponses_questionnaire.*.question_id' => 'required|exists:questions,id',
            'reponses_questionnaire.*.valeur' => 'required|string',
            'reponses_questionnaire.*.fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Informations de base
            'raison_sociale.required' => 'La raison sociale est requise.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'contact.required' => 'Le contact est requis.',
            'adresse.required' => 'L\'adresse est requise.',
            'type_prestataire.required' => 'Le type de prestataire est requis.',
            'type_prestataire.in' => 'Le type de prestataire n\'est pas valide.',
            
            // Documents communs
            'autorisation_ouverture.required' => 'L\'autorisation officielle d\'ouverture est requise.',
            'autorisation_ouverture.file' => 'L\'autorisation d\'ouverture doit être un fichier.',
            'autorisation_ouverture.mimes' => 'L\'autorisation d\'ouverture doit être au format PDF, JPG, JPEG ou PNG.',
            'autorisation_ouverture.max' => 'L\'autorisation d\'ouverture ne doit pas dépasser 2MB.',
            'plan_situation_geographique.required' => 'Le plan de situation géographique est requis.',
            'plan_situation_geographique.file' => 'Le plan de situation géographique doit être un fichier.',
            'plan_situation_geographique.mimes' => 'Le plan de situation géographique doit être au format PDF, JPG, JPEG ou PNG.',
            'plan_situation_geographique.max' => 'Le plan de situation géographique ne doit pas dépasser 2MB.',
            'presentation_structure.required' => 'La présentation en images de la structure est requise.',
            'presentation_structure.file' => 'La présentation de la structure doit être un fichier.',
            'presentation_structure.mimes' => 'La présentation de la structure doit être au format PDF, JPG, JPEG ou PNG.',
            'presentation_structure.max' => 'La présentation de la structure ne doit pas dépasser 2MB.',
            
            // Documents spécifiques
            'diplome_responsable.file' => 'Le diplôme du responsable doit être un fichier.',
            'diplome_responsable.mimes' => 'Le diplôme du responsable doit être au format PDF, JPG, JPEG ou PNG.',
            'diplome_responsable.max' => 'Le diplôme du responsable ne doit pas dépasser 2MB.',
            'attestation_ordre.file' => 'L\'attestation d\'inscription à l\'ordre doit être un fichier.',
            'attestation_ordre.mimes' => 'L\'attestation d\'inscription à l\'ordre doit être au format PDF, JPG, JPEG ou PNG.',
            'attestation_ordre.max' => 'L\'attestation d\'inscription à l\'ordre ne doit pas dépasser 2MB.',
            'diplomes_responsables.file' => 'Les diplômes des responsables doivent être un fichier.',
            'diplomes_responsables.mimes' => 'Les diplômes des responsables doivent être au format PDF, JPG, JPEG ou PNG.',
            'diplomes_responsables.max' => 'Les diplômes des responsables ne doivent pas dépasser 2MB.',
            'grille_tarifaire.file' => 'La grille tarifaire doit être un fichier.',
            'grille_tarifaire.mimes' => 'La grille tarifaire doit être au format PDF, JPG, JPEG ou PNG.',
            'grille_tarifaire.max' => 'La grille tarifaire ne doit pas dépasser 2MB.',
            'carte_immatriculation_fiscale.file' => 'La carte d\'immatriculation fiscale doit être un fichier.',
            'carte_immatriculation_fiscale.mimes' => 'La carte d\'immatriculation fiscale doit être au format PDF, JPG, JPEG ou PNG.',
            'carte_immatriculation_fiscale.max' => 'La carte d\'immatriculation fiscale ne doit pas dépasser 2MB.',
            
            // Questionnaire
            'reponses_questionnaire.required' => 'Les réponses au questionnaire sont requises.',
            'reponses_questionnaire.array' => 'Les réponses au questionnaire doivent être un tableau.',
            'reponses_questionnaire.*.question_id.required' => 'L\'ID de la question est requis.',
            'reponses_questionnaire.*.question_id.exists' => 'La question spécifiée n\'existe pas.',
            'reponses_questionnaire.*.valeur.required' => 'La valeur de la réponse est requise.',
        ];
    }
} 