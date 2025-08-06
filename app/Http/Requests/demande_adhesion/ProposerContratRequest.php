<?php

namespace App\Http\Requests\demande_adhesion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProposerContratRequest extends FormRequest
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
            'contrat_id' => 'required|exists:contrats,id',
            'prime_proposee' => 'required|numeric|min:0',
            'commentaires' => 'nullable|string|max:1000',
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
            'contrat_id.required' => 'Le contrat sélectionné est requis.',
            'contrat_id.exists' => 'Le contrat sélectionné n\'existe pas.',
            'prime_proposee.required' => 'La prime proposée est requise.',
            'prime_proposee.numeric' => 'La prime proposée doit être un nombre.',
            'prime_proposee.min' => 'La prime proposée ne peut pas être négative.',
            'commentaires.max' => 'Les commentaires ne peuvent pas dépasser 1000 caractères.',
            'taux_couverture.numeric' => 'Le taux de couverture doit être un nombre.',
            'taux_couverture.min' => 'Le taux de couverture ne peut pas être négatif.',
            'taux_couverture.max' => 'Le taux de couverture ne peut pas dépasser 100%.',
            'frais_gestion.numeric' => 'Les frais de gestion doivent être un nombre.',
            'frais_gestion.min' => 'Les frais de gestion ne peuvent pas être négatifs.',
            'frais_gestion.max' => 'Les frais de gestion ne peuvent pas dépasser 100%.',
        ];
    }
} 