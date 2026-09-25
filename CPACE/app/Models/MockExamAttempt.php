<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One student's sitting of one mock exam. Unique per (exam, student) - a mock
 * exam is a single-shot graded assessment, not repeatable practice.
 *
 * Note this deliberately does NOT create a quiz_sessions row: mock results
 * stay out of normal quiz history and off the leaderboard, while still
 * feeding weak-area detection through performance_records.
 */
class MockExamAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'exam_id', 'student_id', 'started_at', 'submitted_at', 'answers',
        'score', 'total_points', 'percent', 'is_late', 'flag_count', 'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answers' => 'array',
        'score' => 'integer',
        'total_points' => 'integer',
        'percent' => 'decimal:2',
        'is_late' => 'boolean',
        'flag_count' => 'integer',
    ];

    public function exam()
    {
        return $this->belongsTo(MockExam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function proctorEvents()
    {
        return $this->hasMany(MockExamProctorEvent::class, 'attempt_id')->orderBy('occurred_at');
    }

    public function captures()
    {
        return $this->hasMany(MockExamProctorCapture::class, 'attempt_id')->orderByDesc('captured_at');
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * When this student's time is up: their own duration from started_at, but
     * never later than the exam's scheduled window (a late starter gets the
     * remainder, not a fresh clock).
     */
    public function deadline(): \Illuminate\Support\Carbon
    {
        $personal = $this->started_at->copy()->addMinutes($this->exam->duration_minutes);
        $window = $this->exam->endsAt();

        return $personal->lessThan($window) ? $personal : $window;
    }

    public function hasExpired(): bool
    {
        return ! $this->isSubmitted() && $this->deadline()->isPast();
    }

    /** True when the server closed this sitting because the student never submitted. */
    public function wasAutoClosed(): bool
    {
        return $this->proctorEvents()->where('type', MockExamProctorEvent::TYPE_AUTO_CLOSED)->exists();
    }
}
