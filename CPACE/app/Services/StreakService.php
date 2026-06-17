<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Daily activity streak.
 *
 * A streak is the run of consecutive calendar days, ending today, on which the
 * student completed at least one quiz of ANY type or mode - training and
 * testing both count, because simply showing up to practise keeps it alive.
 *
 * The value is computed live from quiz_sessions so it always self-heals: a
 * missed day collapses the streak with no stored state to go stale. The result
 * is also mirrored into student_profiles.streak_days so other readers (e.g. the
 * faculty dashboard) see a current figure between the student's own page loads.
 */
class StreakService
{
    /**
     * Compute the student's current streak in days.
     */
    public function current(int $studentId): int
    {
        return count($this->streakDates($studentId));
    }

    /**
     * The exact calendar dates (as 'Y-m-d' strings) that make up the current
     * streak - the unbroken run of active days ending today (or yesterday, if
     * today has no activity yet), most-recent first.
     *
     * This is the single source of truth for the streak: current() is just its
     * length, and the consistency dots tick exactly these days, so the number
     * and the dots can never disagree.
     */
    public function streakDates(int $studentId): array
    {
        $activeDates = DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->pluck('completed_at')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->flip(); // date string => key, for O(1) lookups

        if ($activeDates->isEmpty()) {
            return [];
        }

        // Start at today; if today has no activity yet, anchor on yesterday so
        // an unbroken run isn't reported as zero before today's first quiz.
        $cursor = Carbon::today();
        if (! $activeDates->has($cursor->toDateString())) {
            $cursor->subDay();
        }

        // Walk backwards collecting the unbroken consecutive active days.
        $dates = [];
        while ($activeDates->has($cursor->toDateString())) {
            $dates[] = $cursor->toDateString();
            $cursor->subDay();
        }

        return $dates;
    }

    /**
     * Recompute the streak and persist it to the student's profile, returning
     * the fresh value. Called after a quiz is completed.
     */
    public function refresh(int $studentId): int
    {
        $streak = $this->current($studentId);

        DB::table('student_profiles')
            ->where('user_id', $studentId)
            ->update(['streak_days' => $streak]);

        return $streak;
    }
}
