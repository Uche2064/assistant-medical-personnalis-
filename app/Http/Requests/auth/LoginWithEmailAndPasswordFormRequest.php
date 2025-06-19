<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginWithEmailAndPasswordFormRequest extends FormRequest
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
            'username' => ['required', 'string', 'exists:users,username'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse::error('Erreur de validation', 422 ,$validator->errors());
    
        throw new HttpResponseException($response);
    }
}
