<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
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
            "email" => ['required', 'email'],
            "new_password" => ['required', 'string', 'min:8'],
            "password_confirmation" => ['required', 'string'],
        ];
    }


    public function messages(): array
    {
        return [
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.exists' => 'Aucun compte trouvé avec cet email.',
            'new_password.required' => 'Le mot de passe est obligatoire.',
            'new_password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'new_password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'token.required' => 'Le token de réinitialisation est obligatoire.',
            'token.string' => 'Le token doit être une chaîne de caractères.',
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
}
