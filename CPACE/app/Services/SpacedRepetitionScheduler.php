<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * SM-2 spaced-repetition scheduler (Piotr Wozniak's SuperMemo 2 algorithm).
 *
 * Implements the exact formulas the CPAce study evaluates:
 *   I(1) = 1 day, I(2) = 6 days, I(n) = round(I(n-1) x EF) for n > 2
 *   EF' = EF + (0.1 - (5 - q) x (0.08 + (5 - q) x 0.02)),  floored at 1.3
 *   a recall quality q < 3 is a lapse: the repetition chain resets and the
 *   item is rescheduled for the next day.
 *
 * One row in `spaced_repetition_items` is one (student, question) pair. Weak
 * topics resurface sooner automatically: a wrong answer maps to q < 3, which
 * collapses the interval back to a single day - so the questions a student
 * struggles with keep returning to the calendar far more often than mastered
 * ones, which is the "focus on what matters" behaviour the system promises.
 */
class SpacedRepetitionScheduler
{
    /** Lower bound on the ease factor (Wozniak's floor). */
    public const EF_MIN = 1.30;

    /** Ease factor a fresh item starts with. */
    public const EF_DEFAULT = 2.50;

    /**
     * Translate a graded answer into an SM-2 recall-quality score (0-5).
     * Quality reflects how EASILY the item was recalled, so an easy question
     * answered correctly scores highest, while blanking an easy item is the
     * worst possible outcome.
     */
    public function qualityFromAnswer(bool $correct, string $difficulty): int
    {
        if ($correct) {
            return match ($difficulty) {
                'difficult' => 3,
                'moderate'  => 4,
                default     => 5, // easy
            };
        }

        return match ($difficulty) {
            'difficult' => 2,
            'moderate'  => 1,
            default     => 0, // easy - blanked on an easy item
        };
    }

    /**
     * Advance one SM-2 step. $state carries the item's prior repetition_num,
     * ease_factor and interval_days; $reviewedOn is the date of this review.
     * Returns the full next state ready to persist.
     */
    public function next(array $state, int $quality, Carbon $reviewedOn): array
    {
        $rep      = (int) ($state['repetition_num'] ?? 0);
        $ef       = (float) ($state['ease_factor'] ?? self::EF_DEFAULT);
        $interval = (int) ($state['interval_days'] ?? 0);

        // Ease factor is updated the same way whether the item passed or lapsed.
        $ef = $ef + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $ef = max(self::EF_MIN, round($ef, 2));

        if ($quality < 3) {
            // Lapse: restart the chain and review again tomorrow.
            $rep      = 0;
            $interval = 1;
        } else {
            $rep++;
            $interval = match (true) {
                $rep <= 1  => 1,
                $rep === 2 => 6,
                default    => max(1, (int) round($interval * $ef)),
            };
        }

        return [
            'repetition_num' => $rep,
            'ease_factor'    => $ef,
            'interval_days'  => $interval,
            'quality_score'  => $quality,
            'last_reviewed'  => $reviewedOn->toDateString(),
            'next_review_at' => $reviewedOn->copy()->addDays($interval)->toDateString(),
        ];
    }

    /**
     * Fast-forward a brand-new item through a number of consecutive successful
     * reviews so a student's historical performance can seed a realistic
     * interval. Only the resulting repetition_num / ease_factor / interval_days
     * matter here - the caller anchors the actual review date afterwards.
     */
    public function mature(int $successes, int $quality = 4): array
    {
        $state  = ['repetition_num' => 0, 'ease_factor' => self::EF_DEFAULT, 'interval_days' => 0];
        $anchor = Carbon::create(2000, 1, 1); // placeholder; interval is what we keep

        for ($i = 0; $i < max(0, $successes); $i++) {
            $state = $this->next($state, $quality, $anchor);
        }

        return $state;
    }

    /**
     * Record a batch of graded answers, upserting each item's SM-2 state in
     * `spaced_repetition_items`. $answers is a list of
     * ['question_id' => int, 'difficulty' => string, 'correct' => bool].
     */
    public function recordAnswers(int $studentId, array $answers, ?Carbon $reviewedOn = null): void
    {
        $reviewedOn ??= Carbon::today();

        foreach ($answers as $answer) {
            $questionId = (int) $answer['question_id'];
            $quality    = $this->qualityFromAnswer((bool) $answer['correct'], (string) $answer['difficulty']);

            $existing = DB::table('spaced_repetition_items')
                ->where('student_id', $studentId)
                ->where('question_id', $questionId)
                ->first();

            $state = $this->next([
                'repetition_num' => $existing->repetition_num ?? 0,
                'ease_factor'    => $existing->ease_factor ?? self::EF_DEFAULT,
                'interval_days'  => $existing->interval_days ?? 0,
            ], $quality, $reviewedOn);

            DB::table('spaced_repetition_items')->updateOrInsert(
                ['student_id' => $studentId, 'question_id' => $questionId],
                $state
            );
        }
    }
}
