<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A student's single sitting of a class quiz (one attempt per student).
 * answers is {"<item_id>": "<choice label>"}; the score is frozen at submit.
 */
class FacultyQuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'student_id', 'started_at', 'submitted_at',
        'answers', 'score', 'total_points', 'percent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answers' => 'array',
        'score' => 'integer',
        'total_points' => 'integer',
        'percent' => 'float',
    ];

    public function quiz()
    {
        return $this->belongsTo(FacultyQuiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Seconds left on a timed quiz for this attempt, or null when untimed.
     * Never negative - a zero means "time is up".
     */
    public function secondsRemaining(): ?int
    {
        $limit = $this->quiz->time_limit_minutes;
        if (! $limit) {
            return null;
        }

        $deadline = $this->started_at->copy()->addMinutes($limit);

        return max(0, (int) now()->diffInSeconds($deadline, false));
    }
}
