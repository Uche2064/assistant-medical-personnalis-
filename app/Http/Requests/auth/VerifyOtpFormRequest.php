<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOtpFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['string', 'required', 'email'],
            'otp' => ['string', 'required', 'min:6', 'max:6']
        ];
    }

    public function failedValidation(Validator $validator){
        return new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array {
        return [
            'email' => 'L\'email est requis',
            'otp' => 'Le code est requis',
            'email.email' => 'L\'email est invalide',
            'otp.min' => 'Le code doit contenir 6 chiffres',
            'otp.max' => 'Le code doit contenir 6 chiffres'
        ];
    }
}
