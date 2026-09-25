<?php

namespace App\Console\Commands;

use App\Services\MockExamGrader;
use Illuminate\Console\Command;

/**
 * Grades mock exam sittings a student walked away from.
 *
 * A student whose browser crashed, or who simply left, never presses Submit.
 * Without this their sitting would stay "in progress" forever with no score,
 * even though every answer was autosaved. Once their time (plus a short grace)
 * is up, the last autosave is graded and the sitting is marked as closed
 * automatically. The monitor and the student's own pages do the same check on
 * the fly, so this is the safety net for sittings nobody opens.
 */
class CloseExpiredMockExamAttempts extends Command
{
    protected $signature = 'mock-exam:close-expired';

    protected $description = 'Grade mock exam sittings whose time ran out without the student submitting';

    public function handle(MockExamGrader $grader): int
    {
        $closed = $grader->closeExpired();

        $this->info($closed === 0
            ? 'No unsubmitted sittings to close.'
            : "Closed and graded {$closed} unsubmitted sitting(s).");

        return self::SUCCESS;
    }
}
