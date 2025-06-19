<?php

namespace App\Http\Requests\gestionnaire;

use App\Enums\SexeEnum;
use App\Enums\TypePersonnelEnum;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PersonnelFormRequest extends FormRequest
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
        // Récupérer l'ID de la compagnie du gestionnaire connecté
        $compagnieId = Auth::user()->gestionnaire->compagnie_id;
        
        // Déterminer si le contact est obligatoire selon le type de personnel
        $contactRules = ['string', 'max:50', 'unique:users,contact'];
        
        // Si le type est Commercial, le contact devient obligatoire
        if ($this->input('type_personnel') === TypePersonnelEnum::COMMERCIAL->value) {
            $contactRules[] = 'required';
        } else {
            $contactRules[] = 'nullable';
        }
        
        // Vérifier s'il s'agit d'une création (POST) ou d'une mise à jour (PUT)
        $isUpdate = $this->isMethod('PUT');
        
        // Règles de base communes
        $rules = [
            'prenoms' => ['nullable', 'string', 'max:255'],
            'email' => [
                !$isUpdate ? 'required' : 'nullable', 
                'email', 
                'max:255',
                $isUpdate ? Rule::unique('users')->ignore($this->route('id')) : 'unique:users,email'
            ],
            'contact' => $contactRules,
            'username' => [
                'nullable', 
                'string',
                function($attribute, $value, $fail) use ($compagnieId) {
                    if (!$value) return;
                    
                    $exists = User::where('username', $value)
                        ->whereHas('personnel', function($q) use ($compagnieId) {
                            $q->where('compagnie_id', $compagnieId);
                        })->exists();
                    if ($exists) {
                        $fail('Ce nom d\'utilisateur existe déjà dans cette compagnie.');
                    }
                }
            ],
            'adresse' => [ !$isUpdate ? 'required' : 'nullable', 'json', 'max:255'],
            'sexe' => ['nullable', Rule::in(SexeEnum::values())],
            'date_naissance' => ['nullable', 'date'],
            'photo' => ['nullable', 'string', 'max:255'],
            'type_personnel' => [ !$isUpdate ? 'required' : 'nullable', Rule::in(TypePersonnelEnum::values())]
        ];
        
        // Pour la création, le nom est obligatoire
        // Pour la mise à jour, le nom est optionnel
        if ($isUpdate) {
            $rules['nom'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['nom'] = ['required', 'string', 'max:255'];
        }
        
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error($validator->errors()->first(), 422)
        );
    }
}
