<?php

namespace App\Http\Requests;

use App\Enums\TypeClientEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ClientUpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'prime' => ['sometimes', 'numeric'],
                'date_paiement_prime' => ['somtimes', 'date'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Error de validation', 422, $validator->errors()));
    }

    public function messages() {
        return [
            'profession.string' => 'La profession doit  tre une cha ne de caract res.',
            'profession.max' => 'La profession ne doit pas d passer :max caract res.',

            'type_client.string' => 'Le type de client doit  tre une cha ne de caract res.',
            'type_client.in' => 'Le type de client doit  tre l\'un des suivants : ' . implode(', ', TypeClientEnum::values()),

            'prime.numeric' => 'La prime doit  tre un nombre.',
            'date_paiement_prime.date' => 'La date de paiement de la prime doit  tre une date.',
        ];

    }

}
