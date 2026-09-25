<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A camera or screen frame captured during a sitting.
 *
 * `path` points at the PRIVATE disk. These are photographs of students' faces
 * and screens, so they are never written to the public disk and never exposed
 * as a direct URL - the monitor serves them through an authorised route that
 * re-checks the viewer is the owning faculty or the Program Chair.
 */
class MockExamProctorCapture extends Model
{
    public $timestamps = false;

    public const KIND_CAMERA = 'camera';
    public const KIND_SCREEN = 'screen';

    public const KINDS = [self::KIND_CAMERA, self::KIND_SCREEN];

    /** Private-disk directory all captures live under. */
    public const ROOT = 'proctor';

    /**
     * How long frames that were kept as evidence survive after the exam.
     * Clean sittings keep nothing at all; see ProctorCaptureRetention.
     */
    public const RETENTION_DAYS = 14;

    /** Timer frames: routine, and the first thing discarded once a sitting ends. */
    public const REASON_INTERVAL = 'interval';
    public const REASON_START = 'start';

    protected $fillable = ['attempt_id', 'kind', 'path', 'captured_at', 'reason'];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(MockExamAttempt::class, 'attempt_id');
    }

    /**
     * Plain-language "why was this frame taken", shown as the caption headline.
     * The camera/screen source is shown separately, so a face flag can never
     * read like a screen problem.
     */
    public function reasonLabel(): string
    {
        return match ($this->reason) {
            self::REASON_INTERVAL => 'Routine check',
            self::REASON_START => 'Start of exam',
            MockExamProctorEvent::TYPE_BLUR => 'Left the exam window',
            MockExamProctorEvent::TYPE_HIDDEN => 'Switched tab or minimised',
            MockExamProctorEvent::TYPE_FULLSCREEN_EXIT => 'Exited fullscreen',
            MockExamProctorEvent::TYPE_NO_FACE => 'No face detected',
            MockExamProctorEvent::TYPE_MULTIPLE_FACES => 'More than one face',
            MockExamProctorEvent::TYPE_LOOKING_AWAY => 'Looking away',
            default => ucfirst(str_replace('_', ' ', (string) $this->reason)),
        };
    }

    /** Frames taken because something happened, rather than on the timer. */
    public function isEventTriggered(): bool
    {
        return ! in_array($this->reason, [self::REASON_INTERVAL, self::REASON_START], true);
    }
}
