<?php

namespace App\Http\Requests\admin;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompagnieFormRequest extends FormRequest
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
            'nom' => ['required','string','max:255','unique:compagnies,nom'],
            'adresse' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:compagnies,email'],
            'telephone' => ['nullable','string','max:50','unique:compagnies,telephone'],
            'site_web' => ['nullable','url','max:255','unique:compagnies,site_web'],
            'logo' => ['nullable','string','max:255'],
            'description' => ['nullable','string','max:255'],
            'est_actif' => ['nullable','boolean'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse::error('Erreur de validation', 422 ,$validator->errors());
    
        throw new HttpResponseException($response);
    }
}
