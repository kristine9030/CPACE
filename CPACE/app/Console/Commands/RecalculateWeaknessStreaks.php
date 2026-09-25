<?php

namespace App\Console\Commands;

use App\Services\WeaknessDetector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time repair for performance_records.consecutive_wrong.
 *
 * Before the streak fix the column was a running total of wrong answers that
 * only reset on a perfect sitting, so a 90%-accurate topic could read "3 wrong
 * in a row" (the largest value seen was 39). The real streak is the run of
 * wrong answers at the very end of a student's answer history for a topic, so
 * it is rebuilt here from quiz_answers in the order the answers were given.
 *
 * Only quiz_answers-backed history is used (web + mobile quizzes that count
 * toward progress). Records with no answer history are left untouched and
 * reported, rather than guessed at.
 */
class RecalculateWeaknessStreaks extends Command
{
    protected $signature = 'performance:recalculate-streaks
                            {--dry-run : Report what would change without writing anything}';

    protected $description = 'Rebuild consecutive_wrong (and the weak flags that depend on it) from answer history';

    public function handle(WeaknessDetector $detector): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Trailing wrong-run per "student:topic", built in answer order.
        $streaks = [];
        DB::table('quiz_answers')
            ->join('quiz_sessions', 'quiz_sessions.id', '=', 'quiz_answers.session_id')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->where('quiz_sessions.session_type', '!=', 'training')
            ->where('quiz_sessions.is_practice_room', false)
            ->whereNotNull('quiz_answers.is_correct')
            ->orderBy('quiz_sessions.student_id')
            ->orderBy('questions.topic_id')
            ->orderBy('quiz_answers.answered_at')
            ->orderBy('quiz_answers.id')
            ->select('quiz_sessions.student_id', 'questions.topic_id', 'quiz_answers.is_correct')
            ->cursor()
            ->each(function ($row) use (&$streaks) {
                $key = $row->student_id . ':' . $row->topic_id;
                $streaks[$key] = $row->is_correct ? 0 : ($streaks[$key] ?? 0) + 1;
            });

        $changed = 0;
        $unchanged = 0;
        $noHistory = 0;

        DB::table('performance_records')->where('total_attempts', '>', 0)->orderBy('id')->each(
            function ($record) use ($streaks, $detector, $dryRun, &$changed, &$unchanged, &$noHistory) {
                $key = $record->student_id . ':' . $record->topic_id;

                if (! array_key_exists($key, $streaks)) {
                    $noHistory++;

                    return;
                }

                $streak = $streaks[$key];
                [$isWeak] = $detector->evaluate((object) [
                    'total_attempts' => $record->total_attempts,
                    'correct_count' => $record->correct_count,
                    'consecutive_wrong' => $streak,
                ]);

                if ((int) $record->consecutive_wrong === $streak && (bool) $record->is_weak_area === $isWeak) {
                    $unchanged++;

                    return;
                }

                $changed++;
                $this->line(sprintf(
                    'student %d / topic %d: streak %d -> %d, weak %s -> %s',
                    $record->student_id, $record->topic_id,
                    $record->consecutive_wrong, $streak,
                    $record->is_weak_area ? 'yes' : 'no', $isWeak ? 'yes' : 'no'
                ));

                if (! $dryRun) {
                    DB::table('performance_records')->where('id', $record->id)->update([
                        'consecutive_wrong' => $streak,
                        'is_weak_area' => $isWeak,
                    ]);
                    $detector->sync((int) $record->student_id, (int) $record->topic_id);
                }
            }
        );

        $this->info(sprintf(
            '%s%d record(s) corrected, %d already correct, %d without answer history (left as-is).',
            $dryRun ? '[dry run] ' : '', $changed, $unchanged, $noHistory
        ));

        return self::SUCCESS;
    }
}
