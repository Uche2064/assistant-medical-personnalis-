<?php

namespace App\Http\Requests\admin;

use App\Helpers\ApiResponse;
use App\Models\Gestionnaire;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GestionnaireUpdateFormRequest extends FormRequest
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
        $userId = $this->route('id') ? optional(Gestionnaire::find($this->route('id')))->user_id : null;

        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenoms' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $userId],
            'contact' => ['sometimes', 'string', 'max:50'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,' . $userId],
            'adresse' => ['sometimes', 'json', 'max:255'],
            'sexe' => ['sometimes', 'string', 'max:255'],
            'date_naissance' => ['sometimes', 'date'],
            'photo' => ['sometimes', 'string', 'max:255'],
            'compagnie_id' => ['sometimes', 'exists:compagnies,id'],
            'est_actif' => ['sometimes', 'boolean'],
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Erreur de validation', 422, $validator->errors()));
    }
}
