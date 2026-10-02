<?php

namespace App\Models;

use App\Models\Concerns\HasExhibit;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasExhibit;

    public const SOURCE_FACULTY = 'faculty';
    public const SOURCE_AI_SUBSTITUTE = 'ai_substitute';

    public const REVIEW_PENDING = 'pending';
    public const REVIEW_APPROVED = 'approved';
    public const REVIEW_REJECTED = 'rejected';

    protected $fillable = [
        'topic_id',
        'created_by',
        'source',
        'question_text',
        'question_type',
        'difficulty',
        'explanation',
        'image_path',
        'table_data',
        'is_active',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'table_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /** An AI substitute question still waiting for the faculty or chair to approve it. */
    public function isPendingAiReview(): bool
    {
        return $this->source === self::SOURCE_AI_SUBSTITUTE && $this->review_status === self::REVIEW_PENDING;
    }

    public function scopePendingAiReview($query)
    {
        return $query->where('questions.source', self::SOURCE_AI_SUBSTITUTE)
            ->where('questions.review_status', self::REVIEW_PENDING);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function choices()
    {
        return $this->hasMany(QuestionChoice::class);
    }

    public function variants()
    {
        return $this->hasMany(QuestionVariant::class);
    }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
