<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One entry in a mock exam's audit trail. Faculty collaborate on an exam and
 * the Program Chair edits it during review, so "who changed what, and when"
 * has to be answerable after the fact - that is what this table is for.
 *
 * Write through App\Support\MockExamAuditor::record() rather than creating
 * rows directly, so every mutation path logs the same way.
 */
class MockExamAudit extends Model
{
    /** Only created_at is meaningful - an audit entry is never updated. */
    public const UPDATED_AT = null;

    public const ACTION_CREATED = 'created';
    public const ACTION_SETTINGS = 'settings_changed';
    public const ACTION_TOPICS = 'topics_changed';
    public const ACTION_ITEMS = 'items_changed';
    public const ACTION_SUBMITTED = 'submitted_for_review';
    public const ACTION_RETURNED = 'returned';
    public const ACTION_PUBLISHED = 'published';
    public const ACTION_CLOSED = 'closed';

    protected $fillable = ['exam_id', 'user_id', 'action', 'details'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(MockExam::class, 'exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Human-readable label for the trail panel. */
    public function label(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'created the exam',
            self::ACTION_SETTINGS => 'changed the settings',
            self::ACTION_TOPICS => 'changed the topics',
            self::ACTION_ITEMS => 'changed the questions',
            self::ACTION_SUBMITTED => 'submitted it for review',
            self::ACTION_RETURNED => 'returned it for revision',
            self::ACTION_PUBLISHED => 'published the exam',
            self::ACTION_CLOSED => 'closed the exam',
            default => $this->action,
        };
    }
}
