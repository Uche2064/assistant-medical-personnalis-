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
            'client_id' => 'required|exists:clients,id',
            'technicien_id' => 'required|exists:personnels,id',
            'prime' => 'required|numeric|min:0',
            'date_signature' => 'required|date',
            'status' => 'sometimes|string',
            'photo_document' => 'sometimes|array',
            'photo_document.*' => 'sometimes|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire',
            'client_id.exists' => 'Le client sélectionné n\'existe pas',
            'technicien_id.required' => 'Le technicien est obligatoire',
            'technicien_id.exists' => 'Le technicien sélectionné n\'existe pas',
            'prime.required' => 'La prime est obligatoire',
            'prime.numeric' => 'La prime doit être un nombre',
            'prime.min' => 'La prime ne peut pas être négative',
            'date_signature.required' => 'La date de signature est obligatoire',
            'date_signature.date' => 'Format de date invalide',
            'status.in' => 'Le statut doit être actif, suspendu ou résilié',
        ];
    }
}
