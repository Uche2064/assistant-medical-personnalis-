<?php

namespace App\Utils;

use App\Models\Question;
use App\Enums\TypeDonneeEnum;
use Illuminate\Validation\Rule;

class QuestionValidatorBuilder
{
    public static function buildRules(string|int $destinataire): array
    {
        $rules = [];

        $questions = Question::forDestinataire($destinataire)->get();

        if ($questions->isNotEmpty()) {
            $questionIds = $questions->pluck('id')->toArray();

            $rules['reponses'] = [
                'required',
                'array',
                function ($attribute, $value, $fail) use ($questionIds) {
                    $submittedKeys = array_keys($value);
                    $extraKeys = array_diff($submittedKeys, $questionIds);
                    if (!empty($extraKeys)) {
                        $fail("Les réponses contiennent des questions non attendues.");
                    }
                },
            ];

            foreach ($questions as $question) {
                $required = $question->isRequired() ? 'required' : 'nullable';

                $validation = match ($question->type_donnee) {
                    TypeDonneeEnum::TEXT => 'string',
                    TypeDonneeEnum::NUMBER => 'numeric',
                    TypeDonneeEnum::BOOLEAN => Rule::in(['oui', 'non', true, false]),
                    TypeDonneeEnum::DATE => 'date',
                    TypeDonneeEnum::FILE => 'file|mimes:jpeg,png,pdf,jpg|max:2048',
                    TypeDonneeEnum::RADIO => Rule::in($question->options ?? []),
                    TypeDonneeEnum::CHECKBOX => 'array',
                    default => 'string',
                };

                $rules['reponses.' . $question->id] = array_merge([$required], is_string($validation) ? explode('|', $validation) : [$validation]);
            }
        }

        return $rules;
    }
}
