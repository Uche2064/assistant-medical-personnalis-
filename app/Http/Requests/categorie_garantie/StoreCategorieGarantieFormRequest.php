<?php

namespace App\Http\Requests\categorie_garantie;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreCategorieGarantieFormRequest extends FormRequest
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
            "libelle" => ['required', 'string', 'unique:categories_garanties,libelle'],
            "description" => ['nullable', 'string'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error("Erreur lors de la validation", 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le champ libelle est obligatoire.',
            'libelle.unique' => 'Ce libellé est déjà pris.',
            'description.string' => 'La description doit être une chaîne de caractères.',
        ];
    }
}
