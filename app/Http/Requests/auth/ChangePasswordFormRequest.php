<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordFormRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string', 'exists:users,email'],
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'confirmed', 'different:current_password', 'regex:/.*[!@#$&].*/'],
            'new_password_confirmation' => ['required',],
        ];
    }

    public function failedValidation(Validator $validator) {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'new_password.regex' => 'Le nouveau mot de passe doit contenir au moins un caractère spécial.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.string' => 'L\'email doit être une chaîne de caractères.',
            'email.exists' => 'L\'email n\'existe pas.',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'new_password.different' => 'Le nouveau mot de passe doit être différent du mot de passe actuel.',
            'new_password_confirmation.required' => 'La confirmation du mot de passe est obligatoire.',
            'new_password_confirmation.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères.',
            'new_password.regex' => 'Le nouveau mot de passe doit contenir au moins un caractère spécial.',
        ];
    }




}
