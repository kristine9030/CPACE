<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Services\StreakService;
use App\Services\WeaknessDetector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function __construct(private WeaknessDetector $weakness) {}

    /**
     * Student performance dashboard - every figure on the page is computed live
     * from the student's own quiz history (quiz_sessions / quiz_answers) and the
     * per-topic performance_records. Nothing here is hard-coded.
     */
    public function index()
    {
        $studentId = Auth::id();
        $now       = Carbon::now();

        // ── Exam countdown (same source as the Dashboard banner) ───────────
        $profile    = DB::table('student_profiles')->where('user_id', $studentId)->first();
        $examDate   = $profile->exam_target_date ?? null;
        $daysToExam = $examDate
            ? max(0, (int) ceil((Carbon::parse($examDate)->startOfDay()->timestamp - $now->copy()->startOfDay()->timestamp) / 86400))
            : null;

        // Rolling 7-day windows used for the "from last week" deltas.
        $weekStart     = $now->copy()->subDays(7);
        $prevWeekStart = $now->copy()->subDays(14);

        // ── Base: every completed quiz this student has taken. We measure from
        //    the session summaries (total_items / correct_answers) so the figures
        //    cover the student's FULL history and match the Dashboard and Quizzes
        //    pages. total_items counts every question served, so a skipped
        //    question is included and counts against accuracy - exactly like the
        //    score shown at the end of a quiz. ──────────────────────────────
        // Training is a no-stakes practice mode and is excluded from every
        // figure on this page (it is still saved for the results review).
        $sessions = fn () => DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at');

        // Sum the session summaries over an optional date window (by started_at).
        $sumWindow = function ($from = null, $to = null) use ($sessions) {
            $q = $sessions();
            if ($from) {
                $q->where('started_at', '>=', $from);
            }
            if ($to) {
                $q->where('started_at', '<', $to);
            }
            $r = $q->selectRaw(
                'COALESCE(SUM(total_items),0) attempted,
                 COALESCE(SUM(correct_answers),0) correct,
                 COALESCE(SUM(duration_secs),0) duration'
            )->first();

            return [
                'attempted' => (int) $r->attempted,
                'correct'   => (int) $r->correct,
                'duration'  => (int) $r->duration,
            ];
        };

        // ── Headline totals (whole history) ────────────────────────────────
        $all             = $sumWindow();
        $totalAttempted  = $all['attempted'];
        $totalCorrect    = $all['correct'];
        $totalWrong      = max(0, $totalAttempted - $totalCorrect);
        $overallAccuracy = $totalAttempted > 0 ? (int) round($totalCorrect / $totalAttempted * 100) : 0;

        // ── This week vs previous week (by when the quiz was started) ──────
        $thisWeek = $sumWindow($weekStart, null);
        $prevWeek = $sumWindow($prevWeekStart, $weekStart);

        $accThisWeek = $thisWeek['attempted'] > 0 ? (int) round($thisWeek['correct'] / $thisWeek['attempted'] * 100) : 0;
        $accPrevWeek = $prevWeek['attempted'] > 0 ? (int) round($prevWeek['correct'] / $prevWeek['attempted'] * 100) : 0;

        // ── Average time per question ──────────────────────────────────────
        $avgSecs     = $totalAttempted > 0 ? (int) round($all['duration'] / $totalAttempted) : 0;
        $avgThisWeek = $thisWeek['attempted'] > 0 ? (int) round($thisWeek['duration'] / $thisWeek['attempted']) : 0;
        $avgPrevWeek = $prevWeek['attempted'] > 0 ? (int) round($prevWeek['duration'] / $prevWeek['attempted']) : 0;

        $stats = [
            'accuracy'        => $overallAccuracy,
            'accuracy_delta'  => $accThisWeek - $accPrevWeek,
            'attempted'       => $totalAttempted,
            'attempted_delta' => $thisWeek['attempted'] - $prevWeek['attempted'],
            'correct'         => $totalCorrect,
            'correct_delta'   => $thisWeek['correct'] - $prevWeek['correct'],
            'wrong'           => $totalWrong,
            'avg_secs'        => $avgSecs,
            'avg_time'        => $this->formatDuration($avgSecs),
            'avg_delta_secs'  => $avgThisWeek - $avgPrevWeek,
        ];

        // ── Streak ─────────────────────────────────────────────────────────
        // Computed live (training and testing, any mode, all count) so the
        // current-streak card is always up to date, not only after a quiz.
        $streakDays = app(StreakService::class)->current($studentId);

        // ── Performance-over-time line chart (last 7 days, daily accuracy) ──
        $chart = $this->dailyAccuracySeries($sessions, $now);

        // Daily / Weekly / Monthly series for the chart's granularity filter.
        // The SVG geometry is rebuilt client-side from these raw points so the
        // dropdown can switch the curve without a page reload.
        $chartSeries = $this->accuracySeries($sessions, $now);

        // ── Spark series for the stat cards (last 8 days) ──────────────────
        $spark = $this->sparkSeries($sessions, $now);

        // ── Per-topic strengths & weaknesses ───────────────────────────────
        $topicStats = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->where('performance_records.student_id', $studentId)
            ->where('performance_records.total_attempts', '>', 0)
            ->select(
                'topics.name as topic',
                'subjects.code as subject_code',
                'performance_records.correct_count',
                'performance_records.total_attempts',
                'performance_records.consecutive_wrong'
            )
            ->get()
            ->map(function ($r) {
                // Flag weak areas with the SAME rule the Spaced Repetition
                // Calendar uses (WeaknessDetector): accuracy < 60% over >= 5
                // attempts, or 3 wrong in a row. This keeps the two pages in
                // agreement so a topic the calendar treats as High priority is
                // the same topic shown here under Weaknesses.
                [$isWeak] = $this->weakness->evaluate($r);
                $r->correct  = (int) $r->correct_count;
                $r->attempts = (int) $r->total_attempts;
                $r->accuracy = (int) round($r->correct / max($r->attempts, 1) * 100);
                $r->is_weak  = $isWeak;
                return $r;
            });

        // Strengths / weaknesses, classified to match the calendar page:
        //   - Weakness: flagged by WeaknessDetector (same as the calendar's
        //     High-priority reviews).
        //   - Strength: at/above the calendar's mastery line (>= 75%) with
        //     enough evidence (>= MIN_ATTEMPTS) so one lucky question can't
        //     label a topic strong.
        // Every qualifying topic is listed - not capped at 3.
        $strengths  = $topicStats
            ->where('attempts', '>=', WeaknessDetector::MIN_ATTEMPTS)
            ->where('accuracy', '>=', 75)
            ->sortByDesc('accuracy')->values();
        $weaknesses = $topicStats->where('is_weak', true)->sortBy('accuracy')->values();

        // ── Recent activity (latest completed sessions) ────────────────────
        $recentActivity = DB::table('quiz_sessions')
            ->leftJoin('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->where('quiz_sessions.student_id', $studentId)
            ->where('quiz_sessions.session_type', '!=', 'training')
            ->whereNotNull('quiz_sessions.completed_at')
            ->orderByDesc('quiz_sessions.completed_at')
            ->limit(4)
            ->select(
                'quiz_sessions.mode',
                'quiz_sessions.session_type',
                'quiz_sessions.score_percent',
                'quiz_sessions.completed_at',
                'quiz_sessions.duration_secs',
                'subjects.code as subject_code'
            )
            ->get()
            ->map(function ($r) {
                $r->duration = $this->formatDuration((int) $r->duration_secs);
                return $r;
            });

        // ── Overall mastery donut (Strong / Medium / Weak topic mix) ───────
        $mastery = $this->masteryBreakdown($topicStats, $overallAccuracy);

        // ── Accuracy by subject area (every subject, 0% when untouched) ─────
        $subjectAccuracy = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('performance_records', function ($join) use ($studentId) {
                $join->on('performance_records.topic_id', '=', 'topics.id')
                     ->where('performance_records.student_id', '=', $studentId);
            })
            ->groupBy('subjects.id', 'subjects.code', 'subjects.name', 'subjects.passing_threshold')
            ->orderBy('subjects.id')
            ->select(
                'subjects.code',
                'subjects.name',
                'subjects.passing_threshold',
                DB::raw('COALESCE(SUM(performance_records.correct_count),0) as correct'),
                DB::raw('COALESCE(SUM(performance_records.total_attempts),0) as attempts')
            )
            ->get()
            ->map(function ($r) {
                $r->accuracy = $r->attempts > 0 ? (int) round($r->correct / $r->attempts * 100) : 0;
                $r->is_passing = $r->attempts > 0 && $r->accuracy >= (int) $r->passing_threshold;
                $r->color = $r->is_passing ? '#21a366' : $this->accuracyColor($r->accuracy);
                return $r;
            });

        // ── Data-driven insights & recommendations ─────────────────────────
        $insights = $this->buildInsights($weaknesses, $totalWrong, $studentId);

        // ── Consistency: which of the last 7 days had a quiz ───────────────
        $weekActivity = $this->weeklyActivity($studentId, $now);

        // ── GitHub-style study activity heatmap (last 12 months) ───────────
        $heatmap = $this->activityHeatmap($studentId, $now);

        // ── Headline dashboard metrics beyond the classic five ─────────────
        $studyHours      = round($all['duration'] / 3600, 1);
        $studyHoursDelta = $prevWeek['duration'] > 0
            ? round(($thisWeek['duration'] - $prevWeek['duration']) / $prevWeek['duration'] * 100, 1)
            : ($thisWeek['duration'] > 0 ? 100.0 : 0.0);
        $attemptedDeltaPct = $prevWeek['attempted'] > 0
            ? round(($thisWeek['attempted'] - $prevWeek['attempted']) / $prevWeek['attempted'] * 100, 1)
            : ($thisWeek['attempted'] > 0 ? 100.0 : 0.0);

        // Readiness: how close each practised subject is to its passing mark
        // (100 = every practised subject at/above threshold).
        $readiness      = $this->readinessScore($sessions, null, null);
        $readinessDelta = $this->readinessScore($sessions, $weekStart, null)
                        - $this->readinessScore($sessions, $prevWeekStart, $weekStart);

        // Study-time share per subject for the distribution donut.
        $studyDist = $this->studyDistribution($sessions, $studyHours);

        // Today vs yesterday study time.
        $todayStart = $now->copy()->startOfDay();
        $todaySecs  = $sumWindow($todayStart, null)['duration'];
        $yestSecs   = $sumWindow($todayStart->copy()->subDay(), $todayStart)['duration'];
        $todayStudy = $this->formatDuration($todaySecs);
        $todayDelta = $yestSecs > 0
            ? (int) round(($todaySecs - $yestSecs) / $yestSecs * 100)
            : ($todaySecs > 0 ? 100 : 0);

        // Consistency: share of the last 7 days with quiz activity, and how
        // many active days were gained/lost vs the week before.
        $activeDaysIn = function ($from, $to) use ($sessions) {
            $q = $sessions()->where('started_at', '>=', $from);
            if ($to) {
                $q->where('started_at', '<', $to);
            }
            return $q->get(['started_at'])
                ->map(fn ($s) => Carbon::parse($s->started_at)->toDateString())
                ->unique()->count();
        };
        $activeThis       = $activeDaysIn($weekStart, null);
        $activePrev       = $activeDaysIn($prevWeekStart, $weekStart);
        $consistencyPct   = (int) round($activeThis / 7 * 100);
        $consistencyDelta = (int) round(($activeThis - $activePrev) / 7 * 100);
        $streakDelta      = $activeThis - $activePrev;

        // Weekly study-hours goal.
        $goalTarget = 25;
        $goalHours  = round($thisWeek['duration'] / 3600, 1);
        $goalPct    = min(100, (int) round($goalHours / $goalTarget * 100));

        $weakestTopic = $weaknesses->first();

        // ── Unread notifications (header bell) ─────────────────────────────
        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $studentId)
            ->where('is_read', false)
            ->count();

        // ── Tab datasets (By Subject / By Topic / By Quiz Type / By Time) ──
        $byTopic     = $topicStats->sortByDesc('accuracy')->values();
        $byQuizType  = $this->byQuizType($studentId);
        $byTime      = $this->byTime($sessions);

        return view('student.performance', compact(
            'stats',
            'streakDays',
            'chart',
            'chartSeries',
            'spark',
            'strengths',
            'weaknesses',
            'recentActivity',
            'mastery',
            'subjectAccuracy',
            'insights',
            'weekActivity',
            'heatmap',
            'examDate',
            'daysToExam',
            'studyHours',
            'studyHoursDelta',
            'attemptedDeltaPct',
            'readiness',
            'readinessDelta',
            'studyDist',
            'todayStudy',
            'todayDelta',
            'consistencyPct',
            'consistencyDelta',
            'streakDelta',
            'goalTarget',
            'goalHours',
            'goalPct',
            'weakestTopic',
            'unreadNotifications',
            'byTopic',
            'byQuizType',
            'byTime'
        ));
    }

    /**
     * Per-mode breakdown (adaptive / topic / timed / challenge): how many
     * quizzes, how many questions, and the accuracy achieved in each mode.
     */
    private function byQuizType(int $studentId): array
    {
        // One pass over completed sessions, grouped by mode: how many quizzes,
        // how many questions served (total_items) and how many correct.
        $byMode = DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->groupBy('mode')
            ->select('mode',
                DB::raw('COUNT(*) as sessions'),
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct'))
            ->get()->keyBy('mode');

        $meta = [
            'adaptive'  => ['label' => 'Adaptive', 'icon' => 'fa-brain',  'tone' => 'green'],
            'topic'     => ['label' => 'Topic',    'icon' => 'fa-tag',    'tone' => 'amber'],
            'timed'     => ['label' => 'Timed',    'icon' => 'fa-clock',  'tone' => 'blue'],
            'challenge' => ['label' => 'Challenge','icon' => 'fa-trophy', 'tone' => 'red'],
        ];

        $rows = [];
        foreach ($meta as $mode => $m) {
            $att = (int) ($byMode[$mode]->attempted ?? 0);
            $cor = (int) ($byMode[$mode]->correct ?? 0);
            $rows[] = [
                'label'     => $m['label'],
                'icon'      => $m['icon'],
                'tone'      => $m['tone'],
                'sessions'  => (int) ($byMode[$mode]->sessions ?? 0),
                'attempted' => $att,
                'correct'   => $cor,
                'accuracy'  => $att > 0 ? (int) round($cor / $att * 100) : 0,
                'color'     => $this->accuracyColor($att > 0 ? (int) round($cor / $att * 100) : 0),
            ];
        }

        return $rows;
    }

    /**
     * Time-based analysis: accuracy per day of the week, plus a 14-day daily
     * history. Aggregated in PHP from the completed-session summaries so it
     * covers the full history and works on any database driver.
     */
    private function byTime(callable $sessions): array
    {
        $all = $sessions()
            ->select('started_at', 'total_items', 'correct_answers')
            ->get();

        // Accuracy per weekday (Mon..Sun).
        $weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weekday = array_fill(0, 7, ['attempted' => 0, 'correct' => 0]);

        // 14-day daily buckets.
        $daily = [];
        $now = Carbon::now();
        for ($i = 13; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $daily[$d] = ['attempted' => 0, 'correct' => 0];
        }

        foreach ($all as $s) {
            if (! $s->started_at) {
                continue;
            }
            $c = Carbon::parse($s->started_at);
            $idx = $c->dayOfWeekIso - 1; // 1=Mon → 0
            $weekday[$idx]['attempted'] += (int) $s->total_items;
            $weekday[$idx]['correct']   += (int) $s->correct_answers;

            $key = $c->toDateString();
            if (isset($daily[$key])) {
                $daily[$key]['attempted'] += (int) $s->total_items;
                $daily[$key]['correct']   += (int) $s->correct_answers;
            }
        }

        $weekdayRows = [];
        foreach ($weekdayLabels as $i => $label) {
            $att = $weekday[$i]['attempted'];
            $cor = $weekday[$i]['correct'];
            $acc = $att > 0 ? (int) round($cor / $att * 100) : 0;
            $weekdayRows[] = [
                'label'     => $label,
                'attempted' => $att,
                'accuracy'  => $acc,
                'color'     => $this->accuracyColor($acc),
            ];
        }

        $dailyRows = [];
        foreach ($daily as $date => $v) {
            $acc = $v['attempted'] > 0 ? (int) round($v['correct'] / $v['attempted'] * 100) : 0;
            $dailyRows[] = [
                'date'      => Carbon::parse($date)->format('M j'),
                'attempted' => $v['attempted'],
                'correct'   => $v['correct'],
                'accuracy'  => $acc,
                'color'     => $this->accuracyColor($acc),
            ];
        }

        // Best weekday by accuracy (only days with activity).
        $best = collect($weekdayRows)->where('attempted', '>', 0)->sortByDesc('accuracy')->first();

        return [
            'weekday'   => $weekdayRows,
            'daily'     => $dailyRows,
            'best_day'  => $best['label'] ?? null,
            'has_data'  => $all->isNotEmpty(),
        ];
    }

    /**
     * Build the last-7-days daily accuracy line as ready-to-render SVG geometry.
     * Days with no activity carry the previous day's accuracy forward so the
     * line stays continuous instead of crashing to zero.
     */
    private function dailyAccuracySeries(callable $sessions, Carbon $now): array
    {
        $width = 700;
        $top = 2;
        $bottom = 228;

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->startOfDay();
            $next = $day->copy()->addDay();

            $att = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('total_items');
            $cor = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('correct_answers');

            $days[] = [
                'label'    => $day->format('M j'),
                'short'    => $day->format('M j'),
                'accuracy' => $att > 0 ? (int) round($cor / $att * 100) : null,
                'has_data' => $att > 0,
            ];
        }

        // Carry the last known accuracy forward across empty days.
        $last = 0;
        foreach ($days as &$d) {
            if ($d['accuracy'] === null) {
                $d['accuracy'] = $last;
            } else {
                $last = $d['accuracy'];
            }
        }
        unset($d);

        $n = count($days);
        $points = [];
        foreach ($days as $i => $d) {
            $x = $n > 1 ? round($i * ($width / ($n - 1)), 1) : 0;
            $y = round($bottom - ($d['accuracy'] / 100) * ($bottom - $top), 1);
            $points[] = "$x,$y";
        }

        $pointsStr = implode(' ', $points);
        $first = explode(',', $points[0])[0];
        $lastX = explode(',', $points[$n - 1])[0];
        // Area fill: the line, then drop to the baseline and close.
        $areaPath = 'M' . implode(' L', $points) . " L{$lastX},{$bottom} L{$first},{$bottom} Z";

        $lastDay = $days[$n - 1];
        [$hx, $hy] = array_pad(explode(',', $points[$n - 1]), 2, '0');

        return [
            'has_data'   => collect($days)->contains('has_data', true),
            'points'     => $pointsStr,
            'area'       => $areaPath,
            'labels'     => array_column($days, 'short'),
            'highlight'  => ['x' => $hx, 'y' => $hy, 'label' => $lastDay['label'], 'accuracy' => $lastDay['accuracy']],
        ];
    }

    /**
     * Raw accuracy points for the Performance-over-time chart at three
     * granularities (Daily / Weekly / Monthly). Each series is a list of
     * labels and per-bucket accuracy (null where the student had no activity
     * in that bucket) plus the human date range it covers - the chart's
     * dropdown switches between these client-side. A bucket's accuracy is the
     * correct/attempted ratio over the SAME completed, non-training sessions
     * used everywhere else on the page.
     */
    private function accuracySeries(callable $sessions, Carbon $now): array
    {
        // Accuracy over a single [start, end) window.
        $bucket = function (Carbon $start, Carbon $end) use ($sessions) {
            $att = (int) $sessions()->whereBetween('started_at', [$start, $end])->sum('total_items');
            $cor = (int) $sessions()->whereBetween('started_at', [$start, $end])->sum('correct_answers');

            return [
                'attempted' => $att,
                'accuracy'  => $att > 0 ? (int) round($cor / $att * 100) : null,
            ];
        };

        // Turn a list of buckets into the {labels, values, has_data, range} the
        // view embeds as JSON.
        $pack = function (array $buckets, string $range): array {
            return [
                'labels'   => array_column($buckets, 'label'),
                'values'   => array_column($buckets, 'accuracy'),
                'has_data' => collect($buckets)->contains(fn ($b) => $b['attempted'] > 0),
                'range'    => $range,
            ];
        };

        // Daily — last 7 days.
        $daily = [];
        $dailyStart = $now->copy()->subDays(6)->startOfDay();
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->startOfDay();
            $daily[] = ['label' => $d->format('M j')] + $bucket($d, $d->copy()->addDay());
        }

        // Weekly — last 8 weeks (label = the week's Monday).
        $weekly = [];
        $weeklyStart = $now->copy()->subWeeks(7)->startOfWeek();
        for ($i = 7; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $weekly[] = ['label' => $start->format('M j')] + $bucket($start, $start->copy()->addWeek());
        }

        // Monthly — last 6 months.
        $monthly = [];
        $monthlyStart = $now->copy()->subMonths(5)->startOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $monthly[] = ['label' => $start->format('M')] + $bucket($start, $start->copy()->addMonth());
        }

        return [
            'daily'   => $pack($daily,   $dailyStart->format('M j') . ' – ' . $now->format('M j, Y')),
            'weekly'  => $pack($weekly,  $weeklyStart->format('M j') . ' – ' . $now->format('M j, Y')),
            'monthly' => $pack($monthly, $monthlyStart->format('M Y') . ' – ' . $now->format('M Y')),
        ];
    }

    /**
     * Per-day attempted/correct counts for the small stat-card sparklines
     * (last 8 days), returned as 0-100 scaled heights.
     */
    private function sparkSeries(callable $sessions, Carbon $now): array
    {
        $attempted = [];
        $correct = [];
        $accuracy = [];
        $time = [];
        $hours = [];

        for ($i = 7; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->startOfDay();
            $next = $day->copy()->addDay();

            $att = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('total_items');
            $cor = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('correct_answers');
            $dur = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('duration_secs');

            $attempted[] = $att;
            $correct[]   = $cor;
            $accuracy[]  = $att > 0 ? (int) round($cor / $att * 100) : 0;
            $time[]      = $att; // proxy: busier days = taller bar
            $hours[]     = $dur;
        }

        return [
            'attempted' => $this->scaleBars($attempted),
            'correct'   => $this->scaleBars($correct),
            'accuracy'  => $accuracy,
            'time'      => $this->scaleBars($time),
            'hours'     => $this->scaleBars($hours),
        ];
    }

    /**
     * Scale a list of raw counts into 12-100 bar heights (percentages).
     */
    private function scaleBars(array $values): array
    {
        $max = max($values ?: [0]);
        if ($max <= 0) {
            return array_fill(0, count($values), 12);
        }
        return array_map(fn ($v) => (int) round(12 + ($v / $max) * 88), $values);
    }

    /**
     * Classify attempted topics into Strong (>=80%) / Medium (>=60%) / Weak
     * bands and return percentages plus donut dash geometry.
     */
    private function masteryBreakdown($topicStats, int $overallAccuracy): array
    {
        $strong = $medium = $weak = 0;
        foreach ($topicStats as $t) {
            if ($t->accuracy >= 80) {
                $strong++;
            } elseif ($t->accuracy >= 60) {
                $medium++;
            } else {
                $weak++;
            }
        }

        $total = $strong + $medium + $weak;
        if ($total === 0) {
            return [
                'has_data' => false, 'level' => 0,
                'strong' => 0, 'medium' => 0, 'weak' => 0,
                'strong_dash' => 0, 'medium_dash' => 0, 'weak_dash' => 0,
                'medium_offset' => 0, 'weak_offset' => 0,
            ];
        }

        $strongPct = (int) round($strong / $total * 100);
        $mediumPct = (int) round($medium / $total * 100);
        $weakPct   = max(0, 100 - $strongPct - $mediumPct);

        return [
            'has_data'      => true,
            'level'         => $overallAccuracy,
            'strong'        => $strongPct,
            'medium'        => $mediumPct,
            'weak'          => $weakPct,
            // Donut: dasharray "<len> <gap>" out of a 100-unit circumference.
            'strong_dash'   => $strongPct,
            'medium_dash'   => $mediumPct,
            'weak_dash'     => $weakPct,
            'medium_offset' => -$strongPct,
            'weak_offset'   => -($strongPct + $mediumPct),
        ];
    }

    /**
     * Pick a meaningful bar colour for a subject accuracy.
     */
    private function accuracyColor(int $accuracy): string
    {
        if ($accuracy >= 75) {
            return '#21a366'; // green - strong
        }
        if ($accuracy >= 60) {
            return '#3b7ddd'; // blue - on track
        }
        if ($accuracy >= 45) {
            return '#e8910b'; // amber - needs work
        }
        return '#c0392b';     // red - weak
    }

    /**
     * Generate the Insights & Recommendations cards from the real data.
     */
    private function buildInsights($weaknesses, int $totalWrong, int $studentId): array
    {
        $insights = [];

        if ($weaknesses->isNotEmpty()) {
            $names = $weaknesses->take(2)->pluck('topic')->implode(' and ');
            $insights[] = [
                'tone'  => 'red',
                'icon'  => 'fa-chart-line',
                'title' => 'Focus on weak topics',
                'desc'  => "Spend more time on {$names}.",
            ];
        }

        if ($totalWrong > 0) {
            $insights[] = [
                'tone'  => 'green',
                'icon'  => 'fa-book-open',
                'title' => 'Review your mistakes',
                'desc'  => "You got {$totalWrong} question" . ($totalWrong === 1 ? '' : 's') . ' wrong. Review them to improve.',
            ];
        }

        // Encourage timed practice when the student rarely uses Timed mode.
        $timedCount = (int) DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->where('mode', 'timed')
            ->count();
        if ($timedCount < 3) {
            $insights[] = [
                'tone'  => 'blue',
                'icon'  => 'fa-clock',
                'title' => 'Practice more timed quizzes',
                'desc'  => 'Improve your speed. Try Timed Mode quizzes.',
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'tone'  => 'green',
                'icon'  => 'fa-trophy',
                'title' => 'Great work!',
                'desc'  => 'Take more quizzes to unlock personalised recommendations.',
            ];
        }

        return $insights;
    }

    /**
     * The LAST 7 DAYS (rolling window ending today) for the consistency banner.
     * A day is ticked only when it is part of the CURRENT STREAK run - the same
     * dates StreakService counts - so the ticks always equal the streak number
     * beside them (an N-day streak shows as the N right-most days ticked). Each
     * dot is labelled with its real weekday, with two letters where a single
     * one would be ambiguous (Su/Sa, T/Th), so the row reads clearly even
     * though it does not always begin on a Sunday.
     */
    private function weeklyActivity(int $studentId, Carbon $now): array
    {
        // Set of dates that form the current streak (training + testing count).
        $streakDates = array_flip(app(StreakService::class)->streakDates($studentId));

        // Unambiguous weekday labels, indexed by Carbon dayOfWeek (0=Sun..6=Sat).
        $weekdayLabels = ['Su', 'M', 'T', 'W', 'Th', 'F', 'Sa'];

        $days = [];
        $activeCount = 0;
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $done = isset($streakDates[$date->toDateString()]);
            if ($done) {
                $activeCount++;
            }
            $days[] = [
                'label' => $weekdayLabels[$date->dayOfWeek],
                'done'  => $done,
            ];
        }

        return [
            'days'         => $days,
            'active_count' => $activeCount,
        ];
    }

    /**
     * Readiness score (0-100): for every subject practised in the window,
     * how close its session accuracy is to that subject's passing threshold
     * (capped at 100), averaged across subjects. 100 means every practised
     * subject is at or above its passing mark.
     */
    private function readinessScore(callable $sessions, $from, $to): int
    {
        $q = $sessions()
            ->join('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->groupBy('quiz_sessions.subject_id', 'subjects.passing_threshold')
            ->select(
                'subjects.passing_threshold',
                DB::raw('COALESCE(SUM(quiz_sessions.total_items),0) att'),
                DB::raw('COALESCE(SUM(quiz_sessions.correct_answers),0) cor')
            );
        if ($from) {
            $q->where('quiz_sessions.started_at', '>=', $from);
        }
        if ($to) {
            $q->where('quiz_sessions.started_at', '<', $to);
        }

        $rows = $q->get()->filter(fn ($r) => (int) $r->att > 0);
        if ($rows->isEmpty()) {
            return 0;
        }

        return (int) round($rows->avg(function ($r) {
            $acc = $r->cor / $r->att * 100;
            $thr = max(1, (int) $r->passing_threshold);
            return min(100, $acc / $thr * 100);
        }));
    }

    /**
     * Study-time share per subject (for the Study Distribution donut).
     * Colours are assigned to subjects in a fixed order (by subject id) so a
     * subject keeps its colour no matter which subjects appear.
     */
    private function studyDistribution(callable $sessions, float $totalHours): array
    {
        $palette = ['#c0392b', '#3b7ddd', '#e8910b', '#8e44ad', '#21a366', '#d4589e'];

        $slices = $sessions()
            ->join('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->groupBy('subjects.id', 'subjects.code')
            ->orderBy('subjects.id')
            ->select('subjects.code', DB::raw('COALESCE(SUM(quiz_sessions.duration_secs),0) secs'))
            ->get()
            ->map(function ($r, $i) use ($palette) {
                $r->color = $palette[$i % count($palette)];
                return $r;
            })
            ->filter(fn ($r) => (int) $r->secs > 0)
            ->values();

        $total = max(1, $slices->sum('secs'));

        return [
            'has_data'    => $slices->isNotEmpty(),
            'total_hours' => $totalHours,
            'display_val' => $totalHours >= 1 ? round($totalHours, 1) : (int) round($totalHours * 60),
            'display_unit'=> $totalHours >= 1 ? 'hrs' : 'mins',
            'slices'      => $slices->map(fn ($r) => [
                'code'  => $r->code,
                'hours' => round($r->secs / 3600, 1),
                'pct'   => (int) round($r->secs / $total * 100),
                'color' => $r->color,
            ])->all(),
        ];
    }

    /**
     * GitHub-style activity heatmap: one cell per day for the last 12 months,
     * shaded by how many questions the student answered that day. Any completed
     * quiz counts (training included - it is real study activity, the same rule
     * the streak uses). Aggregated in PHP so it works on any database driver.
     */
    private function activityHeatmap(int $studentId, Carbon $now): array
    {
        // Grid starts on the Sunday on/before (today - 364 days) so every
        // column is a full Sun..Sat week, exactly like GitHub's graph.
        $start     = $now->copy()->subDays(364)->startOfDay();
        $gridStart = $start->copy()->subDays($start->dayOfWeek);

        $rows = DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->where('started_at', '>=', $gridStart)
            ->select('started_at', 'total_items')
            ->get();

        $perDay = [];
        foreach ($rows as $r) {
            if (! $r->started_at) {
                continue;
            }
            $key = Carbon::parse($r->started_at)->toDateString();
            $perDay[$key] = ($perDay[$key] ?? 0) + (int) $r->total_items;
        }

        // Quartile thresholds over the active days so the shading adapts to
        // this student's own volume instead of using fixed cut-offs.
        $counts = array_values(array_filter($perDay, fn ($v) => $v > 0));
        sort($counts);
        $q = function (float $p) use ($counts) {
            return empty($counts) ? 0 : $counts[(int) floor($p * (count($counts) - 1))];
        };
        $t1 = max(1, (int) $q(0.25));
        $t2 = max($t1 + 1, (int) $q(0.50));
        $t3 = max($t2 + 1, (int) $q(0.75));

        $weeks       = [];
        $monthLabels = [];
        $prevMonth   = null;
        $total       = 0;
        $activeDays  = 0;
        $best        = 0;

        $day   = $gridStart->copy();
        $today = $now->copy()->startOfDay();
        while ($day <= $today) {
            // Month label above the column where a new month starts.
            $m = $day->format('M');
            if ($m !== $prevMonth) {
                $monthLabels[count($weeks)] = $m;
                $prevMonth = $m;
            }

            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $future = $day->gt($today);
                $c      = $future ? 0 : ($perDay[$day->toDateString()] ?? 0);

                if (! $future) {
                    $total += $c;
                    if ($c > 0) {
                        $activeDays++;
                        $best = max($best, $c);
                    }
                }

                $week[] = [
                    'date'  => $day->format('M j, Y'),
                    'count' => $c,
                    // null level = day is in the future (cell stays blank)
                    'level' => $future ? null : ($c === 0 ? 0 : ($c <= $t1 ? 1 : ($c <= $t2 ? 2 : ($c <= $t3 ? 3 : 4)))),
                ];
                $day->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'weeks'       => $weeks,
            'labels'      => $monthLabels,
            'total'       => $total,
            'active_days' => $activeDays,
            'best'        => $best,
        ];
    }

    /**
     * Format seconds as "Nm Ns" / "Ns".
     */
    private function formatDuration(int $secs): string
    {
        if ($secs <= 0) {
            return '0s';
        }
        $m = intdiv($secs, 60);
        $s = $secs % 60;
        return $m > 0 ? "{$m}m {$s}s" : "{$s}s";
    }
}
