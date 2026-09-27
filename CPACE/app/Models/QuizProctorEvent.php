<?php

namespace App\Models;

/**
 * A behavioural flag raised during a monitored class quiz. Same types, labels
 * and severity as the mock exam's, so the two features flag identically; only
 * the table and the attempt it points at differ.
 */
class QuizProctorEvent extends MockExamProctorEvent
{
    protected $table = 'quiz_proctor_events';

    public function attempt()
    {
        return $this->belongsTo(FacultyQuizAttempt::class, 'attempt_id');
    }
}
