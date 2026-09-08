<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One parsed question staged for faculty review before it becomes a real
 * Test Bank Question. `choices` mirrors the shape faculty_quiz_items uses:
 * [{"label":"A","text":"...","is_correct":true}, ...].
 */
class QuestionImportItem extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'batch_id',
        'topic_id',
        'question_text',
        'question_type',
        'choices',
        'explanation',
        'difficulty',
        'source',
        'confidence',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'choices'    => 'array',
        'confidence' => 'integer',
    ];

    public function batch()
    {
        return $this->belongsTo(QuestionImportBatch::class, 'batch_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /** The correct choice's label ("A"), or null if none is marked (needs faculty attention). */
    public function correctLabel(): ?string
    {
        foreach ($this->choices ?? [] as $choice) {
            if (! empty($choice['is_correct'])) {
                return $choice['label'] ?? null;
            }
        }

        return null;
    }
}
