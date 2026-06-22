<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Achievements / gamification, computed live.
 *
 * Like the Performance and Calendar pages, nothing here is hard-coded: every
 * badge, the tier progress, the leaderboard and the student's rank are derived
 * from the student's own quiz history (quiz_sessions / quiz_answers /
 * performance_records). A badge is "earned" the moment its condition was first
 * satisfied somewhere in that history, so the result is deterministic and
 * self-consistent on every page load - the same idea the StreakService uses.
 *
 * Training is a no-stakes practice mode and is excluded from every figure here,
 * exactly as it is on the rest of the student pages (the streak is the only
 * thing that counts training, mirroring StreakService).
 */
class AchievementService
{
    public function __construct(private StreakService $streaks) {}

    /**
     * Static badge catalogue. Each entry:
     *   [name, description, icon, colour, category, tier, target, kind]
     * kind drives how progress is shown:
     *   count  - "current / target" (questions, streak days, sessions ...)
     *   pct    - an accuracy percentage measured against a target percentage
     *   binary - a one-off "did it / didn't" badge
     *
     * Categories: milestone | performance | consistency | special
     * Tiers:      beginner | intermediate | advanced | legend
     */
    private function catalog(): array
    {
        return [
            // ── Milestone ──────────────────────────────────────────────
            'first_step'     => ['First Step', 'Complete your first quiz.', 'fa-shoe-prints', 'red', 'milestone', 'beginner', 1, 'count'],
            'topic_explorer' => ['Topic Explorer', 'Practise questions across 10 different topics.', 'fa-compass', 'blue', 'milestone', 'intermediate', 10, 'count'],
            'centurion'      => ['Centurion', 'Answer 100 questions in total.', 'fa-list-ol', 'teal', 'milestone', 'intermediate', 100, 'count'],
            'scholar'        => ['Scholar', 'Answer 500 questions in total.', 'fa-graduation-cap', 'purple', 'milestone', 'advanced', 500, 'count'],
            'grandmaster'    => ['Grandmaster', 'Answer 1,000 questions in total.', 'fa-crown', 'yellow', 'milestone', 'legend', 1000, 'count'],

            // ── Performance ────────────────────────────────────────────
            'sharp_shooter'  => ['Sharp Shooter', 'Reach 75% overall accuracy (20+ questions).', 'fa-bullseye', 'green', 'performance', 'intermediate', 75, 'pct'],
            'high_achiever'  => ['High Achiever', 'Score 90% or higher in a single quiz.', 'fa-arrow-trend-up', 'purple', 'performance', 'advanced', 90, 'pct'],
            'perfect_run'    => ['Perfect Run', 'Score a flawless 100% in a quiz.', 'fa-star', 'yellow', 'performance', 'advanced', 100, 'pct'],
            'mock_master'    => ['Mock Master', 'Finish 5 challenge-mode quizzes.', 'fa-trophy', 'pink', 'performance', 'advanced', 5, 'count'],
            'marksman'       => ['Marksman', 'Reach 90% overall accuracy (50+ questions).', 'fa-crosshairs', 'red', 'performance', 'legend', 90, 'pct'],

            // ── Consistency ────────────────────────────────────────────
            'warm_up'        => ['Warming Up', 'Study 3 days in a row.', 'fa-seedling', 'green', 'consistency', 'beginner', 3, 'count'],
            'consistent'     => ['Consistent Learner', 'Keep a 7-day study streak.', 'fa-fire', 'blue', 'consistency', 'intermediate', 7, 'count'],
            'dedicated'      => ['Dedicated', 'Keep a 14-day study streak.', 'fa-calendar-check', 'purple', 'consistency', 'advanced', 14, 'count'],
            'unstoppable'    => ['Unstoppable', 'Keep a 30-day study streak.', 'fa-bolt', 'red', 'consistency', 'legend', 30, 'count'],

            // ── Special ────────────────────────────────────────────────
            'early_bird'     => ['Early Bird', 'Answer a question before 8:00 AM.', 'fa-mug-hot', 'blue', 'special', 'beginner', 1, 'binary'],
            'quick_thinker'  => ['Quick Thinker', 'Answer 20+ questions in under 10 minutes.', 'fa-gauge-high', 'yellow', 'special', 'intermediate', 1, 'binary'],
            'night_owl'      => ['Night Owl', 'Answer a question after 10:00 PM.', 'fa-moon', 'purple', 'special', 'intermediate', 1, 'binary'],
            'time_manager'   => ['Time Manager', 'Finish 10 timed quizzes at 70%+ accuracy.', 'fa-stopwatch', 'teal', 'special', 'advanced', 10, 'count'],
            'topic_master'   => ['Topic Master', 'Reach 90%+ accuracy in any single topic.', 'fa-award', 'green', 'special', 'advanced', 90, 'pct'],
        ];
    }

    /** Human labels for the category filter tabs (key => label). */
    public function categories(): array
    {
        return [
            'all'         => 'All',
            'milestone'   => 'Milestone',
            'performance' => 'Performance',
            'consistency' => 'Consistency',
            'special'     => 'Special',
        ];
    }

    /** Ordered tier metadata for the Badge Progress panel. */
    public function tiers(): array
    {
        return [
            'beginner'     => ['Beginner', 'fa-seedling'],
            'intermediate' => ['Intermediate', 'fa-spa'],
            'advanced'     => ['Advanced', 'fa-tree'],
            'legend'       => ['Legend', 'fa-crown'],
        ];
    }

    /**
     * Build the full achievements payload for one student.
     */
    public function build(int $studentId): array
    {
        [$current, $earnedAt] = $this->evaluate($studentId);

        $now      = Carbon::now();
        $catalog  = $this->catalog();
        $badges   = [];
        $earnedCount = 0;
        $earnedThisMonth = 0;

        foreach ($catalog as $key => [$name, $desc, $icon, $colour, $category, $tier, $target, $kind]) {
            $cur     = $current[$key] ?? 0;
            $date    = $earnedAt[$key] ?? null;
            $earned  = $date !== null;
            $percent = $target > 0 ? min(100, (int) round($cur / $target * 100)) : 0;

            if ($earned) {
                $earnedCount++;
                if ($date->isSameMonth($now)) {
                    $earnedThisMonth++;
                }
            }

            $badges[$key] = [
                'key'         => $key,
                'name'        => $name,
                'desc'        => $desc,
                'icon'        => $icon,
                'colour'      => $earned ? $colour : 'gray',
                'category'    => $category,
                'tier'        => $tier,
                'kind'        => $kind,
                'current'     => $cur,
                'target'      => $target,
                'percent'     => $earned ? 100 : $percent,
                'earned'      => $earned,
                'earned_at'   => $date?->format('M j, Y'),
                'progress'    => $this->progressLabel($kind, $cur, $target, $earned),
            ];
        }

        // ── Tier progress (Badge Progress panel) ───────────────────────
        $tierProgress = [];
        foreach ($this->tiers() as $tierKey => [$label, $tierIcon]) {
            $inTier  = array_filter($badges, fn ($b) => $b['tier'] === $tierKey);
            $total   = count($inTier);
            $done    = count(array_filter($inTier, fn ($b) => $b['earned']));
            $tierProgress[] = [
                'key'     => $tierKey,
                'label'   => $label,
                'icon'    => $tierIcon,
                'earned'  => $done,
                'total'   => $total,
                'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
            ];
        }

        return [
            'badges'            => array_values($badges),
            'categories'        => $this->categories(),
            'tier_progress'     => $tierProgress,
            'earned_count'      => $earnedCount,
            'total_count'       => count($catalog),
            'earned_this_month' => $earnedThisMonth,
            'active_days'       => $current['_active_days'] ?? 0,
            'streak'            => $current['_streak'] ?? 0,
        ];
    }

    /**
     * Walk the student's history once and work out, for every badge, the
     * current progress value and the date (if any) its condition was first met.
     *
     * @return array{0: array<string,mixed>, 1: array<string,Carbon>}
     */
    private function evaluate(int $studentId): array
    {
        $current  = [];
        $earnedAt = [];

        // Earn a badge the first time its condition holds (never overwrite).
        $earn = function (string $key, ?Carbon $date) use (&$earnedAt) {
            if ($date && ! isset($earnedAt[$key])) {
                $earnedAt[$key] = $date->copy();
            }
        };

        // ── Completed, non-training sessions in chronological order ─────
        $sessions = DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->get(['mode', 'total_items', 'correct_answers', 'score_percent', 'duration_secs', 'completed_at']);

        $cumAttempted = 0;
        $cumCorrect   = 0;
        $sessionCount = 0;
        $challengeCnt = 0;
        $timed70Cnt   = 0;
        $bestScore    = 0.0;

        foreach ($sessions as $s) {
            $sessionCount++;
            $cumAttempted += (int) $s->total_items;
            $cumCorrect   += (int) $s->correct_answers;
            $date  = Carbon::parse($s->completed_at);

            $score = $s->score_percent !== null
                ? (float) $s->score_percent
                : ((int) $s->total_items > 0 ? $s->correct_answers / $s->total_items * 100 : 0);
            $bestScore = max($bestScore, $score);

            $earn('first_step', $date);

            if ($cumAttempted >= 100)  { $earn('centurion', $date); }
            if ($cumAttempted >= 500)  { $earn('scholar', $date); }
            if ($cumAttempted >= 1000) { $earn('grandmaster', $date); }

            $acc = $cumAttempted > 0 ? $cumCorrect / $cumAttempted * 100 : 0;
            if ($cumAttempted >= 20 && $acc >= 75) { $earn('sharp_shooter', $date); }
            if ($cumAttempted >= 50 && $acc >= 90) { $earn('marksman', $date); }

            if ($score >= 90)  { $earn('high_achiever', $date); }
            if ($score >= 100) { $earn('perfect_run', $date); }

            if ($s->mode === 'challenge') {
                $challengeCnt++;
                if ($challengeCnt >= 5) { $earn('mock_master', $date); }
            }

            if ((int) $s->total_items >= 20 && (int) $s->duration_secs > 0 && (int) $s->duration_secs <= 600) {
                $earn('quick_thinker', $date);
            }

            if ($s->mode === 'timed' && $score >= 70) {
                $timed70Cnt++;
                if ($timed70Cnt >= 10) { $earn('time_manager', $date); }
            }
        }

        $overallAcc = $cumAttempted > 0 ? (int) round($cumCorrect / $cumAttempted * 100) : 0;

        // ── Streak runs (training + testing count, like StreakService) ──
        $activeDates = DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->pluck('completed_at')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->sort()
            ->values();

        $maxStreak = 0;
        $run       = 0;
        $prev      = null;
        $streakBadges = [3 => 'warm_up', 7 => 'consistent', 14 => 'dedicated', 30 => 'unstoppable'];
        foreach ($activeDates as $ds) {
            $d   = Carbon::parse($ds);
            $run = ($prev && $prev->copy()->addDay()->isSameDay($d)) ? $run + 1 : 1;
            $prev = $d;
            $maxStreak = max($maxStreak, $run);
            foreach ($streakBadges as $len => $key) {
                if ($run >= $len) { $earn($key, $d); }
            }
        }

        // ── Distinct topics + time-of-day, from the answer log ──────────
        $answers = DB::table('quiz_answers as qa')
            ->join('quiz_sessions as qs', 'qs.id', '=', 'qa.session_id')
            ->join('questions as q', 'q.id', '=', 'qa.question_id')
            ->where('qs.student_id', $studentId)
            ->where('qs.session_type', '!=', 'training')
            ->whereNotNull('qa.answered_at')
            ->orderBy('qa.answered_at')
            ->get(['qa.answered_at', 'q.topic_id']);

        $seenTopics = [];
        foreach ($answers as $a) {
            $d = Carbon::parse($a->answered_at);
            if ($a->topic_id && ! isset($seenTopics[$a->topic_id])) {
                $seenTopics[$a->topic_id] = true;
                if (count($seenTopics) >= 10) { $earn('topic_explorer', $d); }
            }
            $hour = (int) $d->format('G');
            if ($hour < 8)   { $earn('early_bird', $d); }
            if ($hour >= 22) { $earn('night_owl', $d); }
        }
        $distinctTopics = count($seenTopics);

        // ── Topic Master: best single-topic accuracy (5+ attempts) ──────
        $topics = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->where('total_attempts', '>=', 5)
            ->get(['correct_count', 'total_attempts', 'last_attempted']);

        $bestTopicAcc = 0;
        foreach ($topics as $t) {
            $tAcc = $t->total_attempts > 0 ? (int) round($t->correct_count / $t->total_attempts * 100) : 0;
            $bestTopicAcc = max($bestTopicAcc, $tAcc);
            if ($tAcc >= 90) {
                $earn('topic_master', $t->last_attempted ? Carbon::parse($t->last_attempted) : Carbon::now());
            }
        }

        // ── Current progress values for every badge ─────────────────────
        $current = [
            'first_step'     => $sessionCount,
            'topic_explorer' => $distinctTopics,
            'centurion'      => $cumAttempted,
            'scholar'        => $cumAttempted,
            'grandmaster'    => $cumAttempted,
            'sharp_shooter'  => $cumAttempted >= 20 ? $overallAcc : min($overallAcc, 74),
            'high_achiever'  => (int) round($bestScore),
            'perfect_run'    => (int) round($bestScore),
            'mock_master'    => $challengeCnt,
            'marksman'       => $cumAttempted >= 50 ? $overallAcc : min($overallAcc, 89),
            'warm_up'        => $maxStreak,
            'consistent'     => $maxStreak,
            'dedicated'      => $maxStreak,
            'unstoppable'    => $maxStreak,
            'early_bird'     => isset($earnedAt['early_bird']) ? 1 : 0,
            'quick_thinker'  => isset($earnedAt['quick_thinker']) ? 1 : 0,
            'night_owl'      => isset($earnedAt['night_owl']) ? 1 : 0,
            'time_manager'   => $timed70Cnt,
            'topic_master'   => $bestTopicAcc,
            '_active_days'   => $activeDates->count(),
            '_streak'        => $this->streaks->current($studentId),
        ];

        return [$current, $earnedAt];
    }

    /**
     * Short progress caption shown under a locked badge.
     */
    private function progressLabel(string $kind, $current, int $target, bool $earned): string
    {
        if ($earned) {
            return 'Unlocked';
        }
        return match ($kind) {
            'pct'    => "{$current}% / {$target}%",
            'binary' => 'Not yet unlocked',
            default  => number_format((int) $current) . ' / ' . number_format($target),
        };
    }

    // ===================================================================
    //  LEADERBOARD + RANK
    // ===================================================================

    /**
     * Leaderboards for the three periods plus the student's own rank/status.
     * Students are ranked by the number of questions they answered correctly
     * in completed, non-training quizzes within the period.
     */
    public function leaderboards(int $meId): array
    {
        $now = Carbon::now();

        $week  = $this->ranked($now->copy()->startOfWeek(Carbon::SUNDAY), null);
        $month = $this->ranked($now->copy()->startOfMonth(), null);
        $all   = $this->ranked(null, null);

        // Rank movement: this calendar month vs the previous one.
        $prevMonth   = $this->ranked($now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->startOfMonth());
        $monthRank   = $this->meRank($month, $meId);
        $prevRank    = $this->meRank($prevMonth, $meId);
        $allRank     = $this->meRank($all, $meId);
        $totalRanked = $all->count();

        $percentile = $allRank ? max(1, (int) round($allRank / max($totalRanked, 1) * 100)) : null;

        if ($monthRank && $prevRank) {
            $delta = $prevRank - $monthRank; // positive = moved up
        } else {
            $delta = 0;
        }

        if ($delta > 0) {
            $deltaLabel = 'Up ' . $delta . ' spot' . ($delta === 1 ? '' : 's') . ' from last month';
            $deltaTone  = 'up';
        } elseif ($delta < 0) {
            $deltaLabel = 'Down ' . abs($delta) . ' spot' . (abs($delta) === 1 ? '' : 's') . ' from last month';
            $deltaTone  = 'down';
        } elseif ($allRank) {
            $deltaLabel = 'Holding your position';
            $deltaTone  = 'flat';
        } else {
            $deltaLabel = 'Take a quiz to join the leaderboard';
            $deltaTone  = 'flat';
        }

        return [
            'week'   => $this->display($week, $meId),
            'month'  => $this->display($month, $meId),
            'all'    => $this->display($all, $meId),
            'status' => [
                'ranked'       => $allRank !== null,
                'rank'         => $allRank,
                'percentile'   => $percentile,
                'total'        => $totalRanked,
                'delta'        => $delta,
                'delta_label'  => $deltaLabel,
                'delta_tone'   => $deltaTone,
            ],
        ];
    }

    /**
     * Ranked student list for a [from, to) window (null = unbounded).
     */
    private function ranked(?Carbon $from, ?Carbon $to): Collection
    {
        $q = DB::table('quiz_sessions as qs')
            ->join('users as u', 'u.id', '=', 'qs.student_id')
            ->where('u.role_id', Role::STUDENT)
            ->where('u.is_active', true)
            ->where('qs.session_type', '!=', 'training')
            ->whereNotNull('qs.completed_at');

        if ($from) { $q->where('qs.completed_at', '>=', $from); }
        if ($to)   { $q->where('qs.completed_at', '<', $to); }

        return $q->groupBy('qs.student_id', 'u.first_name', 'u.last_name')
            ->select(
                'qs.student_id',
                'u.first_name',
                'u.last_name',
                DB::raw('COALESCE(SUM(qs.correct_answers),0) as score')
            )
            ->havingRaw('COALESCE(SUM(qs.correct_answers),0) > 0')
            ->orderByDesc('score')
            ->orderBy('qs.student_id')
            ->get()
            ->values();
    }

    /** The student's 1-based rank in a ranked list, or null if absent. */
    private function meRank(Collection $rows, int $meId): ?int
    {
        foreach ($rows as $i => $r) {
            if ((int) $r->student_id === $meId) {
                return $i + 1;
            }
        }
        return null;
    }

    /**
     * Turn a ranked list into the rows the panel renders: the top 7, plus the
     * student's own row when they fall outside the top 7.
     */
    private function display(Collection $rows, int $meId): array
    {
        $shaped = $rows->values()->map(function ($r, $i) use ($meId) {
            return [
                'rank'     => $i + 1,
                'name'     => trim("{$r->first_name} {$r->last_name}"),
                'initials' => $this->initials($r->first_name, $r->last_name),
                'score'    => (int) $r->score,
                'is_me'    => (int) $r->student_id === $meId,
            ];
        });

        $top = $shaped->take(7)->values();

        $meRow = $shaped->firstWhere('is_me', true);
        if ($meRow && ! $top->contains(fn ($r) => $r['is_me'])) {
            $top->push($meRow);
        }

        return $top->all();
    }

    private function initials(?string $first, ?string $last): string
    {
        return strtoupper(substr((string) $first, 0, 1) . substr((string) $last, 0, 1));
    }
}
