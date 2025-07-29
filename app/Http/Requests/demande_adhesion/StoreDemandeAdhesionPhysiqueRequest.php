<?php

namespace App\Http\Requests\demande_adhesion;

use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Services\DemandeReponseValidatorService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDemandeAdhesionPhysiqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reponses' => 'required|array|min:1',
            'reponses.*.question_id' => 'required|integer|exists:questions,id',
            // Bénéficiaires (optionnel)
            'beneficiaires' => 'nullable|array',
            'beneficiaires.*.nom' => 'required|string|max:255',
            'beneficiaires.*.prenoms' => 'required|string|max:255',
            'beneficiaires.*.date_naissance' => 'required|date|before:today',
            'beneficiaires.*.sexe' => 'required|in:M,F',
            'beneficiaires.*.lien_parente' => 'required|string',
            'beneficiaires.*.reponses' => 'required|array|min:1',
            'beneficiaires.*.reponses.*.question_id' => 'required|integer|exists:questions,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Valider les réponses du demandeur principal
            if ($this->has('reponses')) {
                try {
                    app(DemandeReponseValidatorService::class)->validate(
                        $this->input('reponses'),
                        TypeDemandeurEnum::PHYSIQUE->value,
                        'reponses'
                    );
                } catch (\Illuminate\Validation\ValidationException $e) {
                    foreach ($e->errors() as $field => $errors) {
                        $validator->errors()->add($field, $errors[0]);
                    }
                }
            }

            // Valider les réponses des bénéficiaires
            if ($this->has('beneficiaires')) {
                foreach ($this->input('beneficiaires', []) as $index => $beneficiaire) {
                    if (isset($beneficiaire['reponses'])) {
                        try {
                            app(DemandeReponseValidatorService::class)->validate(
                                $beneficiaire['reponses'],
                                TypeDemandeurEnum::PHYSIQUE->value,
                                "beneficiaires.{$index}.reponses"
                            );
                        } catch (\Illuminate\Validation\ValidationException $e) {
                            foreach ($e->errors() as $field => $errors) {
                                $validator->errors()->add($field, $errors[0]);
                            }
                        }
                    }
                }
            }
        });
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'reponses.required' => 'Le questionnaire médical est obligatoire.',
            'reponses.array' => 'Le questionnaire médical doit être un tableau.',
            'reponses.*.question_id.required' => 'Chaque réponse doit référencer une question.',
            'reponses.*.question_id.exists' => 'Une des questions n\'existe pas.',
            'beneficiaires.*.nom.required' => 'Le nom du bénéficiaire est obligatoire.',
            'beneficiaires.*.prenoms.required' => 'Le prénom du bénéficiaire est obligatoire.',
            'beneficiaires.*.date_naissance.required' => 'La date de naissance du bénéficiaire est obligatoire.',
            'beneficiaires.*.date_naissance.date' => 'La date de naissance du bénéficiaire doit être une date valide.',
            'beneficiaires.*.date_naissance.before' => 'La date de naissance du bénéficiaire doit être antérieure à aujourd\'hui.',
            'beneficiaires.*.sexe.required' => 'Le sexe du bénéficiaire est obligatoire.',
            'beneficiaires.*.lien_parente.required' => 'Le lien de parenté est obligatoire.',
            'beneficiaires.*.reponses.required' => 'Le questionnaire médical du bénéficiaire est obligatoire.',
            'beneficiaires.*.reponses.array' => 'Le questionnaire médical du bénéficiaire doit être un tableau.',
            'beneficiaires.*.reponses.*.question_id.required' => 'Chaque réponse du bénéficiaire doit référencer une question.',
            'beneficiaires.*.reponses.*.question_id.exists' => 'Une des questions du bénéficiaire n\'existe pas.',
        ];
    }
} 