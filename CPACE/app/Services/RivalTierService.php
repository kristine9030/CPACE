<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Live Room rival parameters for Assessment/Ranked-mode quizzes.
 *
 * The "intelligence" of each rival is two things:
 *   1. Its starting pace/accuracy (spq/acc below) - this used to be dev-picked
 *      arbitrary numbers with no empirical basis.
 *   2. Its live adaptation to the student's own pace (see take-quiz.blade.php
 *      Room.target()) - this part was already objective, since it reacts to
 *      real data (the student's own timing) rather than a fixed script.
 *
 * This service fixes (1): once enough historical data exists, rival tiers are
 * derived from actual top-performer session data instead of guesses. Until
 * there's enough data to be statistically meaningful, it falls back to the
 * original hand-picked tiers so the Live Room still works for a new/small
 * install.
 *
 * Only Assessment-mode sessions (is_practice_room = false) ever feed or use
 * this - Practice Room sessions use student-picked difficulty labels instead
 * and are never part of this computation (see QuizController::PRACTICE_TIERS).
 */
class RivalTierService
{
    /** A qualifying student needs at least this many completed sessions. */
    private const MIN_SESSIONS_PER_STUDENT = 3;

    /**
     * Need at least this SHARE of the whole active student body to be
     * "qualifying" before percentiles mean anything - a percentage rather
     * than a fixed headcount, because a school-scale deployment might only
     * ever have a few dozen students total (this one currently has ~25:
     * 12 in 4th year, 13 in 3rd). A fixed number like "30 students" would
     * never be reachable there, so the requirement scales with however many
     * students the install actually has.
     */
    private const MIN_QUALIFYING_RATIO = 0.5;

    /**
     * ...but never require fewer than this many regardless of ratio, so a
     * handful of students in a brand-new/tiny install can't trigger this on
     * a statistically meaningless sample (e.g. 3 out of 4 total students).
     */
    private const MIN_QUALIFYING_FLOOR = 10;

    /**
     * Top performers = this percentile and above, by average accuracy. Kept
     * wider than a typical "top 20%" cut because with a small student body
     * the qualifying pool itself is small - cutting too sharply would average
     * only 2-3 students, which is too noisy to anchor a rival's pace on.
     */
    private const TOP_PERCENTILE = 0.60;

    private const CACHE_KEY = 'rival_tiers_v1';
    private const CACHE_TTL_HOURS = 24;

    /**
     * The hand-picked fallback tiers, used until we have enough historical
     * data (see hasEnoughData()) to compute real ones. These are the original
     * dev-chosen values that shipped with the Live Room feature.
     */
    private const FALLBACK_TIERS = [
        ['name' => 'Aria', 'tag' => 'Speedster',  'color' => '#ef4444', 'spq' => 13, 'acc' => 0.81],
        ['name' => 'Dex',  'tag' => 'Risk-taker', 'color' => '#f59e0b', 'spq' => 15, 'acc' => 0.78],
        ['name' => 'Mira', 'tag' => 'Methodical', 'color' => '#3b82f6', 'spq' => 24, 'acc' => 0.93],
        ['name' => 'Kip',  'tag' => 'Steady',     'color' => '#10b981', 'spq' => 19, 'acc' => 0.87],
        ['name' => 'Nova', 'tag' => 'Clutch',     'color' => '#8b5cf6', 'spq' => 17, 'acc' => 0.90],
        ['name' => 'Rio',  'tag' => 'Grinder',    'color' => '#0ea5e9', 'spq' => 21, 'acc' => 0.85],
        ['name' => 'Sage', 'tag' => 'Precise',    'color' => '#14b8a6', 'spq' => 26, 'acc' => 0.95],
        ['name' => 'Zed',  'tag' => 'Sprinter',   'color' => '#e11d48', 'spq' => 11, 'acc' => 0.76],
    ];

    /**
     * The rival pool for Assessment/Ranked mode: data-derived once enough
     * history exists, otherwise the fallback tiers above. Cached because this
     * scans historical sessions and does not need to be second-fresh - the
     * rival roster only needs to move as the student body's performance
     * shifts over days/weeks, not within a single quiz.
     */
    public function tiers(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_TTL_HOURS), function () {
            $derived = $this->deriveFromHistory();

            return $derived ?? self::FALLBACK_TIERS;
        });
    }

    /**
     * Whether the current tiers being served are the real, data-derived ones
     * (vs. the fallback). Exposed so the UI/reporting can be honest about
     * which basis is currently active - useful while defending this to the
     * capstone panel.
     */
    public function isDataDerived(): bool
    {
        return $this->deriveFromHistory() !== null;
    }

    /** How many currently-active students the whole install has. */
    private function activeStudentCount(): int
    {
        return DB::table('users')
            ->where('role_id', Role::STUDENT)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Compute tiers from real top-performer data, or return null if there
     * isn't yet enough history for the percentile to be meaningful.
     */
    private function deriveFromHistory(): ?array
    {
        $perStudent = DB::table('quiz_sessions')
            ->where('session_type', '!=', 'training')
            ->where('is_practice_room', false)
            ->whereNotNull('completed_at')
            ->where('total_items', '>', 0)
            ->selectRaw('
                student_id,
                COUNT(*) as sessions,
                AVG(duration_secs / total_items) as avg_spq,
                AVG(correct_answers / total_items) as avg_acc
            ')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_SESSIONS_PER_STUDENT])
            ->get();

        $requiredQualifying = max(
            self::MIN_QUALIFYING_FLOOR,
            (int) ceil($this->activeStudentCount() * self::MIN_QUALIFYING_RATIO)
        );

        if ($perStudent->count() < $requiredQualifying) {
            return null;
        }

        // Keep the top percentile by accuracy - these are the students whose
        // pace/accuracy the rivals should model.
        $cutIndex = (int) floor($perStudent->count() * (1 - self::TOP_PERCENTILE));
        $topPerformers = $perStudent->sortByDesc('avg_acc')->take(max(1, $perStudent->count() - $cutIndex));

        $baseSpq = (float) $topPerformers->avg('avg_spq');
        $baseAcc = (float) $topPerformers->avg('avg_acc');

        // Spread the single top-performer baseline across the existing named
        // tiers using the same relative offsets the fallback pool used, so
        // the rival "personalities" (Speedster vs. Methodical, etc.) keep
        // their character instead of collapsing into one identical rival.
        return collect(self::FALLBACK_TIERS)->map(function ($tier) use ($baseSpq, $baseAcc) {
            $fallbackAvgSpq = collect(self::FALLBACK_TIERS)->avg('spq');
            $fallbackAvgAcc = collect(self::FALLBACK_TIERS)->avg('acc');

            $spqRatio = $tier['spq'] / $fallbackAvgSpq;
            $accOffset = $tier['acc'] - $fallbackAvgAcc;

            return [
                'name'  => $tier['name'],
                'tag'   => $tier['tag'],
                'color' => $tier['color'],
                'spq'   => round($baseSpq * $spqRatio, 1),
                'acc'   => round(min(0.97, max(0.5, $baseAcc + $accOffset)), 2),
            ];
        })->all();
    }
}
