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

        // Convertir le format des réponses
        $reponsesFormatees = [];
        foreach ($reponses as $reponse) {
            if (isset($reponse['question_id'])) {
                $questionId = $reponse['question_id'];
                $reponsesFormatees[$questionId] = $this->extraireValeurReponse($reponse);
            }
        }

        foreach ($questions as $questionId => $question) {
            $value = $reponsesFormatees[$questionId] ?? null;
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
                    if (!in_array($value, ['oui', 'non', true, false, 1, 0, 'true', 'false'], true)) {
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

    /**
     * Extraire la valeur de la réponse selon le format envoyé
     */
    private function extraireValeurReponse(array $reponse)
    {
        // Chercher la valeur dans les différents champs possibles
        $champs = ['reponse_text', 'reponse_bool', 'reponse_number', 'reponse_date', 'reponse_fichier'];
        
        foreach ($champs as $champ) {
            if (isset($reponse[$champ])) {
                $value = $reponse[$champ];
                
                // Conversion pour les booléens
                if ($champ === 'reponse_bool') {
                    if (is_bool($value)) return $value;
                    if (is_string($value)) {
                        return in_array(strtolower($value), ['true', 'oui', 'yes', '1']);
                    }
                    if (is_numeric($value)) {
                        return (bool) $value;
                    }
                }
                
                return $value;
            }
        }
        
        return null;
    }
}
