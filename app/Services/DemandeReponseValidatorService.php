<?php

namespace App\Services;

use App\Models\Question;
use App\Enums\TypeDonneeEnum;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DemandeReponseValidatorService
{
    public function validate(array $reponses, int|string $destinataire, string $prefix = 'reponses')
    {
        $questions = Question::forDestinataire($destinataire)->get()->keyBy('id');

        $errors = [];

        foreach ($questions as $questionId => $question) {
            $value = $reponses[$questionId] ?? null;
            $fieldKey = "$prefix.$questionId";

            // Vérification obligatoire
            if ($question->isRequired() && is_null($value)) {
                $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » est obligatoire."];
                continue;
            }

            // Si pas de réponse et pas obligatoire, on saute
            if (is_null($value)) continue;

            // Vérifications par type
            switch ($question->type_donnee) {
                case TypeDonneeEnum::BOOLEAN:
                    if (!in_array($value, ['oui', 'non', true, false, 1, 0], true)) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » doit être Oui ou Non."];
                    }
                    break;

                case TypeDonneeEnum::NUMBER:
                    if (!is_numeric($value)) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » doit être un nombre."];
                    }
                    break;

                case TypeDonneeEnum::DATE:
                    if (!strtotime($value)) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » doit être une date valide."];
                    }
                    break;

                case TypeDonneeEnum::RADIO:
                    $options = $question->options ?? [];
                    if (!in_array($value, $options)) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » n'est pas valide."];
                    }
                    break;

                case TypeDonneeEnum::CHECKBOX:
                    if (!is_array($value)) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » doit être une liste."];
                    }
                    break;

                case TypeDonneeEnum::FILE:
                    if (!is_object($value) || !method_exists($value, 'getClientOriginalName')) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » doit être un fichier valide."];
                    }
                    break;

                case TypeDonneeEnum::TEXT:
                default:
                    if (!is_string($value)) {
                        $errors[$fieldKey] = ["La réponse à la question « {$question->libelle} » doit être du texte."];
                    }
                    break;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
