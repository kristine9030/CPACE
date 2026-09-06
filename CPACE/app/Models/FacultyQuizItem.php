<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One question inside a class quiz. Choices are stored inline as JSON
 * ([{label, text, is_correct}]) so the quiz is self-contained and immune to
 * later Test Bank edits; source_question_id only records where it came from.
 */
class FacultyQuizItem extends Model
{
    protected $fillable = [
        'quiz_id', 'source_question_id', 'question_text', 'question_type',
        'choices', 'explanation', 'points', 'sort_order',
    ];

    protected $casts = [
        'choices' => 'array',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(FacultyQuiz::class, 'quiz_id');
    }

    /** Label (A/B/C/D or T/F) of the correct choice, or null if none is marked. */
    public function correctLabel(): ?string
    {
        foreach ($this->choices ?? [] as $choice) {
            if (! empty($choice['is_correct'])) {
                return (string) $choice['label'];
            }
        }

        return null;
    }
}
