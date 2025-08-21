<?php

namespace App\Http\Requests;

use App\Enums\TypeContratEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class ContratFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'prime_standard' => 'required|numeric|min:0',
            'libelle' => 'required|string|in:' . implode(',', TypeContratEnum::values()),
            'categories_garanties_standard' => 'required|array',
            'categories_garanties_standard.*' => 'required|exists:categories_garanties,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'technicien_id.required' => 'Le technicien est obligatoire',
            'technicien_id.exists' => 'Le technicien sélectionné n\'existe pas',
            'prime_standard.required' => 'La prime est obligatoire',
            'prime_standard.numeric' => 'La prime doit être un nombre',
            'prime_standard.min' => 'La prime ne peut pas être négative',
        ];
    }
}
