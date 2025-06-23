<?php

namespace App\Http\Requests\medecin;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class QuestionsBulkInsertRequest extends FormRequest
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
            '*.libelle' => 'required|string|max:255',
            '*.type_donnees' => 'required|string|in:' . implode(',', TypeDonneeEnum::values()),
            '*.destinataire' => 'required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            '*.obligatoire' => 'boolean',
            '*.est_actif' => 'boolean',
            '*.options' => 'nullable|json',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}
