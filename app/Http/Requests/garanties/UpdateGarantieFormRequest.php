<?php

namespace App\Http\Requests\garanties;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateGarantieFormRequest extends FormRequest
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
            'libelle' => ['sometimes', 'string', 'unique:garanties,libelle'],
            'plafond' => ['sometimes', 'numeric', 'min:0'],
            'taux_couverture' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'categorie_garantie_id' => ['sometimes', 'integer'],
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

}
