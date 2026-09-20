<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\Subject;
use App\Services\WeaknessDetector;
use App\Support\FacultySectionScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Faculty landing dashboard. Every figure, list and bar on the page is computed
 * live from the database - questions / quiz_sessions / performance_records - and
 * scoped to the CPALE subjects the Program Chair has assigned to the signed-in
 * faculty member (falling back to every subject for a brand-new account so the
 * page is never empty). Nothing here is hard-coded.
 */
class FacultyDashboardController extends Controller
{
    /** Difficulty enum -> human label used across the UI. */
    private const DIFFICULTY_LABELS = [
        'easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard',
    ];

    /** Subject brand colours reused for the "Questions by Subject" bars. */
    private const SUBJECT_COLORS = [
        'FAR'  => '#3b82f6',
        'AFAR' => '#17a2b8',
        'MS'   => '#8b5cf6',
        'TAX'  => '#27ae60',
        'AUD'  => '#e8567d',
        'RFBT' => '#f59e0b',
    ];

    /** Board-readiness accuracy benchmark, same threshold used across CPALE analytics. */
    private const READINESS_BENCHMARK = 75;

    /** Below this average score (with a minimum sample), a student is flagged at-risk. */
    private const AT_RISK_THRESHOLD = 50;

    public function index(Request $request)
    {
        $data = $this->computeDashboardData();

        return view('faculty.dashboard', $data);
    }

    /**
     * Re-run just the insights computation against the live database and
     * return it as JSON — backs the "Regenerate" button on the dashboard so
     * a faculty member can pull fresh insights after adding questions or
     * grading activity without reloading the whole page.
     */
    public function insights(Request $request)
    {
        $data = $this->computeDashboardData();

        return response()->json(['insights' => $data['insights']]);
    }

    /**
     * Every figure the dashboard (and its insights) needs, computed fresh
     * from the database and scoped to the faculty member's assigned subjects.
     */
    private function computeDashboardData(): array
    {
        // Subjects assigned to this faculty; fall back to all subjects so a
        // freshly-created account still sees the whole picture.
        $assigned = Auth::user()->assignedSubjects()->orderBy('subjects.id')->get();
        if ($assigned->isEmpty()) {
            $assigned = Subject::orderBy('id')->get();
        }
        $subjectIds = $assigned->pluck('id')->all();

        $now         = Carbon::now();
        $weekAgo     = $now->copy()->subDays(7);
        $monthAgo    = $now->copy()->subDays(30);
        $twoMonthAgo = $now->copy()->subDays(60);

        $weeklyTrend          = $this->weeklyTrend($subjectIds, $now);
        $questionsWeeklyTrend = $this->questionsWeeklyTrend($subjectIds, $now);
        $bySubject    = $this->questionsBySubject($assigned);
        $byType       = $this->questionsByType($subjectIds);
        $byDifficulty = $this->questionsByDifficulty($subjectIds);
        $studentBand  = $this->studentBandCounts($subjectIds);
        $stats        = $this->headlineStats($subjectIds, $weekAgo, $monthAgo, $twoMonthAgo, $assigned, $weeklyTrend);

        return [
            'assigned'        => $assigned,
            'stats'           => $stats,
            'recentQuestions' => $this->recentQuestions($subjectIds),
            'recentActivity'  => $this->recentActivity($subjectIds),
            'bySubject'       => $bySubject,
            'byType'          => $byType,
            'byDifficulty'    => $byDifficulty,
            'topStudents'     => $this->topStudents($subjectIds),
            'weeklyTrend'          => $weeklyTrend,
            'questionsWeeklyTrend' => $questionsWeeklyTrend,
            'studentBand'     => $studentBand,
            'benchmark'       => self::READINESS_BENCHMARK,
            'insights'        => $this->buildInsights($bySubject, $byType, $byDifficulty, $weeklyTrend, $stats, $studentBand, $assigned),
            'typeInsight'       => $this->typeInsight($byType),
            'difficultyInsight' => $this->difficultyInsight($byDifficulty),
        ];
    }

    /**
     * One-line read on the MCQ vs. True/False mix — the actual CPALE is almost
     * entirely multiple choice, so a bank that drifts too far from that format
     * doesn't prepare students for the exam they'll actually sit.
     */
    private function typeInsight(array $byType): string
    {
        if ($byType['total'] === 0) {
            return 'Add questions to see how your item formats compare to the real exam mix.';
        }

        $mcqPct = $byType['mcq']['pct'];

        if ($mcqPct >= 85) {
            return "Multiple Choice makes up {$mcqPct}% of the bank — closely mirrors the CPALE's actual format.";
        }
        if ($mcqPct >= 60) {
            return "Multiple Choice makes up {$mcqPct}% of the bank — a reasonable mix, though the real exam leans more heavily MCQ.";
        }

        return "True/False makes up {$byType['tf']['pct']}% of the bank — consider shifting toward Multiple Choice to better match exam-day format.";
    }

    /**
     * One-line read on the Easy/Medium/Hard mix — the lever a faculty member
     * would actually pull (add more of X) rather than just the raw split.
     */
    private function difficultyInsight(array $byDifficulty): string
    {
        $total = $byDifficulty['easy']['count'] + $byDifficulty['medium']['count'] + $byDifficulty['hard']['count'];
        if ($total === 0) {
            return 'Add questions to see the difficulty mix.';
        }

        if ($byDifficulty['hard']['pct'] < 15) {
            return "Only {$byDifficulty['hard']['pct']}% is Hard difficulty — add more challenging items to stretch students who are already passing.";
        }
        if ($byDifficulty['easy']['pct'] < 15) {
            return "Only {$byDifficulty['easy']['pct']}% is Easy difficulty — students still building fundamentals may not have enough of a foothold.";
        }
        if ($byDifficulty['medium']['pct'] >= 60) {
            return "Medium items dominate the bank ({$byDifficulty['medium']['pct']}%) — a fairly conservative curve overall.";
        }

        return "A healthy spread across difficulty levels — {$byDifficulty['easy']['pct']}% Easy, {$byDifficulty['medium']['pct']}% Medium, {$byDifficulty['hard']['pct']}% Hard.";
    }

    /**
     * Last 8 calendar weeks in scope: distinct active students, quizzes taken,
     * and average accuracy — feeds the Engagement / Accuracy trend charts.
     */
    private function weeklyTrend(array $subjectIds, Carbon $now)
    {
        return collect(range(7, 0))->map(function (int $i) use ($subjectIds, $now) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end   = $start->copy()->endOfWeek();

            $row = $this->scopedSessions($subjectIds)
                ->whereBetween('quiz_sessions.completed_at', [$start, $end])
                ->select(
                    DB::raw('COUNT(DISTINCT quiz_sessions.student_id) as active_students'),
                    DB::raw('COUNT(*) as quizzes'),
                    DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                    DB::raw('COALESCE(SUM(correct_answers),0) as correct')
                )
                ->first();

            $attempted = (int) ($row->attempted ?? 0);

            return [
                'label'           => $start->format('M j'),
                'active_students' => (int) ($row->active_students ?? 0),
                'quizzes'         => (int) ($row->quizzes ?? 0),
                'accuracy'        => $attempted > 0 ? (int) round(((int) $row->correct) / $attempted * 100) : null,
            ];
        })->values();
    }

    /**
     * Last 8 calendar weeks of test-bank growth in scope: how many questions
     * were added that week, and the running total as of that week's end —
     * feeds the KPI sparklines for Total Questions / Questions Added.
     */
    private function questionsWeeklyTrend(array $subjectIds, Carbon $now)
    {
        $baseTotal = (clone $this->scopedQuestions($subjectIds))
            ->where('questions.created_at', '<', $now->copy()->subWeeks(7)->startOfWeek())
            ->count();

        $running = $baseTotal;

        return collect(range(7, 0))->map(function (int $i) use ($subjectIds, $now, &$running) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end   = $start->copy()->endOfWeek();

            $added = (clone $this->scopedQuestions($subjectIds))
                ->whereBetween('questions.created_at', [$start, $end])
                ->count();

            $running += $added;

            return [
                'label'      => $start->format('M j'),
                'added'      => $added,
                'cumulative' => $running,
            ];
        })->values();
    }

    /**
     * Base query for the questions that belong to the faculty's scoped subjects.
     */
    private function scopedQuestions(array $subjectIds)
    {
        return DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereIn('topics.subject_id', $subjectIds);
    }

    /**
     * Base query for completed, graded (non-training) sessions in scope.
     */
    private function scopedSessions(array $subjectIds)
    {
        $query = DB::table('quiz_sessions')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'quiz_sessions.student_id')
            ->where('quiz_sessions.session_type', '!=', 'training')->where('quiz_sessions.is_practice_room', false)
            ->whereNotNull('quiz_sessions.completed_at')
            ->select('quiz_sessions.*');

        return FacultySectionScope::apply($query, Auth::user(), $subjectIds, 'quiz_sessions.subject_id', 'student_profiles.section');
    }

    /**
     * The four headline cards. Each figure carries not just a delta but the
     * context a program head actually needs to decide something: a benchmark
     * distance, a per-subject average, or a week-over-week pace comparison.
     */
    private function headlineStats(array $subjectIds, Carbon $weekAgo, Carbon $monthAgo, Carbon $twoMonthAgo, $assigned, $weeklyTrend): array
    {
        $totalQuestions = (clone $this->scopedQuestions($subjectIds))->count();
        $addedThisWeek  = (clone $this->scopedQuestions($subjectIds))
            ->where('questions.created_at', '>=', $weekAgo)->count();

        // Active students: distinct students with graded activity in scope.
        $activeStudents = (clone $this->scopedSessions($subjectIds))
            ->distinct()->count('student_id');

        // "New this month": students whose earliest graded session in scope is
        // within the last 30 days.
        $firsts = $this->scopedSessions($subjectIds)
            ->select('student_id', DB::raw('MIN(started_at) as first_seen'))
            ->groupBy('student_id');

        $newThisMonth = DB::query()
            ->fromSub($firsts, 'firsts')
            ->where('first_seen', '>=', $monthAgo)
            ->count();

        // Average student score this month vs the previous month.
        $avgNow  = $this->avgScore($subjectIds, $monthAgo, null);
        $avgPrev = $this->avgScore($subjectIds, $twoMonthAgo, $monthAgo);
        $avgAll  = $this->avgScore($subjectIds, null, null);
        $avgDelta = ($avgNow !== null && $avgPrev !== null) ? $avgNow - $avgPrev : null;
        $avgScore = $avgAll ?? 0;

        // Week-over-week engagement, straight from the trend series so the
        // number on the card and the chart never disagree.
        $thisWeekTrend = $weeklyTrend->last();
        $lastWeekTrend = $weeklyTrend->count() > 1 ? $weeklyTrend[$weeklyTrend->count() - 2] : null;
        $engagementDelta = $lastWeekTrend ? $thisWeekTrend['active_students'] - $lastWeekTrend['active_students'] : null;
        $engagementDeltaPct = ($engagementDelta !== null && $lastWeekTrend['active_students'] > 0)
            ? (int) round($engagementDelta / $lastWeekTrend['active_students'] * 100)
            : null;

        // Weekly content pace over the trailing 8 weeks, to judge whether
        // this week's additions are keeping up with the usual cadence.
        $eightWeeksAgo = $weekAgo->copy()->subWeeks(7);
        $addedLast8Weeks = (clone $this->scopedQuestions($subjectIds))
            ->where('questions.created_at', '>=', $eightWeeksAgo)->count();
        $weeklyPaceAvg = (int) round($addedLast8Weeks / 8);

        $subjectCount = max(1, $assigned->count());

        return [
            'total_questions'      => $totalQuestions,
            'questions_per_subject'=> (int) round($totalQuestions / $subjectCount),
            'added_this_week'      => $addedThisWeek,
            'weekly_pace_avg'      => $weeklyPaceAvg,
            'active_students'      => $activeStudents,
            'new_this_month'       => $newThisMonth,
            'engagement_delta'     => $engagementDelta,
            'engagement_delta_pct' => $engagementDeltaPct,
            'avg_score'            => $avgScore,
            'avg_delta'            => $avgDelta,
            'benchmark_gap'        => $avgScore - self::READINESS_BENCHMARK,
        ];
    }

    /**
     * Students bucketed by average completed-session score in scope (min.
     * sample so a single lucky/unlucky quiz can't misclassify anyone) —
     * feeds the "students needing attention" insight and readiness split.
     */
    private function studentBandCounts(array $subjectIds): array
    {
        $agg = $this->scopedSessions($subjectIds)
            ->join('users', 'users.id', '=', 'quiz_sessions.student_id')
            ->where('users.role_id', Role::STUDENT)
            ->groupBy('users.id')
            ->havingRaw('SUM(total_items) >= ?', [WeaknessDetector::MIN_ATTEMPTS])
            ->select(
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct')
            )
            ->get()
            ->map(fn ($r) => (int) $r->attempted > 0 ? (int) round($r->correct / $r->attempted * 100) : 0);

        return [
            'measured'  => $agg->count(),
            'ready'     => $agg->filter(fn ($s) => $s >= self::READINESS_BENCHMARK)->count(),
            'at_risk'   => $agg->filter(fn ($s) => $s < self::AT_RISK_THRESHOLD)->count(),
            'developing'=> $agg->filter(fn ($s) => $s >= self::AT_RISK_THRESHOLD && $s < self::READINESS_BENCHMARK)->count(),
        ];
    }

    /**
     * Short, data-driven narrative cards — the "so what" a program head would
     * otherwise have to work out themselves by staring at the charts.
     */
    private function buildInsights($bySubject, array $byType, array $byDifficulty, $weeklyTrend, array $stats, array $studentBand, $assigned): array
    {
        $insights = [];

        // Engagement momentum.
        if ($stats['engagement_delta'] !== null) {
            if ($stats['engagement_delta'] > 0) {
                $insights[] = [
                    'tone' => 'good', 'icon' => 'fa-arrow-trend-up',
                    'title' => 'Engagement is climbing',
                    'text' => "Active students are up {$stats['engagement_delta']}" . ($stats['engagement_delta_pct'] !== null ? " ({$stats['engagement_delta_pct']}%)" : '') . ' week-over-week — momentum is on your side.',
                ];
            } elseif ($stats['engagement_delta'] < 0) {
                $insights[] = [
                    'tone' => 'warn', 'icon' => 'fa-arrow-trend-down',
                    'title' => 'Engagement is slipping',
                    'text' => 'Active students fell by ' . abs($stats['engagement_delta']) . ' week-over-week. Consider assigning a class quiz to re-engage the section.',
                ];
            } else {
                $insights[] = [
                    'tone' => 'info', 'icon' => 'fa-minus',
                    'title' => 'Engagement is flat',
                    'text' => 'Active student count is unchanged from last week.',
                ];
            }
        }

        // Accuracy vs. the board-readiness benchmark.
        if ($stats['avg_score'] > 0) {
            if ($stats['benchmark_gap'] >= 0) {
                $insights[] = [
                    'tone' => 'good', 'icon' => 'fa-check-circle',
                    'title' => 'Above the readiness benchmark',
                    'text' => "Average accuracy ({$stats['avg_score']}%) is {$stats['benchmark_gap']} pts above the " . self::READINESS_BENCHMARK . "% board-readiness benchmark.",
                ];
            } else {
                $insights[] = [
                    'tone' => 'crit', 'icon' => 'fa-triangle-exclamation',
                    'title' => 'Below the readiness benchmark',
                    'text' => "Average accuracy ({$stats['avg_score']}%) is " . abs($stats['benchmark_gap']) . ' pts below the ' . self::READINESS_BENCHMARK . '% benchmark — review the weakest topics before the next mock exam.',
                ];
            }
        }

        // Students needing intervention.
        if ($studentBand['at_risk'] > 0) {
            $insights[] = [
                'tone' => 'crit', 'icon' => 'fa-user-clock',
                'title' => $studentBand['at_risk'] . ' student' . ($studentBand['at_risk'] === 1 ? '' : 's') . ' at risk',
                'text' => 'Averaging below ' . self::AT_RISK_THRESHOLD . '% with enough attempts to be measured — worth a direct check-in or remedial material.',
            ];
        }

        // Difficulty mix — is the bank challenging enough?
        if ($byDifficulty['hard']['count'] + $byDifficulty['medium']['count'] + $byDifficulty['easy']['count'] > 0) {
            if ($byDifficulty['hard']['pct'] < 15) {
                $insights[] = [
                    'tone' => 'warn', 'icon' => 'fa-layer-group',
                    'title' => 'Test bank is Easy-heavy',
                    'text' => "Only {$byDifficulty['hard']['pct']}% of questions are Hard difficulty — top students may not be getting stretched. Consider adding harder items.",
                ];
            } elseif ($byDifficulty['easy']['pct'] < 15) {
                $insights[] = [
                    'tone' => 'info', 'icon' => 'fa-layer-group',
                    'title' => 'Test bank skews Hard',
                    'text' => "Only {$byDifficulty['easy']['pct']}% of questions are Easy — students still building fundamentals may struggle to find a foothold.",
                ];
            }
        }

        // Weakest subject by content coverage.
        if ($bySubject->count() > 1) {
            $thin = $bySubject->sortBy('total')->first();
            $avgPerSubject = (int) round($bySubject->sum('total') / max(1, $bySubject->count()));
            if ($thin['total'] < $avgPerSubject * 0.6) {
                $insights[] = [
                    'tone' => 'warn', 'icon' => 'fa-database',
                    'title' => "{$thin['code']} needs more questions",
                    'text' => "{$thin['code']} has only {$thin['total']} question" . ($thin['total'] === 1 ? '' : 's') . ", well below your {$avgPerSubject}-question average per subject.",
                ];
            }
        }

        // Content pace.
        if ($stats['weekly_pace_avg'] > 0 && $stats['added_this_week'] < $stats['weekly_pace_avg'] * 0.5) {
            $insights[] = [
                'tone' => 'info', 'icon' => 'fa-gauge',
                'title' => 'Slower content pace this week',
                'text' => "{$stats['added_this_week']} question" . ($stats['added_this_week'] === 1 ? '' : 's') . ' added vs. your usual ~' . $stats['weekly_pace_avg'] . '/week average.',
            ];
        }

        return $insights;
    }

    /**
     * Average completed-session score (%) in scope over an optional window.
     */
    private function avgScore(array $subjectIds, ?Carbon $from, ?Carbon $to): ?int
    {
        $agg = $this->scopedSessions($subjectIds)
            ->when($from, fn ($q) => $q->where('started_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('started_at', '<', $to))
            ->select(DB::raw('COALESCE(SUM(total_items),0) as attempted'), DB::raw('COALESCE(SUM(correct_answers),0) as correct'))
            ->first();

        $attempted = (int) ($agg->attempted ?? 0);
        if ($attempted === 0) {
            return null;
        }

        return (int) round(((int) $agg->correct) / $attempted * 100);
    }

    /**
     * The most recently added questions in scope (for the left table).
     */
    private function recentQuestions(array $subjectIds)
    {
        return $this->scopedQuestions($subjectIds)
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->orderByDesc('questions.id')
            ->limit(5)
            ->select(
                'questions.id',
                'questions.question_text',
                'questions.question_type',
                'questions.difficulty',
                'questions.is_active',
                'questions.created_at',
                'subjects.code as subject_code'
            )
            ->get()
            ->map(fn ($q) => [
                'id'         => (int) $q->id,
                'text'       => $q->question_text,
                'type_label' => $q->question_type === 'mcq' ? 'Multiple Choice' : 'True / False',
                'difficulty' => self::DIFFICULTY_LABELS[$q->difficulty] ?? ucfirst($q->difficulty),
                'active'     => (bool) $q->is_active,
                'subject'    => $q->subject_code,
                'ago'        => $q->created_at ? Carbon::parse($q->created_at)->diffForHumans() : '',
            ]);
    }

    /**
     * Recent completed quiz sessions in scope, as an activity feed.
     */
    private function recentActivity(array $subjectIds)
    {
        $sessions = $this->scopedSessions($subjectIds)
            ->join('users', 'users.id', '=', 'quiz_sessions.student_id')
            ->join('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->where('users.role_id', Role::STUDENT)
            ->orderByDesc('quiz_sessions.completed_at')
            ->limit(6)
            ->select(
                'users.first_name',
                'users.last_name',
                'quiz_sessions.session_type',
                'quiz_sessions.score_percent',
                'quiz_sessions.completed_at',
                'subjects.code as subject_code'
            )
            ->get();

        return $sessions->map(function ($s) {
            $score = $s->score_percent !== null ? (int) round($s->score_percent) : null;
            $name  = trim("{$s->first_name} {$s->last_name}");

            // Icon / tone driven by the achieved score.
            if ($score === null) {
                $tone = ['bg' => '#f1f5f9', 'fg' => '#64748b', 'icon' => 'fa-hourglass-half'];
            } elseif ($score < 50) {
                $tone = ['bg' => '#fde8e8', 'fg' => '#c0392b', 'icon' => 'fa-exclamation-circle'];
            } elseif ($score < 75) {
                $tone = ['bg' => '#dbeafe', 'fg' => '#2563eb', 'icon' => 'fa-brain'];
            } else {
                $tone = ['bg' => '#d1fae5', 'fg' => '#059669', 'icon' => 'fa-check-circle'];
            }

            $typeLabel = match ($s->session_type) {
                'mock_exam'     => 'Mock Exam',
                'spaced_review' => 'Spaced Review',
                'testing'       => 'Quiz',
                default         => ucfirst(str_replace('_', ' ', $s->session_type)),
            };

            return [
                'name'    => $name,
                'detail'  => "{$s->subject_code} {$typeLabel}" . ($score !== null ? " &bull; Score: {$score}%" : ''),
                'ago'     => $s->completed_at ? Carbon::parse($s->completed_at)->diffForHumans() : '',
                'tone'    => $tone,
            ];
        });
    }

    /**
     * Question count per assigned subject, with a bar width relative to the
     * busiest subject.
     */
    private function questionsBySubject($assigned)
    {
        $counts = DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereIn('topics.subject_id', $assigned->pluck('id')->all())
            ->groupBy('topics.subject_id')
            ->select('topics.subject_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'subject_id');

        $max = max(1, (int) ($counts->max() ?? 0));

        return $assigned->map(fn ($s) => [
            'code'  => $s->code,
            'total' => (int) ($counts[$s->id] ?? 0),
            'width' => (int) round(((int) ($counts[$s->id] ?? 0)) / $max * 100),
            'color' => self::SUBJECT_COLORS[$s->code] ?? '#7B1D1D',
        ])->sortByDesc('total')->values();
    }

    /**
     * Multiple-choice vs true/false split in scope.
     */
    private function questionsByType(array $subjectIds): array
    {
        $rows = (clone $this->scopedQuestions($subjectIds))
            ->groupBy('questions.question_type')
            ->select('questions.question_type', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'question_type');

        $mcq   = (int) ($rows['mcq'] ?? 0);
        $tf    = (int) ($rows['true_false'] ?? 0);
        $total = max(1, $mcq + $tf);

        return [
            'total' => $mcq + $tf,
            'mcq'   => ['count' => $mcq, 'pct' => round($mcq / $total * 100, 1)],
            'tf'    => ['count' => $tf,  'pct' => round($tf / $total * 100, 1)],
        ];
    }

    /**
     * Easy / Medium / Hard split in scope.
     */
    private function questionsByDifficulty(array $subjectIds): array
    {
        $rows = (clone $this->scopedQuestions($subjectIds))
            ->groupBy('questions.difficulty')
            ->select('questions.difficulty', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'difficulty');

        $easy   = (int) ($rows['easy'] ?? 0);
        $medium = (int) ($rows['moderate'] ?? 0);
        $hard   = (int) ($rows['difficult'] ?? 0);
        $total  = max(1, $easy + $medium + $hard);

        return [
            'easy'   => ['count' => $easy,   'pct' => round($easy / $total * 100, 1)],
            'medium' => ['count' => $medium, 'pct' => round($medium / $total * 100, 1)],
            'hard'   => ['count' => $hard,   'pct' => round($hard / $total * 100, 1)],
        ];
    }

    /**
     * Top students by average completed-session score in scope (min. sample so a
     * single lucky quiz can't top the board).
     */
    private function topStudents(array $subjectIds)
    {
        $agg = $this->scopedSessions($subjectIds)
            ->join('users', 'users.id', '=', 'quiz_sessions.student_id')
            ->where('users.role_id', Role::STUDENT)
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->havingRaw('SUM(total_items) >= ?', [WeaknessDetector::MIN_ATTEMPTS])
            ->select(
                'users.first_name',
                'users.last_name',
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct')
            )
            ->get();

        return $agg->map(fn ($r) => [
            'name'  => trim("{$r->first_name} {$r->last_name}"),
            'score' => (int) $r->attempted > 0 ? (int) round($r->correct / $r->attempted * 100) : 0,
        ])->sortByDesc('score')->take(3)->values();
    }
}
