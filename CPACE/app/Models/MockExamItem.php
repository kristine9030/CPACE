<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One question inside a mock exam. The text and choices are COPIED from the
 * Test Bank when the item is added, so later edits to the source question
 * can never retroactively change an exam students have already sat.
 */
class MockExamItem extends Model
{
    protected $fillable = [
        'exam_id', 'source_question_id', 'topic_id', 'question_text', 'question_type',
        'difficulty', 'choices', 'explanation', 'points', 'sort_order',
    ];

    protected $casts = [
        'choices' => 'array',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function exam()
    {
        return $this->belongsTo(MockExam::class, 'exam_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Snapshot a Test Bank question into the payload for an exam item.
     *
     * Choices are re-labelled A, B, C... in stored order rather than trusting
     * question_choices.choice_label, because a bank question that has had a
     * choice deleted can be left with a gap (A, B, D) that would confuse the
     * exam paper.
     *
     * @return array<string, mixed>
     */
    public static function payloadFromQuestion(Question $question, int $sortOrder): array
    {
        $choices = $question->choices
            ->sortBy('choice_label')
            ->values()
            ->map(fn (QuestionChoice $choice, int $i) => [
                'label' => $question->question_type === 'true_false' ? ($i === 0 ? 'T' : 'F') : chr(65 + $i),
                'text' => $choice->choice_text,
                'is_correct' => (bool) $choice->is_correct,
            ])
            ->all();

        return [
            'source_question_id' => $question->id,
            'topic_id' => $question->topic_id,
            'question_text' => $question->question_text,
            'question_type' => $question->question_type ?: 'mcq',
            'difficulty' => $question->difficulty,
            'choices' => $choices,
            'explanation' => $question->explanation,
            'points' => 1,
            'sort_order' => $sortOrder,
        ];
    }

    /** The label ("A", "B", "T"...) of this item's correct choice. */
    public function correctLabel(): ?string
    {
        foreach ((array) $this->choices as $choice) {
            if (! empty($choice['is_correct'])) {
                return $choice['label'] ?? null;
            }
        }

        return null;
    }

    public function isCorrect(?string $label): bool
    {
        return $label !== null && $label === $this->correctLabel();
    }
}
