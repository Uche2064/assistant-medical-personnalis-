<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class VerifyOtpRequest extends FormRequest
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
            'email' => ['string', 'required', 'email'],
            'type' => ['string', 'required'],
            'otp' => ['string', 'required', 'min:6', 'max:6']
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'Le code OTP est obligatoire.',
            'otp.min' => 'Le code OTP doit contenir 6 chiffres.',
            'otp.max' => 'Le code OTP doit contenir 6 chiffres.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error("Erreur de validation", 422, $validator->errors()));
    }
}
