<?php

namespace App\Services;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamProctorEvent;
use App\Support\ProctorCaptureRetention;
use Illuminate\Support\Carbon;

/**
 * Grades a mock exam sitting and closes it.
 *
 * One code path for both ways a sitting ends - the student pressing Submit, and
 * the server closing a sitting the student walked away from - so the two can
 * never grade differently.
 *
 * Everything is scored against the item snapshot stored on the exam, never the
 * live Test Bank, so a question edited after publish can't change a mark.
 */
class MockExamGrader
{
    /** How long after a student's deadline before the server closes the sitting for them. */
    public const GRACE_MINUTES = 2;

    /**
     * @param  array<string, string>  $answers  already sanitised: item id => choice label
     */
    public function grade(MockExam $exam, MockExamAttempt $attempt, array $answers, Carbon $submittedAt, bool $isLate): void
    {
        $items = $exam->items()->get();

        $score = 0;
        $totalPoints = 0;
        $topicTally = [];
        $answerResults = [];

        foreach ($items as $item) {
            $selected = $answers[(string) $item->id] ?? null;
            $isCorrect = $item->isCorrect($selected);
            $totalPoints += $item->points;
            if ($isCorrect) {
                $score += $item->points;
            }

            if ($item->topic_id) {
                $topicTally[$item->topic_id] ??= ['attempts' => 0, 'correct' => 0, 'trailing_wrong' => 0];
                $topicTally[$item->topic_id]['attempts']++;
                $topicTally[$item->topic_id]['correct'] += $isCorrect ? 1 : 0;
                // Trailing run of wrong answers, in the order answered - the "3 in a row" streak.
                $topicTally[$item->topic_id]['trailing_wrong'] = $isCorrect ? 0 : $topicTally[$item->topic_id]['trailing_wrong'] + 1;
            }

            if ($item->source_question_id) {
                $answerResults[] = [
                    'question_id' => $item->source_question_id,
                    'difficulty' => $item->difficulty,
                    'correct' => $isCorrect,
                ];
            }
        }

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'total_points' => $totalPoints,
            'percent' => $totalPoints > 0 ? round($score / $totalPoints * 100, 2) : 0,
            'submitted_at' => $submittedAt,
            'is_late' => $isLate,
            'status' => MockExamAttempt::STATUS_SUBMITTED,
        ]);

        // Analytics run after the grade is committed so a failure here can
        // never void a finished exam. No points are awarded: a graded exam is
        // not practice, and a 100-item paper would distort the leaderboard.
        try {
            app(PerformanceRecorder::class)->record($attempt->student_id, $topicTally);
            app(SpacedRepetitionScheduler::class)->recordAnswers($attempt->student_id, $answerResults);
            app(WeaknessDetector::class)->syncMany($attempt->student_id, array_keys($topicTally));
        } catch (\Throwable $e) {
            report($e);
        }

        // Separate from the analytics so a failure in either can't stop the other.
        try {
            app(ProctorCaptureRetention::class)->afterSubmit($attempt);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Close sittings whose time ran out without a submit, grading whatever the
     * autosave last stored. Optionally limited to one exam or one student.
     *
     * The result is marked with an auto_closed note (not a flag), so faculty can
     * tell a closed-for-them sitting from a submitted one.
     *
     * @return int how many sittings were closed
     */
    public function closeExpired(?MockExam $exam = null, ?int $studentId = null): int
    {
        $query = MockExamAttempt::with('exam')->where('status', MockExamAttempt::STATUS_IN_PROGRESS);
        if ($exam) {
            $query->where('exam_id', $exam->id);
        }
        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $closed = 0;
        foreach ($query->get() as $attempt) {
            if (! $attempt->exam || $attempt->deadline()->copy()->addMinutes(self::GRACE_MINUTES)->isFuture()) {
                continue;
            }

            $closed += $this->closeOne($attempt) ? 1 : 0;
        }

        return $closed;
    }

    /** Close one sitting whose deadline has passed. False if someone else got there first. */
    public function closeOne(MockExamAttempt $attempt): bool
    {
        $deadline = $attempt->deadline();

        // Claim it atomically: if the student's own submit lands at the same
        // moment, only one of the two wins and the other does nothing.
        $claimed = MockExamAttempt::whereKey($attempt->id)
            ->where('status', MockExamAttempt::STATUS_IN_PROGRESS)
            ->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => $deadline]);
        if (! $claimed) {
            return false;
        }

        $attempt->refresh();

        // On time by construction: it was closed at the student's own deadline.
        $this->grade($attempt->exam, $attempt, (array) ($attempt->answers ?? []), $deadline, false);

        MockExamProctorEvent::create([
            'attempt_id' => $attempt->id,
            'type' => MockExamProctorEvent::TYPE_AUTO_CLOSED,
            'occurred_at' => now(),
            'meta' => 'Time ran out; graded from the last autosave',
        ]);

        return true;
    }
}
