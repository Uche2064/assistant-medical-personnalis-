<?php

namespace App\Http\Requests\contrat;

use App\Enums\RoleEnum;
use App\Enums\TypeContratEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateContratFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::TECHNICIEN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_contrat' => ['sometimes'],
            'prime_standard' => ['sometimes', 'numeric', 'min:0'],
            'couverture' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        return new HttpResponseException(ApiResponse::error(
            $validator->errors(),
            'Validation failed',
            422
        ));
    }

    public function messages(): array
    {
        return [
            'technicien_id.required' => 'Le champ technicien est obligatoire.',
            'technicien_id.integer' => 'Le champ technicien doit être un entier.',
            'technicien_id.exists' => 'Le technicien sélectionné n\'existe pas.',
            'type_contrat.required' => 'Le type de contrat est obligatoire.',
            'type_contrat.in' => 'Le type de contrat sélectionné est invalide.',
            'prime_standard.required' => 'La prime standard est obligatoire.',
            'prime_standard.numeric' => 'La prime standard doit être un nombre.',
            'prime_standard.min' => 'La prime standard doit être supérieure ou égale à 0.',
            'couverture.required' => 'La couverture est obligatoire.',
            'couverture.numeric' => 'La couverture doit être un nombre.',
            'couverture.min' => 'La couverture doit être supérieure ou égale à 0.',
            'couverture.max' => 'La couverture doit être inférieure ou égale à 100.',
        ];
    }
}

