<?php

namespace App\Http\Requests\garanties;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class BulkDeleteGarantieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('medecin_controleur|technicien');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'ids'   => 'required|array',
                'ids.*' => 'integer|exists:garanties,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(
            'Erreur de validation',
            422,
            $validator->errors(),

        ));
    }

    public function messages(): array
    {
        return [
            'ids.array' => 'Il faut une liste',
            'ids.required' => 'Liste requise'
        ];
    }
}
