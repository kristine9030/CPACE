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
        'audience_years', 'audience_sections', 'audience_batch_years',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'submitted_for_review_at' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'duration_minutes' => 'integer',
        'total_items' => 'integer',
        'version' => 'integer',
        'audience_years' => 'array',
        'audience_sections' => 'array',
        'audience_batch_years' => 'array',
    ];

    /**
     * Who may take this exam. The redeem code is shared by everyone who hears
     * it, so the code alone must not be enough: the Chair names the year levels
     * (and optionally sections and enrollment batches) that are allowed, and a
     * student outside them is refused at redeem and at every later step.
     *
     * No year levels recorded = open to everyone, which is how exams published
     * before this control existed keep working. Sections and batch years each
     * narrow independently within the chosen years.
     */
    public function hasAudience(): bool
    {
        return ! empty($this->audience_years) || ! empty($this->audience_sections) || ! empty($this->audience_batch_years);
    }

    public function admitsStudent(?int $yearLevel, ?string $section, ?string $batchYear = null): bool
    {
        if (! $this->hasAudience()) {
            return true;
        }

        $years = array_map('intval', (array) $this->audience_years);
        if ($years && ($yearLevel === null || ! in_array((int) $yearLevel, $years, true))) {
            return false;
        }

        $sections = array_map(fn ($s) => mb_strtolower(trim((string) $s)), (array) $this->audience_sections);
        if ($sections && ! ($section !== null && in_array(mb_strtolower(trim($section)), $sections, true))) {
            return false;
        }

        $batchYears = array_map(fn ($b) => mb_strtolower(trim((string) $b)), (array) $this->audience_batch_years);
        if ($batchYears && ! ($batchYear !== null && in_array(mb_strtolower(trim($batchYear)), $batchYears, true))) {
            return false;
        }

        return true;
    }

    /** "4th Year, 5th Year · BSA 4-A · 2026-2027" for the Chair and student screens. */
    public function audienceLabel(): string
    {
        if (! $this->hasAudience()) {
            return 'All students';
        }

        $years = collect((array) $this->audience_years)->map(fn ($y) => Section::YEAR_LABELS[(int) $y] ?? "Year {$y}")->implode(', ');
        $sections = collect((array) $this->audience_sections)->implode(', ');
        $batchYears = collect((array) $this->audience_batch_years)->implode(', ');

        return collect([$years, $sections, $batchYears])->filter()->implode(' · ');
    }

    /**
     * How many active, non-alumni students currently match this exam's
     * audience - shown to the Chair/faculty as "who's expected", now that
     * there's no redeem code and no registration to count instead.
     */
    public function eligibleStudentsCount(): int
    {
        $years = array_map('intval', (array) $this->audience_years);
        $sections = array_map(fn ($s) => mb_strtolower(trim((string) $s)), (array) $this->audience_sections);
        $batchYears = array_map(fn ($b) => mb_strtolower(trim((string) $b)), (array) $this->audience_batch_years);

        return User::query()
            ->where('role_id', Role::STUDENT)
            ->where('is_active', true)
            ->whereHas('studentProfile', function ($q) use ($years, $sections, $batchYears) {
                $q->where(fn ($q2) => $q2->whereNull('is_alumni')->orWhere('is_alumni', false));
                if ($years) {
                    $q->whereIn('year_level', $years);
                }
                if ($sections) {
                    $q->whereRaw('LOWER(TRIM(section)) IN (' . implode(',', array_fill(0, count($sections), '?')) . ')', $sections);
                }
                if ($batchYears) {
                    $q->whereRaw('LOWER(TRIM(batch_year)) IN (' . implode(',', array_fill(0, count($batchYears), '?')) . ')', $batchYears);
                }
            })
            ->count();
    }

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
