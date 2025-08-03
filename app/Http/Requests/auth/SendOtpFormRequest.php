<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendOtpFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'email' => ['string', 'required', 'email'],
            'type' => ['string', 'required']
        ];
    }

    public function failedValidation(Validator $validator){
        return new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array {
        return [
            'email' => 'L\'email est requis',
            'email.email' => 'L\'email n\'est pas valide',
            'type' => 'Le type est requis'
        ];
    }
}
