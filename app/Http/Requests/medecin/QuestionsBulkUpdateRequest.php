<?php

namespace App\Http\Requests\medecin;

use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePersonnelEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class QuestionsBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->role->name === RoleEnum::PERSONNEL 
        && Auth::user()->personnel === TypePersonnelEnum::MEDECIN_CONTROLEUR;
    }

    public function rules(): array
    {
        return [
            '*.id' => 'required|integer|exists:questions,id',
            '*.libelle' => 'sometimes|required|string|max:255',
            '*.type_donnees' => 'sometimes|required|string|in:' . implode(',', TypeDonneeEnum::values()),
            '*.destinataire' => 'sometimes|required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            '*.obligatoire' => 'sometimes|boolean',
            '*.est_actif' => 'sometimes|boolean',
            '*.options' => 'sometimes|nullable|json',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
    
    public function messages(): array
    {
        return [
            '*.id' => 'required|integer|exists:questions,id',
            '*.libelle' => 'sometimes|required|string|max:255',
            '*.type_donnees' => 'sometimes|required|string|in:' . implode(',', TypeDonneeEnum::values()),
            '*.destinataire' => 'sometimes|required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            '*.obligatoire' => 'sometimes|boolean',
            '*.est_actif' => 'sometimes|boolean',
            '*.options' => 'sometimes|nullable|json',
        ];
    }

}
