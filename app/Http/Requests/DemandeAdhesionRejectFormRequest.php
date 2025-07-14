<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DemandeAdhesionRejectFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif_rejet' => 'required|string'
        ];
    }


    public function failedValidation(Validator $validator) {
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }
}
