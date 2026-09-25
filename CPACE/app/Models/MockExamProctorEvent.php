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

    // Raised by the in-browser face check (see mock-exam-take.blade.php).
    public const TYPE_NO_FACE = 'no_face';
    public const TYPE_MULTIPLE_FACES = 'multiple_faces';
    public const TYPE_LOOKING_AWAY = 'looking_away';

    // Raised by the runner when the shared screen is not the whole display.
    public const TYPE_PARTIAL_SCREEN = 'partial_screen';
    public const TYPE_SECOND_MONITOR = 'second_monitor';

    /**
     * Written by the server when it closes a sitting the student never
     * submitted. Deliberately NOT in TYPES: a client must never be able to post
     * it, and it is a note on the record, not a flag (no flag_count, no weight).
     */
    public const TYPE_AUTO_CLOSED = 'auto_closed';

    /** The only types the ingest endpoint will accept from the client. */
    public const TYPES = [
        self::TYPE_BLUR, self::TYPE_HIDDEN, self::TYPE_FULLSCREEN_EXIT,
        self::TYPE_PASTE_BLOCKED, self::TYPE_CAMERA_LOST, self::TYPE_SCREEN_LOST,
        self::TYPE_NO_FACE, self::TYPE_MULTIPLE_FACES, self::TYPE_LOOKING_AWAY,
        self::TYPE_PARTIAL_SCREEN, self::TYPE_SECOND_MONITOR,
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
            self::TYPE_NO_FACE => 'No face visible on camera',
            self::TYPE_MULTIPLE_FACES => 'More than one face on camera',
            self::TYPE_LOOKING_AWAY => 'Looking away from the screen',
            self::TYPE_PARTIAL_SCREEN => 'Shared a window or tab, not the whole screen',
            self::TYPE_SECOND_MONITOR => 'A second monitor is connected',
            self::TYPE_AUTO_CLOSED => 'Closed automatically — the student did not submit',
            default => $this->type,
        };
    }

    /**
     * Losing camera or screen mid-exam is a deliberate act, so it reads as
     * more serious than a single alt-tab in the monitor.
     */
    public function isSevere(): bool
    {
        return in_array($this->type, [self::TYPE_CAMERA_LOST, self::TYPE_SCREEN_LOST, self::TYPE_MULTIPLE_FACES], true);
    }
}
