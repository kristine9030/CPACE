<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One subject's mock exam for one sitting. A mock exam is never combined
 * across subjects - FAR and AUD on the same day are two separate MockExam
 * rows that happen to share a MockExamEvent (and therefore a redeem code).
 *
 * Lifecycle: draft -> for_review -> published -> closed, with the Chair able
 * to send a for_review exam back to draft with a note. Once published the
 * exam is frozen: every edit path checks isEditable() first.
 */
class MockExam extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FOR_REVIEW = 'for_review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    /** Ceiling on items in a single subject exam. */
    public const MAX_ITEMS = 100;

    /** Real CPALE allots three hours per subject. */
    public const DEFAULT_DURATION = 180;

    public const MODE_MANUAL = 'manual';
    public const MODE_AUTO = 'auto';
    public const MODE_ALL = 'all';

    protected $fillable = [
        'event_id', 'subject_id', 'created_by', 'title', 'status', 'scheduled_at',
        'duration_minutes', 'total_items', 'topic_mode', 'question_mode', 'review_note',
        'submitted_for_review_at', 'reviewed_by', 'published_by', 'published_at', 'closed_at', 'version',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'submitted_for_review_at' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'duration_minutes' => 'integer',
        'total_items' => 'integer',
        'version' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(MockExamEvent::class, 'event_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function items()
    {
        return $this->hasMany(MockExamItem::class, 'exam_id')->orderBy('sort_order')->orderBy('id');
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'mock_exam_topics', 'exam_id', 'topic_id');
    }

    public function attempts()
    {
        return $this->hasMany(MockExamAttempt::class, 'exam_id');
    }

    public function audits()
    {
        return $this->hasMany(MockExamAudit::class, 'exam_id')->latest('created_at')->latest('id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isForReview(): bool
    {
        return $this->status === self::STATUS_FOR_REVIEW;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /** Published and closed exams are frozen - nobody edits them, not even the Chair. */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_FOR_REVIEW], true);
    }

    /**
     * Faculty assigned to this subject collaborate on the exam - the creator
     * holds no special privilege over co-assigned colleagues. The Program
     * Chair can edit any exam as part of review.
     */
    public function canBeEditedBy(User $user): bool
    {
        if (! $this->isEditable()) {
            return false;
        }

        return $this->canBeViewedBy($user);
    }

    public function canBeViewedBy(User $user): bool
    {
        if ($user->isChair()) {
            return true;
        }

        return $user->isFaculty()
            && $user->assignedSubjects()->where('subjects.id', $this->subject_id)->exists();
    }

    public function endsAt(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    /** True while students are allowed to be sitting this exam. */
    public function isOpenNow(): bool
    {
        return $this->isPublished()
            && $this->scheduled_at->isPast()
            && $this->endsAt()->isFuture();
    }

    /**
     * Student-facing state for a card: upcoming | open | ended, on top of
     * which the controller layers the student's own attempt status.
     */
    public function window(): string
    {
        if ($this->scheduled_at->isFuture()) {
            return 'upcoming';
        }

        return $this->endsAt()->isFuture() ? 'open' : 'ended';
    }

    public function totalPoints(): int
    {
        return (int) $this->items->sum('points');
    }
}
