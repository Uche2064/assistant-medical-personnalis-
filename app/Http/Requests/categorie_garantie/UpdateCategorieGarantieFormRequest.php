<?php

namespace App\Http\Requests\categorie_garantie;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

    // Dans UpdateCategorieGarantieFormRequest
    public function rules()
    {
        $categorieId = $this->route('id');

        return [
            'libelle' => [
                'sometimes',
                'string',
                Rule::unique('categories_garanties', 'libelle')
                    ->ignore($categorieId)
                    ->whereNull('deleted_at') // Ignorer les supprimés
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(
            'Validation failed',
            422,
            $validator->errors()
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
