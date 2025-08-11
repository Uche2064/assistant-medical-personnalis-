<?php

namespace App\Http\Requests\garanties;

use App\Helpers\ApiResponse;
use App\Models\Garantie;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
    public function rules()
    {
        $garantieId = $this->route('id');
        
        return [
            'libelle' => [
                'sometimes',
                'string',
                Rule::unique('garanties', 'libelle')
                    ->ignore($garantieId)
                    ->whereNull('deleted_at')
            ],
            'plafond' => ['sometimes', 'numeric', 'min:0'],
            'taux_couverture' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'prix_standard' => ['sometimes', 'numeric', 'min:0'],
            'categorie_garantie_id' => ['sometimes', 'exists:categories_garanties,id'],
            'description' => ['nullable', 'string'],
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
