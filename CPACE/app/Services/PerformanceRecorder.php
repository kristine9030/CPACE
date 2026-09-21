<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Rolls a sitting's per-topic tally into performance_records and keeps
 * is_weak_area in step with WeaknessDetector.
 *
 * Extracted from Student\QuizController so the mock exam scores through the
 * exact same path. That matters: weak-area detection is the single source of
 * truth behind the Spaced Repetition Calendar, the Performance page and
 * Adaptive mode's topic targeting, and a second, subtly different copy of
 * this arithmetic would quietly desynchronise all three.
 */
class PerformanceRecorder
{
    public function __construct(private WeaknessDetector $weakness)
    {
    }

    /**
     * @param  array<int, array{attempts:int, correct:int}>  $topicTally  keyed by topic id
     */
    public function record(int $studentId, array $topicTally): void
    {
        foreach ($topicTally as $topicId => $tally) {
            $record = DB::table('performance_records')
                ->where('student_id', $studentId)
                ->where('topic_id', $topicId)
                ->first();

            $wrong = $tally['attempts'] - $tally['correct'];

            if ($record) {
                $totalAttempts = $record->total_attempts + $tally['attempts'];
                $correctCount = $record->correct_count + $tally['correct'];
                // Reset the wrong-streak on a clean sitting, otherwise extend it.
                $consecutiveWrong = $wrong === 0 ? 0 : $record->consecutive_wrong + $wrong;

                [$isWeak] = $this->weakness->evaluate((object) [
                    'total_attempts' => $totalAttempts,
                    'correct_count' => $correctCount,
                    'consecutive_wrong' => $consecutiveWrong,
                ]);

                // accuracy_rate is a STORED generated column in the database
                // (computed from correct_count / total_attempts), so it is
                // never written here - the DB keeps it in sync automatically.
                DB::table('performance_records')
                    ->where('id', $record->id)
                    ->update([
                        'total_attempts' => $totalAttempts,
                        'correct_count' => $correctCount,
                        'consecutive_wrong' => $consecutiveWrong,
                        'is_weak_area' => $isWeak,
                        'last_attempted' => now(),
                    ]);
            } else {
                [$isWeak] = $this->weakness->evaluate((object) [
                    'total_attempts' => $tally['attempts'],
                    'correct_count' => $tally['correct'],
                    'consecutive_wrong' => $wrong,
                ]);

                DB::table('performance_records')->insert([
                    'student_id' => $studentId,
                    'topic_id' => $topicId,
                    'total_attempts' => $tally['attempts'],
                    'correct_count' => $tally['correct'],
                    'consecutive_wrong' => $wrong,
                    'is_weak_area' => $isWeak,
                    'last_attempted' => now(),
                ]);
            }
        }
    }
}
