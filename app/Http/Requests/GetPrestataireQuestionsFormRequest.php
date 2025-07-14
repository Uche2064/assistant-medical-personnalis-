<?php

namespace App\Http\Requests;

use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class GetPrestataireQuestionsFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destinataire' => ['required', Rule::in(array_diff(TypeDemandeurEnum::values(), [TypeDemandeurEnum::PROSPECT_MORAL->value, TypeDemandeurEnum::PROSPECT_PHYSIQUE->value]))],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}
