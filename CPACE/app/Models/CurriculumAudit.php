<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only history of what happened to a curriculum version. Written only
 * through App\Support\CurriculumAuditor, never directly.
 */
class CurriculumAudit extends Model
{
    public const ACTION_CREATED = 'version_created';
    public const ACTION_EDITED = 'version_edited';
    public const ACTION_PUBLISHED = 'version_published';
    public const ACTION_DISCARDED = 'version_discarded';
    public const ACTION_TOPIC_ADDED = 'topic_added';
    public const ACTION_TOPIC_EDITED = 'topic_edited';
    public const ACTION_TOPIC_REMOVED = 'topic_removed';
    public const ACTION_TOPICS_IMPORTED = 'topics_imported';
    public const ACTION_QUESTIONS_COPIED = 'questions_copied';
    public const ACTION_AI_GAP_FILLED = 'ai_gap_filled';

    public $timestamps = false;

    protected $fillable = ['curriculum_version_id', 'subject_id', 'user_id', 'action', 'details', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function label(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Started this curriculum',
            self::ACTION_EDITED => 'Edited the curriculum details',
            self::ACTION_PUBLISHED => 'Published this curriculum',
            self::ACTION_DISCARDED => 'Discarded this draft',
            self::ACTION_TOPIC_ADDED => 'Added a topic',
            self::ACTION_TOPIC_EDITED => 'Edited a topic',
            self::ACTION_TOPIC_REMOVED => 'Removed a topic',
            self::ACTION_TOPICS_IMPORTED => 'Imported topics from a TOS',
            self::ACTION_QUESTIONS_COPIED => 'Copied Test Bank questions forward',
            self::ACTION_AI_GAP_FILLED => 'AI drafted substitute questions for a short topic',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
