<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendOtpFormRequest extends FormRequest
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
            'phone' => ['string', 'required']
        ];
    }

    public function failedValidation(Validator $validator){
        return new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array {
        return [
            'phone' => 'Le numéro est requis'
        ];
    }
}
