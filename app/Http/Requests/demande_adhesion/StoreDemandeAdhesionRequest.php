<?php

namespace App\Http\Requests\demande_adhesion;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDemandeAdhesionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_demandeur' => 'required|in:physique',

            // Fiche médicale du prospect
            'fiche_medicale' => 'required|array|min:1',
            'fiche_medicale.*.question_id' => 'required|exists:questions,id',
            'fiche_medicale.*.reponse_text' => 'nullable|string',
            'fiche_medicale.*.reponse_bool' => 'nullable|boolean',
            'fiche_medicale.*.reponse_decimal' => 'nullable|numeric',
            'fiche_medicale.*.reponse_date' => 'nullable|date',
            'fiche_medicale.*.reponse_fichier' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'fiche_medicale.*' => 'array',

            // Bénéficiaires (facultatif)
            'beneficiaires' => 'nullable|array',
            'beneficiaires.*.nom' => 'required|string',
            'beneficiaires.*.prenoms' => 'required|string',
            'beneficiaires.*.date_naissance' => 'required|date',
            'beneficiaires.*.sexe' => 'required|in:masculin,feminin',
            'beneficiaires.*.lien_parente' => 'required|in:parent,conjoint,enfant,autre',
            'beneficiaires.*.fiche_medicale' => 'required|array|min:1',
            'beneficiaires.*.fiche_medicale.*.question_id' => 'required|exists:questions,id',
            'beneficiaires.*.fiche_medicale.*.reponse_text' => 'nullable|string',
            'beneficiaires.*.fiche_medicale.*.reponse_bool' => 'nullable|boolean',
            'beneficiaires.*.fiche_medicale.*.reponse_decimal' => 'nullable|numeric',
            'beneficiaires.*.fiche_medicale.*.reponse_date' => 'nullable|date',
            'beneficiaires.*.fiche_medicale.*.reponse_fichier' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'fiche_medicale.required' => 'La fiche médicale est obligatoire.',
            'fiche_medicale.*.question_id.required' => 'La question est obligatoire.',
            'type_demandeur.required' => 'Le champ type_demandeur est obligatoire.',
            'beneficiaires.*.nom.required' => 'Le nom du bénéficiaire est obligatoire.',
            'beneficiaires.*.prenoms.required' => 'Les prénoms du bénéficiaire sont obligatoires.',
            'beneficiaires.*.sexe.required' => 'Le sexe du bénéficiaire est obligatoire.',
            'beneficiaires.*.email.required' => 'L\'adresse e-mail du bénéficiaire est obligatoire.',
            'beneficiaires.*.lien_parente.required' => 'Le lien parente du bénéficiaire est obligatoire.',
            'beneficiaires.*.lien_parente.in' => 'Le lien parente du bénéficiaire doit être parente, conjoint ou conjointe, enfant ou autre.',
            'beneficiaires.*.reponses.*.question_id.required' => 'La question est obligatoire.',
            'beneficiaires.*.reponses.*.required' => 'La réponse est obligatoire.',
        ];
    }
}
