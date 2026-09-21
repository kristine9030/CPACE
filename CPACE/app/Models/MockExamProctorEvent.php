<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single behavioural flag raised during a sitting. These are tiny and are
 * the durable evidence record - unlike captures, they are never purged.
 */
class MockExamProctorEvent extends Model
{
    public $timestamps = false;

    public const TYPE_BLUR = 'blur';
    public const TYPE_HIDDEN = 'visibility_hidden';
    public const TYPE_FULLSCREEN_EXIT = 'fullscreen_exit';
    public const TYPE_PASTE_BLOCKED = 'paste_blocked';
    public const TYPE_CAMERA_LOST = 'camera_lost';
    public const TYPE_SCREEN_LOST = 'screen_lost';

    /** The only types the ingest endpoint will accept from the client. */
    public const TYPES = [
        self::TYPE_BLUR, self::TYPE_HIDDEN, self::TYPE_FULLSCREEN_EXIT,
        self::TYPE_PASTE_BLOCKED, self::TYPE_CAMERA_LOST, self::TYPE_SCREEN_LOST,
    ];

    protected $fillable = ['attempt_id', 'type', 'occurred_at', 'meta'];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(MockExamAttempt::class, 'attempt_id');
    }

    public function label(): string
    {
        return match ($this->type) {
            self::TYPE_BLUR => 'Left the exam window',
            self::TYPE_HIDDEN => 'Switched tab or minimised',
            self::TYPE_FULLSCREEN_EXIT => 'Exited fullscreen',
            self::TYPE_PASTE_BLOCKED => 'Paste blocked',
            self::TYPE_CAMERA_LOST => 'Camera stopped',
            self::TYPE_SCREEN_LOST => 'Screen sharing stopped',
            default => $this->type,
        };
    }

    /**
     * Losing camera or screen mid-exam is a deliberate act, so it reads as
     * more serious than a single alt-tab in the monitor.
     */
    public function isSevere(): bool
    {
        return in_array($this->type, [self::TYPE_CAMERA_LOST, self::TYPE_SCREEN_LOST], true);
    }
}
