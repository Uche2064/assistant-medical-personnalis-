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
            'type_contrat' => 'required|string',
            'commentaires' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type_contrat.required' => 'Le type de contrat est requis.',
            'type_contrat.in' => 'Le type de contrat doit être basic, standard, premium ou team.',
            'commentaires.max' => 'Les commentaires ne peuvent pas dépasser 1000 caractères.',
        ];
    }
} 