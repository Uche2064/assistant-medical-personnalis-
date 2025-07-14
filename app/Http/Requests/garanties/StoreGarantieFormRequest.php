<?php

namespace App\Http\Requests\garanties;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreGarantieFormRequest extends FormRequest
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
            'libelle' => ['required', 'string', 'unique:garanties,libelle'],
            'prix_standard' => ['required', 'numeric', 'min:0'],
            'plafond' => ['required', 'numeric', 'min:0'],
            'taux_couverture' => ['required', 'numeric', 'min:0', 'max:100'],
            'categorie_garantie_id' => ['required', 'integer'],
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

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le champ libelle est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.unique' => 'Le libellé est déjà utilisé.',
            'plafond.required' => 'Le champ plafond est obligatoire.',
            'plafond.numeric' => 'Le plafond doit être un nombre.',
            'plafond.min' => 'Le plafond doit être supérieur ou égale à 0.',
            'taux_couverture.required' => 'Le champ taux_couverture est obligatoire.',
            'taux_couverture.numeric' => 'Le taux de couverture doit être un nombre.',
            'taux_couverture.min' => 'Le taux_couverture doit être supérieur ou égale à 0.',
            'taux_couverture.max' => 'Le taux_couverture doit être inférieur ou égale à 100.',
            'categorie_garantie_id.required' => 'Le champ categorie_garantie_id est obligatoire.',
            'categorie_garantie_id.integer' => 'Le champ categorie_garantie_id doit être un entier.',
            'prix_standard.required' => 'Le champ prix_standard est obligatoire.',
            'prix_standard.numeric' => 'Le prix_standard doit être un nombre.',
            'prix_standard.min' => 'Le prix_standard doit être supérieur ou égale à 0.',
        ];
    }
}
