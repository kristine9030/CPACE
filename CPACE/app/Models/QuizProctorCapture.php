<?php

namespace App\Models;

/**
 * A camera or screen frame captured during a monitored class quiz. Lives on
 * the PRIVATE disk under its own root and is served only through an authorised
 * route, exactly like the mock exam's captures.
 */
class QuizProctorCapture extends MockExamProctorCapture
{
    protected $table = 'quiz_proctor_captures';

    public const ROOT = 'proctor-quiz';

    public function attempt()
    {
        return $this->belongsTo(FacultyQuizAttempt::class, 'attempt_id');
    }
}
