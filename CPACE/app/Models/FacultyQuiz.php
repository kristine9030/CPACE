<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A class quiz authored by a faculty member: a fixed set of questions with a
 * deadline, shared with students through a link. See the migration for the
 * draft / published / closed lifecycle.
 */
class FacultyQuiz extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'faculty_id', 'subject_id', 'title', 'instructions', 'status', 'share_token',
        'opens_at', 'due_at', 'time_limit_minutes', 'shuffle_questions', 'show_results', 'published_at',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'due_at' => 'datetime',
        'published_at' => 'datetime',
        'shuffle_questions' => 'boolean',
        'show_results' => 'boolean',
        'time_limit_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (FacultyQuiz $quiz) {
            $quiz->share_token = $quiz->share_token ?: static::newShareToken();
        });
    }

    public static function newShareToken(): string
    {
        do {
            $token = Str::lower(Str::random(24));
        } while (static::where('share_token', $token)->exists());

        return $token;
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function items()
    {
        return $this->hasMany(FacultyQuizItem::class, 'quiz_id')->orderBy('sort_order')->orderBy('id');
    }

    public function attempts()
    {
        return $this->hasMany(FacultyQuizAttempt::class, 'quiz_id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Fine-grained availability for the student side:
     *   draft | closed | upcoming (published but opens_at is in the future)
     *   | expired (published but the deadline passed) | open
     */
    public function availability(): string
    {
        if ($this->status === self::STATUS_CLOSED) {
            return 'closed';
        }
        if ($this->status !== self::STATUS_PUBLISHED) {
            return 'draft';
        }
        if ($this->opens_at && $this->opens_at->isFuture()) {
            return 'upcoming';
        }
        if ($this->due_at && $this->due_at->isPast()) {
            return 'expired';
        }

        return 'open';
    }

    public function isOpen(): bool
    {
        return $this->availability() === 'open';
    }

    /** Absolute link the faculty shares with students. */
    public function shareUrl(): string
    {
        return route('class-quiz.show', $this->share_token);
    }

    public function totalPoints(): int
    {
        return (int) $this->items->sum('points');
    }
}
