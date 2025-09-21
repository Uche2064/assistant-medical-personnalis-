<?php

namespace App\Http\Requests\auth;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckUniqueFieldRequest extends FormRequest
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
            'champ' => ['required', 'string'],
            'valeur' => ['required']
        ];
    }

    public function failedValidation(Validator $validator)
    {    
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'champ.required' => 'Le champ est requis',
            'champ.string' => 'Le doit être une chaîne valide',
            'valeur.required' => 'La valeur du champ est requise',

        ];
    }

}
