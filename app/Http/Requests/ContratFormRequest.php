<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class ContratFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'technicien_id' => 'required|exists:personnels,id',
            'client_id' => 'required|exists:clients,id',
            'prime' => 'required|numeric|min:0',
            'numero_police' => ['required', 'string', 'unique:contrats,numero_police'],
            'photo_document' => 'required|array',
            'photo_document.*' => ['file', 'mimes:jpeg,png,jpg,pdf,doc,docx'],
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'technicien_id.required' => 'Le technicien est obligatoire',
            'technicien_id.exists' => 'Le technicien sélectionné n\'existe pas',
            'client_id.required' => 'Le client est obligatoire',
            'client_id.exists' => 'Le client sélectionné n\'existe pas',
            'prime.required' => 'La prime est obligatoire',
            'prime.numeric' => 'La prime doit être un nombre',
            'prime.min' => 'La prime ne peut pas être négative',
            'date_debut.required' => 'La date de début du contrat est obligatoire',
            'date_debut.date' => 'La date de début doit être une date valide',
            'date_fin.required' => 'La date de fin du contrat est obligatoire',
            'date_fin.date' => 'La date de fin doit être une date valide',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début',
        ];
    }
}
