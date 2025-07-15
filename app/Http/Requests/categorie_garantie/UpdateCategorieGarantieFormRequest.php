<?php

namespace App\Http\Requests\categorie_garantie;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateCategorieGarantieFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('medecin_controleur');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "libelle" => ['sometimes', 'string', 'unique:categories_garanties,libelle'],
            "description" => ['nullable', 'string'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(
            $validator->errors(),
            'Validation failed',
            422
        ));
    }

    public function messages(): array
    {
        return [
            'medecin_controleur_id.exists' => 'Le medecin_controleur n\'existe pas.',
            'libelle.unique' => 'Le champ libelle doit être unique.',
            'description.string' => 'La description doit être une chaîne de caractères.',
        ];
    }
}
