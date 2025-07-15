<?php

namespace App\Http\Requests\demande_adhesion;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SoumissionEmployeFormRequest extends FormRequest
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
        return  [
            'nom' => 'required|string',
            'prenoms' => 'required|string',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:M,F',
            'email' => 'nullable|email|unique:employes_temp,email',
            'contact' => 'required|string|unique:employes_temp,contact',
            'fiche_medicale' => 'required|array|min:1',
            'fiche_medicale.*.question_id' => 'required|exists:questions,id',
            'fiche_medicale.*.reponse_text' => 'nullable|string',
            'fiche_medicale.*.reponse_bool' => 'nullable|boolean',
            'fiche_medicale.*.reponse_decimal' => 'nullable|numeric',
            'fiche_medicale.*.reponse_date' => 'nullable|date',
            'fiche_medicale.*.reponses_fichier' => 'nullable|file',
            'beneficiaires' => 'nullable|array',
            'beneficiaires.*.nom' => 'required|string',
            'beneficiaires.*.prenoms' => 'required|string',
            'beneficiaires.*.date_naissance' => 'required|date',
            'beneficiaires.*.sexe' => 'required|in:M,F',
            'beneficiaires.*.email' => 'nullable|email|unique:beneficiaires,email',
            'beneficiaires.*.contact' => 'required|string|unique:beneficiaires,contact',
            'beneficiaires.*.lien_parente' => 'required|in:enfant,conjoint,parent,autre',
            'beneficiaires.*.fiche_medicale' => 'required|array|min:1',
            'beneficiaires.*.fiche_medicale.*.question_id' => 'required|exists:questions,id',
            'beneficiaires.*.fiche_medicale.*.reponse_text' => 'nullable|string',
            'beneficiaires.*.fiche_medicale.*.reponse_bool' => 'nullable|boolean',
            'beneficiaires.*.fiche_medicale.*.reponse_decimal' => 'nullable|numeric',
            'beneficiaires.*.fiche_medicale.*.reponse_date' => 'nullable|date',
            'beneficiaires.*.fiche_medicale.*.reponses_fichier' => 'nullable|file',

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenoms.required' => 'Le prénom est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'fiche_medicale.required' => 'La fiche médicale est obligatoire.',
            'fiche_medicale.*.question_id.required' => 'La question est obligatoire.',
            'beneficiaires.*.nom.required' => 'Le nom est obligatoire.',
            'beneficiaires.*.prenoms.required' => 'Le prénom est obligatoire.',
            'beneficiaires.*.date_naissance.required' => 'La date de naissance est obligatoire.',
            'beneficiaires.*.sexe.required' => 'Le sexe est obligatoire.',
            'beneficiaires.*.lien_parente.required' => 'Le lien parent est obligatoire.',
            'beneficiaires.*.fiche_medicale.required' => 'La fiche médicale est obligatoire.',
            'beneficiaires.*.fiche_medicale.*.question_id.required' => 'La question est obligatoire.',
        ];
    }       

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}
