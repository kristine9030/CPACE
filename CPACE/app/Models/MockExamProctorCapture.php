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

    /** Frames taken because something happened, rather than on the timer. */
    public function isEventTriggered(): bool
    {
        return ! in_array($this->reason, [self::REASON_INTERVAL, self::REASON_START], true);
    }
}
