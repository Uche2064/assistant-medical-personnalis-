<?php

namespace App\Http\Requests\medecin;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use Illuminate\Foundation\Http\FormRequest;

class QuestionsUpdateFormRequest extends FormRequest
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
            'libelle' => 'sometimes|required|string|max:255',
            'type_donnees' => 'sometimes|required|string|in:' . implode(',', TypeDonneeEnum::values()),
            'destinataire' => 'sometimes|required|string|in:' . implode(',', TypeDemandeurEnum::values()),
            'obligatoire' => 'sometimes|boolean',
            'est_actif' => 'sometimes|boolean',
            'options' => 'sometimes|nullable|json',
        ];
    }
}
