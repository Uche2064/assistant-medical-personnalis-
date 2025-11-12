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
            'contrat_id' => 'required|exists:types_contrats,id',
            'commentaires_technicien' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contrat_id.required' => 'Le contrat est requis.',
            'contrat_id.exists' => 'Le contrat n\'existe pas.',
            'commentaires_technicien.max' => 'Les commentaires ne peuvent pas dépasser 1000 caractères.',
            'pourcentage_gestion.numeric' => 'Le pourcentage de gestion doit être un nombre.',
            'pourcentage_gestion.min' => 'Le pourcentage de gestion doit être supérieur à 0.',
            'pourcentage_gestion.max' => 'Le pourcentage de gestion doit être inférieur à 100.',
        ];
    }
}
