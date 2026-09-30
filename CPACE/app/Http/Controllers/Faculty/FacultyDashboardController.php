<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Concerns\ReadsChartFilters;
use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\Subject;
use App\Services\WeaknessDetector;
use App\Support\CurriculumScope;
use App\Support\FacultySectionScope;
use App\Support\PeriodBuckets;
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
    use ReadsChartFilters;

    /** Difficulty enum -> human label used across the UI. */
    private const DIFFICULTY_LABELS = [
        'easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard',
    ];

    /**
     * Board-readiness accuracy benchmark. Kept equal to
     * ChairAnalyticsService::READY_ACCURACY on purpose, so "ready"/"above
     * benchmark" means the same 75% everywhere in the app (faculty dashboard,
     * chair's Readiness Bands, subjects.passing_threshold's default) instead
     * of each screen quietly defining its own bar.
     */
    private const READINESS_BENCHMARK = 75;

    /**
     * Below this average score (with a minimum sample), a student is flagged
     * at-risk; between this and READINESS_BENCHMARK is "developing". Kept
     * equal to ChairAnalyticsService::DEVELOPING_ACCURACY so the Ready /
     * Developing / At-risk split means the same thing on both dashboards.
     */
    private const AT_RISK_THRESHOLD = 60;

    /**
     * A student needs this many attempts in scope before they're "measured"
     * at all (below this: not yet measurable, same as the chair's Readiness
     * Bands). Kept equal to ChairAnalyticsService::DEVELOPING_ATTEMPTS so
     * "measured" means the same sample size on both dashboards - this is
     * deliberately higher than WeaknessDetector::MIN_ATTEMPTS (5), which is
     * a different, more sensitive floor used to flag a single weak topic
     * early, not to certify a student's overall readiness.
     */
    private const MEASURED_MIN_ATTEMPTS = 20;

    /** "Ready" also needs this many attempts and this many distinct subjects
     * touched, on top of clearing READINESS_BENCHMARK - matching
     * ChairAnalyticsService::READY_ATTEMPTS / READY_SUBJECTS, so a student
     * can't be called Ready off a lucky handful of questions in one subject.
     * READY_SUBJECTS gracefully caps to however many subjects this faculty
     * member actually has in scope, so it stays reachable for a faculty
     * assigned to only 1-2 subjects. */
    private const READY_ATTEMPTS = 50;
    private const READY_SUBJECTS = 3;


    /** The dashboard's filtered window, set once per request by computeDashboardData(). */
    private Carbon $from;
    private Carbon $to;
    private ?string $section = null;

    public function index(Request $request)
    {
        $data = $this->computeDashboardData($request);

        return view('faculty.dashboard', $data);
    }

    /**
     * Every figure the dashboard (and its insights) needs, computed fresh
     * from the database and scoped to the faculty member's assigned subjects,
     * then narrowed by the filter bar: a date range, one of those subjects,
     * and one of the sections this faculty member handles.
     *
     * Student figures follow all three filters. Test-bank composition (total
     * questions, the by-subject / type / difficulty splits) has no section and
     * describes the bank as it stands, so only the subject filter applies to
     * it; "questions added" follows the date range too.
     */
    private function computeDashboardData(Request $request): array
    {
        // Subjects assigned to this faculty; fall back to all subjects so a
        // freshly-created account still sees the whole picture.
        $assigned = Auth::user()->assignedSubjects()->orderBy('subjects.id')->get();
        if ($assigned->isEmpty()) {
            $assigned = Subject::orderBy('id')->get();
        }

        $sectionOptions = $this->sectionOptions($assigned->pluck('id')->all());
        $filters = $this->chartFilters($request);
        // Only this faculty member's own subjects and sections can be picked.
        if ($filters['subject'] && ! $assigned->contains('id', $filters['subject'])) {
            $filters['subject'] = null;
        }
        if ($filters['section'] && ! $sectionOptions->contains($filters['section'])) {
            $filters['section'] = null;
        }
        $filters = array_intersect_key($filters, array_flip(['from', 'to', 'subject', 'section']));

        $scope = $filters['subject'] ? $assigned->where('id', $filters['subject'])->values() : $assigned;
        $subjectIds = $scope->pluck('id')->all();
        $this->from = Carbon::parse($filters['from'])->startOfDay();
        $this->to = Carbon::parse($filters['to'])->endOfDay();
        $this->section = $filters['section'];

        $trend        = $this->periodTrend($subjectIds);
        $bySubject    = $this->questionsBySubject($scope);
        $byType       = $this->questionsByType($subjectIds);
        $byDifficulty = $this->questionsByDifficulty($subjectIds);
        $studentBand  = $this->studentBandCounts($subjectIds);
        $stats        = $this->headlineStats($subjectIds, $scope);

        return [
            'assigned'        => $assigned,
            'filters'         => $filters,
            'defaults'        => array_intersect_key($this->chartFilters(new Request()), array_flip(['from', 'to', 'subject', 'section'])),
            'sectionOptions'  => $sectionOptions,
            'range'           => ['days' => (int) $this->from->diffInDays($this->to) + 1, 'bucket' => $trend->first()['unit'] ?? 'day'],
            'stats'           => $stats,
            'recentQuestions' => $this->recentQuestions($subjectIds),
            'recentActivity'  => $this->recentActivity($subjectIds),
            'bySubject'       => $bySubject,
            'byType'          => $byType,
            'byDifficulty'    => $byDifficulty,
            'topStudents'     => $this->topStudents($subjectIds),
            'weeklyTrend'     => $trend->map(fn ($b) => collect($b)->except(['unit'])->all())->values(),
            'studentBand'     => $studentBand,
            'benchmark'       => self::READINESS_BENCHMARK,
            'atRiskThreshold' => self::AT_RISK_THRESHOLD,
            'chartInsights'   => $this->chartInsights(
                $this->buildInsights($bySubject, $byType, $byDifficulty, $stats, $studentBand),
                $byType,
                $byDifficulty,
            ),
        ];
    }

    /**
     * Insights grouped by the chart they explain, shown as a hover tip on
     * that chart rather than as a separate block of cards. The format and
     * difficulty mix reads always have something to say, so those two charts
     * always get a tip; the rest appear only when an insight fires.
     *
     * @return array<string, array{tone: string, items: array<int, array{title: string, text: string}>}>
     */
    private function chartInsights(array $insights, array $byType, array $byDifficulty): array
    {
        $grouped = collect($insights)->groupBy('target');

        if ($byType['total'] > 0) {
            $grouped['type'] = collect([['tone' => 'info', 'title' => 'Format mix', 'text' => $this->typeInsight($byType)]]);
        }
        if (! $grouped->has('difficulty') && ($byDifficulty['easy']['count'] + $byDifficulty['medium']['count'] + $byDifficulty['hard']['count']) > 0) {
            $grouped['difficulty'] = collect([['tone' => 'info', 'title' => 'Difficulty mix', 'text' => $this->difficultyInsight($byDifficulty)]]);
        }

        // The tip wears the most urgent tone among its insights.
        $rank = ['crit' => 0, 'warn' => 1, 'info' => 2, 'good' => 3];

        return $grouped->map(fn ($items) => [
            'tone' => $items->sortBy(fn ($i) => $rank[$i['tone']] ?? 9)->first()['tone'],
            'items' => $items->map(fn ($i) => ['title' => $i['title'], 'text' => $i['text']])->values()->all(),
        ])->all();
    }

    /**
     * The sections this faculty member may filter by: the ones the chair
     * assigned for their subjects, or every active section when they are
     * unrestricted for any subject (same rule as FacultySectionScope).
     */
    private function sectionOptions(array $subjectIds): \Illuminate\Support\Collection
    {
        $names = [];
        foreach ($subjectIds as $subjectId) {
            $forSubject = Auth::user()->sectionNamesForSubject((int) $subjectId);
            if ($forSubject === null) {
                return DB::table('sections')->where('is_active', true)->orderBy('name')->pluck('name');
            }
            $names = array_merge($names, $forSubject);
        }

        return collect(array_values(array_unique($names)))->sort()->values();
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
     * The filtered range, bucketed by day / week / month: distinct active
     * students, quizzes taken and accuracy — feeds the Engagement / Accuracy
     * trend charts.
     */
    private function periodTrend(array $subjectIds)
    {
        return PeriodBuckets::for($this->from, $this->to)->map(function (array $bucket) use ($subjectIds) {
            $row = $this->scopedSessions($subjectIds)
                ->whereBetween('quiz_sessions.completed_at', [$bucket['start'], $bucket['end']])
                ->select(
                    DB::raw('COUNT(DISTINCT quiz_sessions.student_id) as active_students'),
                    DB::raw('COUNT(*) as quizzes'),
                    DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                    DB::raw('COALESCE(SUM(correct_answers),0) as correct')
                )
                ->first();

            $attempted = (int) ($row->attempted ?? 0);

            return [
                'unit'            => $bucket['unit'],
                'label'           => $bucket['label'],
                'active_students' => (int) ($row->active_students ?? 0),
                'quizzes'         => (int) ($row->quizzes ?? 0),
                'accuracy'        => $attempted > 0 ? (int) round(((int) $row->correct) / $attempted * 100) : null,
            ];
        })->values();
    }

    /**
     * Base query for the questions that belong to the faculty's scoped subjects.
     */
    private function scopedQuestions(array $subjectIds)
    {
        return CurriculumScope::restrictToActive(DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
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
            ->when($this->section, fn ($q) => $q->where('student_profiles.section', $this->section))
            ->select('quiz_sessions.*');

        return FacultySectionScope::apply($query, Auth::user(), $subjectIds, 'quiz_sessions.subject_id', 'student_profiles.section');
    }

    /** Scoped sessions completed inside [$from, $to]. */
    private function sessionsBetween(array $subjectIds, Carbon $from, Carbon $to)
    {
        return $this->scopedSessions($subjectIds)->whereBetween('quiz_sessions.completed_at', [$from, $to]);
    }

    /**
     * The four headline cards. Each figure carries not just a delta but the
     * context a program head actually needs to decide something: a benchmark
     * distance, a per-subject average, or a week-over-week pace comparison.
     */
    private function headlineStats(array $subjectIds, $scope): array
    {
        // The period just before the selected one, same length, for every
        // "vs before" comparison on the cards.
        $days = (int) $this->from->diffInDays($this->to) + 1;
        $prevFrom = $this->from->copy()->subDays($days);
        $prevTo = $this->from->copy()->subSecond();

        $totalQuestions = (clone $this->scopedQuestions($subjectIds))->count();
        $added = fn (Carbon $from, Carbon $to) => (clone $this->scopedQuestions($subjectIds))
            ->whereBetween('questions.created_at', [$from, $to])->count();
        $addedInRange = $added($this->from, $this->to);
        $addedBefore = $added($prevFrom, $prevTo);

        // Active students: distinct students with graded activity in the range.
        $active = fn (Carbon $from, Carbon $to) => $this->sessionsBetween($subjectIds, $from, $to)
            ->distinct()->count('quiz_sessions.student_id');
        $activeStudents = $active($this->from, $this->to);
        $activeBefore = $active($prevFrom, $prevTo);

        // "New": students whose earliest graded session in scope falls inside the range.
        $firsts = $this->scopedSessions($subjectIds)
            ->select('quiz_sessions.student_id', DB::raw('MIN(quiz_sessions.completed_at) as first_seen'))
            ->groupBy('quiz_sessions.student_id');
        $newInRange = DB::query()
            ->fromSub($firsts, 'firsts')
            ->whereBetween('first_seen', [$this->from, $this->to])
            ->count();

        $avgScore = $this->avgScore($subjectIds, $this->from, $this->to);
        $avgPrev  = $this->avgScore($subjectIds, $prevFrom, $prevTo);

        $engagementDelta = $activeStudents - $activeBefore;

        return [
            'total_questions'      => $totalQuestions,
            'questions_per_subject'=> (int) round($totalQuestions / max(1, $scope->count())),
            'added_in_range'       => $addedInRange,
            'added_before'         => $addedBefore,
            'weekly_pace'          => round($addedInRange / max(1, $days / 7), 1),
            'active_students'      => $activeStudents,
            'new_in_range'         => $newInRange,
            'engagement_delta'     => $engagementDelta,
            'engagement_delta_pct' => $activeBefore > 0 ? (int) round($engagementDelta / $activeBefore * 100) : null,
            'avg_score'            => $avgScore,
            'avg_delta'            => ($avgScore !== null && $avgPrev !== null) ? $avgScore - $avgPrev : null,
            'benchmark_gap'        => $avgScore === null ? null : $avgScore - self::READINESS_BENCHMARK,
        ];
    }

    /**
     * Every active student this faculty member has visibility into, per
     * FacultySectionScope's rules - unrestricted for a subject means every
     * student counts for it, which (since one assigned subject is enough)
     * makes the whole active roster count. Used so studentBandCounts() can
     * report students who haven't attempted anything yet as "not yet
     * measurable" instead of silently excluding them, the same way
     * ChairAnalyticsService::studentReadinessRows() starts from every
     * student account rather than only ones with existing activity.
     */
    private function assignedStudentRoster(array $subjectIds): \Illuminate\Support\Collection
    {
        $faculty = Auth::user();
        $allowedSections = [];
        foreach ($subjectIds as $subjectId) {
            $names = $faculty->sectionNamesForSubject((int) $subjectId);
            if ($names === null) {
                $allowedSections = null; // unrestricted for at least one subject -> whole roster
                break;
            }
            $allowedSections = array_merge($allowedSections ?? [], $names);
        }

        $query = DB::table('users')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->when($this->section, fn ($q) => $q->where('student_profiles.section', $this->section));

        if ($allowedSections !== null) {
            $query->whereIn('student_profiles.section', array_unique($allowedSections));
        }

        return $query->pluck('users.id');
    }

    /**
     * Students bucketed by average completed-session score, scored across
     * every student in the faculty's roster (min. sample so a single lucky/
     * unlucky quiz can't misclassify anyone) — feeds the "students needing
     * attention" insight and readiness split. A student with no activity yet
     * counts toward the roster but not toward any band, same as the chair's
     * Readiness Bands.
     */
    private function studentBandCounts(array $subjectIds): array
    {
        // Cumulative as of the range end, like the chair's readiness: a
        // student's standing is built from everything they have practised.
        $activity = $this->scopedSessions($subjectIds)
            ->where('quiz_sessions.completed_at', '<=', $this->to)
            ->join('users', 'users.id', '=', 'quiz_sessions.student_id')
            ->where('users.role_id', Role::STUDENT)
            ->groupBy('users.id')
            ->select(
                'users.id',
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct'),
                DB::raw('COUNT(DISTINCT quiz_sessions.subject_id) as subjects')
            )
            ->get()
            ->keyBy('id');

        $roster = $this->assignedStudentRoster($subjectIds);
        // A faculty scoped to fewer subjects than READY_SUBJECTS could never
        // produce a Ready student otherwise - cap the requirement to however
        // many subjects are actually in scope, same reasoning as
        // ChairAnalyticsService skipping the subject-count check when
        // already filtered to one subject.
        $requiredSubjects = min(self::READY_SUBJECTS, count($subjectIds));

        $measured = $ready = $developing = $atRisk = 0;
        foreach ($roster as $studentId) {
            $row = $activity->get($studentId);
            $attempted = (int) ($row->attempted ?? 0);
            if ($attempted < self::MEASURED_MIN_ATTEMPTS) {
                continue; // not yet measurable
            }
            $measured++;
            $score = (int) round((int) $row->correct / $attempted * 100);
            $isReady = $score >= self::READINESS_BENCHMARK
                && $attempted >= self::READY_ATTEMPTS
                && (int) $row->subjects >= $requiredSubjects;

            if ($isReady) {
                $ready++;
            } elseif ($score < self::AT_RISK_THRESHOLD) {
                $atRisk++;
            } else {
                $developing++;
            }
        }

        return [
            'measured'    => $measured,
            'total_active'=> $roster->count(),
            'ready'       => $ready,
            'at_risk'     => $atRisk,
            'developing'  => $developing,
        ];
    }

    /**
     * Short, data-driven narrative cards — the "so what" a program head would
     * otherwise have to work out themselves by staring at the charts.
     */
    private function buildInsights($bySubject, array $byType, array $byDifficulty, array $stats, array $studentBand): array
    {
        $insights = [];

        // Engagement momentum, against the equally long period before.
        if ($stats['engagement_delta'] > 0) {
            $insights[] = [
                'target' => 'engagement', 'tone' => 'good', 'icon' => 'fa-arrow-trend-up',
                'title' => 'Engagement is climbing',
                'text' => "Active students are up {$stats['engagement_delta']}" . ($stats['engagement_delta_pct'] !== null ? " ({$stats['engagement_delta_pct']}%)" : '') . ' compared with the period before — momentum is on your side.',
            ];
        } elseif ($stats['engagement_delta'] < 0) {
            $insights[] = [
                'target' => 'engagement', 'tone' => 'warn', 'icon' => 'fa-arrow-trend-down',
                'title' => 'Engagement is slipping',
                'text' => 'Active students fell by ' . abs($stats['engagement_delta']) . ' compared with the period before. Consider assigning a class quiz to re-engage the section.',
            ];
        } elseif ($stats['active_students'] > 0) {
            $insights[] = [
                'target' => 'engagement', 'tone' => 'info', 'icon' => 'fa-minus',
                'title' => 'Engagement is flat',
                'text' => 'Active student count is unchanged from the period before.',
            ];
        }

        // Accuracy vs. the board-readiness benchmark.
        if ($stats['avg_score'] !== null) {
            if ($stats['benchmark_gap'] >= 0) {
                $insights[] = [
                    'target' => 'accuracy', 'tone' => 'good', 'icon' => 'fa-check-circle',
                    'title' => 'Above the readiness benchmark',
                    'text' => "Average accuracy ({$stats['avg_score']}%) is {$stats['benchmark_gap']} pts above the " . self::READINESS_BENCHMARK . "% board-readiness benchmark.",
                ];
            } else {
                $insights[] = [
                    'target' => 'accuracy', 'tone' => 'crit', 'icon' => 'fa-triangle-exclamation',
                    'title' => 'Below the readiness benchmark',
                    'text' => "Average accuracy ({$stats['avg_score']}%) is " . abs($stats['benchmark_gap']) . ' pts below the ' . self::READINESS_BENCHMARK . '% benchmark — review the weakest topics before the next mock exam.',
                ];
            }
        }

        // Students needing intervention.
        if ($studentBand['at_risk'] > 0) {
            $insights[] = [
                'target' => 'readiness', 'tone' => 'crit', 'icon' => 'fa-user-clock',
                'title' => $studentBand['at_risk'] . ' student' . ($studentBand['at_risk'] === 1 ? '' : 's') . ' at risk',
                'text' => 'Averaging below ' . self::AT_RISK_THRESHOLD . '% with enough attempts to be measured — worth a direct check-in or remedial material.',
            ];
        }

        // Difficulty mix — is the bank challenging enough?
        if ($byDifficulty['hard']['count'] + $byDifficulty['medium']['count'] + $byDifficulty['easy']['count'] > 0) {
            if ($byDifficulty['hard']['pct'] < 15) {
                $insights[] = [
                    'target' => 'difficulty', 'tone' => 'warn', 'icon' => 'fa-layer-group',
                    'title' => 'Test bank is Easy-heavy',
                    'text' => "Only {$byDifficulty['hard']['pct']}% of questions are Hard difficulty — top students may not be getting stretched. Consider adding harder items.",
                ];
            } elseif ($byDifficulty['easy']['pct'] < 15) {
                $insights[] = [
                    'target' => 'difficulty', 'tone' => 'info', 'icon' => 'fa-layer-group',
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
                    'target' => 'subjects', 'tone' => 'warn', 'icon' => 'fa-database',
                    'title' => "{$thin['code']} needs more questions",
                    'text' => "{$thin['code']} has only {$thin['total']} question" . ($thin['total'] === 1 ? '' : 's') . ", well below your {$avgPerSubject}-question average per subject.",
                ];
            }
        }

        // Content pace, against the equally long period before.
        if ($stats['added_before'] > 0 && $stats['added_in_range'] < $stats['added_before'] * 0.5) {
            $insights[] = [
                'target' => 'subjects', 'tone' => 'info', 'icon' => 'fa-gauge',
                'title' => 'Slower content pace',
                'text' => "{$stats['added_in_range']} question" . ($stats['added_in_range'] === 1 ? '' : 's') . " added in this period vs. {$stats['added_before']} in the period before.",
            ];
        }

        return $insights;
    }

    /**
     * Average completed-session score (%) in scope over an optional window.
     */
    private function avgScore(array $subjectIds, Carbon $from, Carbon $to): ?int
    {
        $agg = $this->sessionsBetween($subjectIds, $from, $to)
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
        $sessions = $this->sessionsBetween($subjectIds, $this->from, $this->to)
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

            // Icon / tone driven by the achieved score — steps of the page's
            // maroon scale (resources/views/faculty/dashboard.blade.php --m-*).
            if ($score === null) {
                $tone = ['bg' => '#f4f4f5', 'fg' => '#9a9a9a', 'icon' => 'fa-hourglass-half'];
            } elseif ($score < 50) {
                $tone = ['bg' => '#eec9c9', 'fg' => '#5f1515', 'icon' => 'fa-exclamation-circle'];
            } elseif ($score < 75) {
                $tone = ['bg' => '#f7e6e6', 'fg' => '#7B1D1D', 'icon' => 'fa-brain'];
            } else {
                $tone = ['bg' => '#7B1D1D', 'fg' => '#ffffff', 'icon' => 'fa-check-circle'];
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
        $counts = CurriculumScope::restrictToActive(DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
            ->whereIn('topics.subject_id', $assigned->pluck('id')->all())
            ->groupBy('topics.subject_id')
            ->select('topics.subject_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'subject_id');

        $max = max(1, (int) ($counts->max() ?? 0));

        return $assigned->map(fn ($s) => [
            'code'  => $s->code,
            'total' => (int) ($counts[$s->id] ?? 0),
            'width' => (int) round(((int) ($counts[$s->id] ?? 0)) / $max * 100),
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
     * Top students by average completed-session score within the range (min.
     * sample so a single lucky quiz can't top the board).
     */
    private function topStudents(array $subjectIds)
    {
        $agg = $this->sessionsBetween($subjectIds, $this->from, $this->to)
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
