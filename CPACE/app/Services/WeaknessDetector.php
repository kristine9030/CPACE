<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Weakness-detection rules used across CPAce.
 *
 * A topic is flagged as a weak area, per the study's scope, when either:
 *   - the student's accuracy on it falls below 60% over at least 5 attempts, or
 *   - they answer it incorrectly 3 times in a row.
 *
 * The flags live in `weakness_reports` (an open row is an unresolved weakness,
 * a row with `resolved_at` is one the student has since recovered from). These
 * flags are what the Spaced Repetition Calendar reads to mark a review as
 * High priority, so the weak areas a student keeps missing are the ones the
 * calendar pushes to the front.
 */
class WeaknessDetector
{
    public const ACCURACY_THRESHOLD  = 0.60;
    public const MIN_ATTEMPTS        = 5;
    public const CONSECUTIVE_WRONG   = 3;
    public const STRENGTH_THRESHOLD  = 0.75;

    /**
     * Decide whether a performance_records row currently counts as weak.
     * Returns [bool $isWeak, ?string $reason, float $accuracy].
     */
    public function evaluate(object $record): array
    {
        $attempts = (int) $record->total_attempts;
        $correct  = (int) $record->correct_count;
        $wrongRun = (int) $record->consecutive_wrong;
        $accuracy = $attempts > 0 ? $correct / $attempts : 0.0;

        if ($wrongRun >= self::CONSECUTIVE_WRONG) {
            return [true, 'consecutive_wrong', $accuracy];
        }

        if ($attempts >= self::MIN_ATTEMPTS && $accuracy < self::ACCURACY_THRESHOLD) {
            return [true, 'low_accuracy', $accuracy];
        }

        return [false, null, $accuracy];
    }

    /**
     * Decide whether a performance_records row counts as a strength: accuracy
     * at/above the mastery line (75%) over enough attempts (the same minimum as
     * the weak rule, so one lucky answer can't label a topic strong), and not
     * currently flagged weak. Compared unrounded so 74.6% is never "strong" on
     * one page and not on another.
     */
    public function isStrong(object $record): bool
    {
        $attempts = (int) $record->total_attempts;
        if ($attempts < self::MIN_ATTEMPTS) {
            return false;
        }

        if ($this->evaluate($record)[0]) {
            return false;
        }

        return (int) $record->correct_count * 100 >= self::STRENGTH_THRESHOLD * 100 * $attempts;
    }

    /**
     * The "wrong in a row" streak after a sitting. A sitting with no correct
     * answer extends the running streak; otherwise the streak restarts from the
     * wrong answers at the very end of the sitting (0 if it ended on a correct
     * one). $tally['trailing_wrong'] is the run of wrong answers at the end of
     * the sitting, in the order they were answered.
     *
     * @param  array{attempts:int, correct:int, trailing_wrong?:int}  $tally
     */
    public static function nextStreak(int $previousStreak, array $tally): int
    {
        $trailing = (int) ($tally['trailing_wrong'] ?? ($tally['correct'] === 0 ? $tally['attempts'] : 0));

        return $trailing >= (int) $tally['attempts'] ? $previousStreak + $trailing : $trailing;
    }

    /**
     * Reconcile the `weakness_reports` log for one (student, topic) against the
     * student's current performance: open a new report when a topic becomes
     * weak, resolve the open one when they recover. Idempotent.
     */
    public function sync(int $studentId, int $topicId): void
    {
        $record = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->where('topic_id', $topicId)
            ->first();

        if (! $record) {
            return;
        }

        [$isWeak, $reason, $accuracy] = $this->evaluate($record);

        $open = DB::table('weakness_reports')
            ->where('student_id', $studentId)
            ->where('topic_id', $topicId)
            ->whereNull('resolved_at')
            ->first();

        if ($isWeak && ! $open) {
            DB::table('weakness_reports')->insert([
                'student_id'       => $studentId,
                'topic_id'         => $topicId,
                'flagged_at'       => now(),
                'trigger_reason'   => $reason,
                'accuracy_at_flag' => round($accuracy * 100, 2),
            ]);
        } elseif (! $isWeak && $open) {
            DB::table('weakness_reports')
                ->where('id', $open->id)
                ->update(['resolved_at' => now()]);
        }
    }

    /**
     * Sync several topics at once (e.g. every topic touched by a quiz).
     */
    public function syncMany(int $studentId, iterable $topicIds): void
    {
        foreach ($topicIds as $topicId) {
            $this->sync($studentId, (int) $topicId);
        }
    }
}
