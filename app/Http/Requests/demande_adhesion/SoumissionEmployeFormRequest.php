<?php

namespace App\Http\Requests\demande_adhesion;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SoumissionEmployeFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'date_naissance' => 'required|date|before:today',
            'sexe' => 'required|in:M,F',
            'contact' => 'nullable|string|max:30',
            'profession' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
            'reponses' => 'required|array|min:1',
            'reponses.*.question_id' => 'required|integer|exists:questions,id',
            // Les champs de réponse sont validés dynamiquement côté contrôleur selon le type de question
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenoms.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            'reponses.required' => 'Le questionnaire médical est obligatoire.',
            'reponses.array' => 'Le questionnaire médical doit être un tableau.',
            'reponses.*.question_id.required' => 'Chaque réponse doit référencer une question.',
            'reponses.*.question_id.exists' => 'Une des questions n\'existe pas.',
        ];
    }
}
