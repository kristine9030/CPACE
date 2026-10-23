<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * Validates one hand-written question coming from a builder (class quiz or mock exam)
 * and turns it into the columns an item row stores. One implementation so both builders
 * hold their authors to the same rules: text, 2-26 non-blank choices, exactly one correct
 * answer, formatting cleaned by RichText.
 */
class CustomQuestionParser
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>  question_text, question_type, choices, explanation, points (+ exhibit columns when set)
     */
    public static function parse(array $row, int $number, string $errorKey): array
    {
        $text = (string) RichText::sanitize((string) ($row['question_text'] ?? ''));
        if ($text === '') {
            throw ValidationException::withMessages([$errorKey => "Question {$number} has no text."]);
        }

        $type = ($row['question_type'] ?? 'mcq') === 'true_false' ? 'true_false' : 'mcq';
        $choices = [];
        $correct = 0;
        foreach (array_values((array) ($row['choices'] ?? [])) as $j => $choice) {
            $choiceText = trim((string) ($choice['text'] ?? ''));
            if ($choiceText === '') {
                continue;
            }
            $isCorrect = ! empty($choice['is_correct']);
            $correct += $isCorrect ? 1 : 0;
            $choices[] = [
                'label' => $type === 'true_false' ? ($j === 0 ? 'T' : 'F') : chr(65 + count($choices)),
                'text' => $choiceText,
                'is_correct' => $isCorrect,
            ];
        }

        if (count($choices) < 2 || count($choices) > 26) {
            throw ValidationException::withMessages([$errorKey => "Question {$number} needs between 2 and 26 answer choices."]);
        }
        if ($correct !== 1) {
            throw ValidationException::withMessages([$errorKey => "Question {$number} must have exactly one correct answer."]);
        }

        return [
            'question_text' => $text,
            'question_type' => $type,
            'choices' => $choices,
            'explanation' => RichText::sanitize((string) ($row['explanation'] ?? '')) ?: null,
            'points' => max(1, min(100, (int) ($row['points'] ?? 1))),
        ] + QuestionExhibit::attributesOf((object) [
            'image_path' => $row['image_path'] ?? null,
            'table_data' => QuestionExhibit::sanitizeTable($row['table_data'] ?? null),
        ]);
    }
}
