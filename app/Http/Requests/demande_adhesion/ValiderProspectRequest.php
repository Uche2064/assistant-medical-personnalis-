<?php

namespace App\Http\Requests\demande_adhesion;

use App\Enums\TypeContratEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValiderProspectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('technicien');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_contrat' => 'required|string|in:' . implode(',', TypeContratEnum::values()),
            'prime_proposee' => 'required|numeric|min:0',
            'commentaires' => 'nullable|string|max:1000',
            'garanties_incluses' => 'nullable|array',
            'garanties_incluses.*' => 'exists:garanties,id',
            'taux_couverture' => 'nullable|numeric|min:0|max:100',
            'frais_gestion' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type_contrat.required' => 'Le type de contrat est requis.',
            'type_contrat.in' => 'Le type de contrat sélectionné est invalide.',
            'prime_proposee.required' => 'La prime proposée est requise.',
            'prime_proposee.numeric' => 'La prime proposée doit être un nombre.',
            'prime_proposee.min' => 'La prime proposée ne peut pas être négative.',
            'commentaires.max' => 'Les commentaires ne peuvent pas dépasser 1000 caractères.',
            'garanties_incluses.array' => 'Les garanties incluses doivent être un tableau.',
            'garanties_incluses.*.exists' => 'Une ou plusieurs garanties sélectionnées n\'existent pas.',
            'taux_couverture.numeric' => 'Le taux de couverture doit être un nombre.',
            'taux_couverture.min' => 'Le taux de couverture ne peut pas être négatif.',
            'taux_couverture.max' => 'Le taux de couverture ne peut pas dépasser 100%.',
            'frais_gestion.numeric' => 'Les frais de gestion doivent être un nombre.',
            'frais_gestion.min' => 'Les frais de gestion ne peuvent pas être négatifs.',
            'frais_gestion.max' => 'Les frais de gestion ne peuvent pas dépasser 100%.',
        ];
    }
} 