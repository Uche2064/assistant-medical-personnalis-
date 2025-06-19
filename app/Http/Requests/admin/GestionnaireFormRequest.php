<?php

namespace App\Http\Requests\admin;

use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class GestionnaireFormRequest extends FormRequest
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
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'unique:users,username'],
            'adresse' => ['required', 'json', 'max:255'],
            'sexe' => ['nullable', Rule::in(SexeEnum::values())],
            'date_naissance' => ['nullable', 'date'],
            'est_actif' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'string', 'max:255'],
            'compagnie_id' => ['required', 'exists:compagnies,id'],
        ];
    }

    
    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse::error('Erreur de validation', 422 ,$validator->errors());
    
        throw new HttpResponseException($response);
    }
}
