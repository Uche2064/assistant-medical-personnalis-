<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginWithEmailAndPasswordFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function failedValidation(Validator $validator)
    {    
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email est invalide',
            'email.string' => 'L\'email doit être une chaîne valide',
            'email.exists' => 'L\'email n\'exist pas',
            'password.required' => 'Le mot de passe est requis',
            'password.string' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',

        ];
    }


}
