<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use App\Support\PeriodBuckets;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chart data for the Program Chair dashboard's four tabs. Every tab answers to
 * the same date range / subject / section filter bar, so one call returns the
 * whole dashboard and a filter change redraws every tab together.
 *
 * Where a filter cannot apply to a figure it is ignored on purpose, and the
 * card says so: test-bank coverage has no date or section, and at-risk status
 * is a "right now" judgement, so the date range does not move it.
 */
class ChairDashboardService
{
    /** Days without a login or quiz before a student is flagged inactive. */
    public const INACTIVITY_DAYS = 7;

    /** Tabs the dashboard can ask for, keyed as they appear in the response. */
    public const TABS = ['overview', 'performance', 'faculty', 'at_risk'];

    /** At-risk "reason" filter values → the reason labels on each student. */
    public const REASONS = [
        'low' => 'Low readiness',
        'inactive' => 'Inactive',
        'none' => 'No learning activity',
    ];

    public function __construct(private ChairAnalyticsService $analytics) {}

    /**
     * @param  string|null  $priority  At-risk tab only: 'high' or 'watch'.
     * @param  string|null  $reason  At-risk tab only: a key of REASONS.
     * @param  array|null  $tabs  Limit the work to these TABS (null = all).
     */
    public function charts(Carbon $from, Carbon $to, ?int $subjectId = null, ?string $section = null,
        ?string $priority = null, ?string $reason = null, ?array $tabs = null): array
    {
        $tabs ??= self::TABS;
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $days = (int) $from->diffInDays($to) + 1;

        // Subject-scoped, all sections: the per-section comparisons need every
        // section even while one is selected. $scoped narrows to that section.
        $sessions = array_intersect($tabs, ['overview', 'performance'])
            ? $this->sessionsUpTo($to, $subjectId)
            : collect();
        $scoped = $section === null ? $sessions : $sessions->where('section', $section)->values();

        $subjects = $this->subjectRows($subjectId);
        $buckets = PeriodBuckets::for($from, $to);

        $builders = [
            'overview' => fn () => $this->overview($scoped, $subjects, $buckets, $from, $to, $days, $subjectId, $section),
            'performance' => fn () => $this->performance($sessions, $scoped, $subjects, $buckets, $from, $to, $subjectId, $section),
            'faculty' => fn () => $this->faculty($buckets, $from, $to, $subjectId, $section),
            'at_risk' => fn () => $this->atRiskBreakdown($subjectId, $section, $priority, $reason),
        ];

        $result = ['range' => [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'days' => $days,
            'bucket' => $buckets->first()['unit'] ?? 'day',
        ]];
        foreach (self::TABS as $tab) {
            if (in_array($tab, $tabs, true)) {
                $result[$tab] = $builders[$tab]();
            }
        }

        return $result;
    }

    // ── Class-Level Performance page ───────────────────────────────────

    /**
     * Everything the Class-Level Performance page shows, for one date range /
     * subject / section. It builds on the dashboard's Performance tab and adds
     * the headline figures, difficulty calibration and topic strengths/gaps.
     */
    public function classPerformance(Carbon $from, Carbon $to, ?int $subjectId = null, ?string $section = null): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $days = (int) $from->diffInDays($to) + 1;

        $sessions = $this->sessionsUpTo($to, $subjectId);
        $scoped = $section === null ? $sessions : $sessions->where('section', $section)->values();
        $buckets = PeriodBuckets::for($from, $to);
        $subjects = $this->subjectRows($subjectId);
        $performance = $this->performance($sessions, $scoped, $subjects, $buckets, $from, $to, $subjectId, $section);

        $inRange = $this->between($scoped, $from, $to);
        $accuracy = $this->accuracyOf($inRange);
        $previousAccuracy = $this->accuracyOf($this->between($scoped, $from->copy()->subDays($days), $from->copy()->subSecond()));
        $bands = $this->readinessBands($scoped, (bool) $subjectId);
        $enrolled = $this->activeStudents($section)->count();
        $participating = $inRange->pluck('student_id')->unique()->count();
        $coverage = $enrolled ? (int) round($bands['eligible'] / $enrolled * 100) : 0;

        $answers = $this->answersBetween($from, $to, $subjectId, $section);
        $topics = $this->topicAccuracy($answers);
        $weakLine = WeaknessDetector::ACCURACY_THRESHOLD * 100;
        $strongLine = WeaknessDetector::STRENGTH_THRESHOLD * 100;

        return array_merge($performance, [
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'days' => $days,
                'bucket' => $buckets->first()['unit'] ?? 'day',
            ],
            'kpis' => [
                'accuracy' => $accuracy,
                'accuracy_change' => $accuracy !== null && $previousAccuracy !== null ? $accuracy - $previousAccuracy : null,
                'participating' => $participating,
                'enrolled' => $enrolled,
                'items' => (int) $inRange->sum('items'),
                'quizzes' => $this->quizCount($inRange),
                'readiness_rate' => $bands['readiness_rate'],
                'pass_projection' => $bands['pass_projection'],
                'ready' => $bands['ready'],
                'developing' => $bands['developing'],
                'at_risk' => $bands['at_risk'],
                'eligible' => $bands['eligible'],
                'coverage' => $coverage,
                // Same conservative read as ChairAnalyticsService: a small
                // measured pool swings on one or two students.
                'confidence' => match (true) {
                    $bands['eligible'] < 5 => 'low',
                    $coverage >= 50 => 'high',
                    $coverage >= 25 => 'medium',
                    default => 'low',
                },
            ],
            'leaderboard' => $this->leaderboard($inRange, $subjects),
            // Column order for the per-subject standings on the leaderboard.
            'leaderboard_subjects' => $subjects->pluck('code')->values(),
            'difficulty' => $this->difficultyAccuracy($answers),
            'weak_topics' => $topics->filter(fn ($t) => $t['rate'] < $weakLine)->sortBy('accuracy')->take(10)->values(),
            'strong_topics' => $topics->filter(fn ($t) => $t['rate'] >= $strongLine)->sortByDesc('accuracy')->take(10)->values(),
        ]);
    }

    /**
     * Students ranked within the filtered sessions. The default order —
     * correct answers — is the same rule as the students' own leaderboard
     * (AchievementService::ranked), so the chair and the students see the same
     * standings; the page can re-sort the same rows by accuracy. Every ranked
     * student is returned (not a top-N) so that re-sort is never missing
     * someone who ranks low on volume but high on accuracy.
     */
    private function leaderboard(Collection $sessions, Collection $subjects): Collection
    {
        $students = User::whereIn('id', $sessions->pluck('student_id')->unique()->all())
            ->where('role_id', Role::STUDENT)
            ->where('is_active', true)
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->keyBy('id');

        // Each student's standing inside every subject, by the same rule as
        // the overall board: correct answers, ties to the earlier account.
        $standings = [];
        foreach ($subjects as $subject) {
            $ranked = $sessions->where('subject_id', (int) $subject->id)
                ->groupBy('student_id')
                ->map(fn (Collection $rows, $id) => [
                    'id' => (int) $id,
                    'correct' => (int) $rows->sum('correct'),
                    'items' => (int) $rows->sum('items'),
                ])
                ->filter(fn ($r) => $r['correct'] > 0 && $students->has($r['id']))
                ->sortBy([['correct', 'desc'], ['id', 'asc']])
                ->values();

            foreach ($ranked as $i => $r) {
                $standings[$r['id']][$subject->code] = [
                    'rank' => $i + 1,
                    'of' => $ranked->count(),
                    'correct' => $r['correct'],
                    'accuracy' => $r['items'] ? (int) round($r['correct'] / $r['items'] * 100) : null,
                ];
            }
        }

        $rows = $sessions->groupBy('student_id')->map(function (Collection $rows, $studentId) {
            $items = (int) $rows->sum('items');
            $correct = (int) $rows->sum('correct');

            return [
                'id' => (int) $studentId,
                'section' => $rows->first()->section,
                'correct' => $correct,
                'items' => $items,
                'quizzes' => $this->quizCount($rows),
                'subjects' => $rows->pluck('subject_id')->filter()->unique()->count(),
                'accuracy' => $items ? (int) round($correct / $items * 100) : null,
                'last_at' => $rows->max('at'),
            ];
        })->filter(fn ($row) => $row['correct'] > 0);

        return $rows
            ->filter(fn ($row) => $students->has($row['id']))
            ->sortBy([['correct', 'desc'], ['id', 'asc']])
            ->map(function ($row) use ($students, $standings) {
                $student = $students->get($row['id']);

                return array_merge($row, [
                    // Keyed by subject code; an object in JSON even when empty.
                    'standings' => (object) ($standings[$row['id']] ?? []),
                    'name' => trim("{$student->first_name} {$student->last_name}"),
                    'email' => $student->email,
                    'initials' => strtoupper(substr((string) $student->first_name, 0, 1).substr((string) $student->last_name, 0, 1)),
                    'last_active' => Carbon::createFromTimestamp($row['last_at'])->diffForHumans(),
                    'url' => route('chair.students.show', $row['id']),
                ]);
            })
            ->values();
    }

    /**
     * Answer-level rows (one per topic × difficulty) from quizzes completed in
     * the range — the only source with both a date and a topic/difficulty.
     */
    private function answersBetween(Carbon $from, Carbon $to, ?int $subjectId, ?string $section): Collection
    {
        return DB::table('quiz_answers')
            ->join('quiz_sessions', 'quiz_sessions.id', '=', 'quiz_answers.session_id')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->whereNotNull('quiz_answers.is_correct')
            ->whereNotNull('quiz_sessions.completed_at')
            ->where('quiz_sessions.session_type', '!=', 'training')->where('quiz_sessions.is_practice_room', false)
            ->whereBetween('quiz_sessions.completed_at', [$from, $to])
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->when($section !== null, fn ($query) => $query
                ->join('student_profiles', 'student_profiles.user_id', '=', 'quiz_sessions.student_id')
                ->where('student_profiles.section', $section))
            ->select('topics.id as topic_id', 'topics.name as topic', 'subjects.code as subject_code',
                'questions.difficulty', 'quiz_sessions.student_id', 'quiz_answers.is_correct')
            ->get();
    }

    /** Class accuracy by authored difficulty — a calibration check on the bank. */
    private function difficultyAccuracy(Collection $answers): Collection
    {
        $levels = ['easy' => 'Easy', 'moderate' => 'Moderate', 'difficult' => 'Difficult'];
        $grouped = $answers->groupBy(fn ($a) => match (strtolower(trim((string) $a->difficulty))) {
            'easy' => 'easy',
            'medium', 'moderate' => 'moderate',
            'hard', 'difficult' => 'difficult',
            default => 'other',
        });

        return collect($levels)->map(function ($label, $key) use ($grouped) {
            $rows = $grouped->get($key, collect());

            return [
                'label' => $label,
                'answered' => $rows->count(),
                'accuracy' => $rows->count() ? (int) round($rows->sum('is_correct') / $rows->count() * 100) : null,
            ];
        })->values();
    }

    /** Pooled accuracy per topic, for topics with enough answers to be reportable. */
    private function topicAccuracy(Collection $answers): Collection
    {
        return $answers->groupBy('topic_id')
            ->filter(fn ($rows) => $rows->count() >= ChairAnalyticsService::TOPIC_MIN_ATTEMPTS)
            ->map(function ($rows) {
                $rate = $rows->sum('is_correct') / $rows->count() * 100;

                return [
                    'name' => $rows->first()->topic,
                    'subject_code' => $rows->first()->subject_code,
                    'attempts' => $rows->count(),
                    'students' => $rows->pluck('student_id')->unique()->count(),
                    'accuracy' => (int) round($rate),
                    // Unrounded, so the 60% / 75% lines are applied exactly.
                    'rate' => $rate,
                ];
            })
            ->values();
    }

    // ── Overview ───────────────────────────────────────────────────────

    /**
     * Accuracy is activity *inside* the range. Readiness and pass projection
     * are cumulative *as of* the range end (a student's standing is built from
     * everything they have practised), and their change is measured against
     * where they stood when the range began.
     */
    private function overview(Collection $sessions, Collection $subjects, Collection $buckets, Carbon $from, Carbon $to, int $days, ?int $subjectId, ?string $section): array
    {
        $inRange = $this->between($sessions, $from, $to);
        $previous = $this->between($sessions, $from->copy()->subDays($days), $from->copy()->subSecond());

        $accuracy = $this->accuracyOf($inRange);
        $previousAccuracy = $this->accuracyOf($previous);
        $weakest = $subjects
            ->map(fn ($subject) => [
                'code' => $subject->code,
                'accuracy' => $this->accuracyOf($inRange->where('subject_id', (int) $subject->id)),
            ])
            ->filter(fn ($row) => $row['accuracy'] !== null)
            ->sortBy('accuracy')
            ->first();

        $now = $this->readinessBands($sessions, (bool) $subjectId);
        $then = $this->readinessBands($this->upTo($sessions, $from->copy()->subSecond()), (bool) $subjectId);

        $thin = $this->analytics->coverageReport($subjectId)->whereIn('status', ['critical', 'thin']);

        return [
            'accuracy' => [
                'value' => $accuracy,
                'change' => $accuracy !== null && $previousAccuracy !== null ? $accuracy - $previousAccuracy : null,
                'weakest' => $weakest,
                'trend' => $buckets->map(fn ($bucket) => [
                    'label' => $bucket['label'],
                    'accuracy' => $this->accuracyOf($this->between($sessions, $bucket['start'], $bucket['end'])),
                ])->values(),
            ],
            'readiness' => [
                'value' => $now['readiness_rate'],
                'change' => $now['readiness_rate'] !== null && $then['readiness_rate'] !== null
                    ? $now['readiness_rate'] - $then['readiness_rate']
                    : null,
                'eligible' => $now['eligible'],
                'ready' => $now['ready'],
                // Active students without the 20 items needed to be measured —
                // shown so a 0% reads as "few measured", not "no data".
                'not_measured' => max(0, $this->activeStudents($section)->count() - $now['eligible']),
                'by_subject' => $subjects->map(function ($subject) use ($sessions) {
                    $bands = $this->readinessBands($sessions->where('subject_id', (int) $subject->id), true);

                    return ['code' => $subject->code, 'rate' => $bands['readiness_rate'], 'eligible' => $bands['eligible']];
                })->values(),
            ],
            'thin' => [
                'value' => $thin->count(),
                'by_subject' => $subjects->map(fn ($subject) => [
                    'code' => $subject->code,
                    'topics' => $thin->where('subject_id', (int) $subject->id)->count(),
                ])->values(),
            ],
            'projection' => [
                'value' => $now['pass_projection'],
                'eligible' => $now['eligible'],
                'trend' => $buckets->map(function ($bucket) use ($sessions, $subjectId) {
                    $bands = $this->readinessBands($this->upTo($sessions, $bucket['end']), (bool) $subjectId);

                    return ['label' => $bucket['label'], 'projection' => $bands['pass_projection'], 'eligible' => $bands['eligible']];
                })->values(),
            ],
        ];
    }

    // ── Performance ────────────────────────────────────────────────────

    private function performance(Collection $allSections, Collection $sessions, Collection $subjects, Collection $buckets, Carbon $from, Carbon $to, ?int $subjectId, ?string $section): array
    {
        $inRange = $this->between($sessions, $from, $to);
        $bands = $this->readinessBands($sessions, (bool) $subjectId);
        $activeStudents = $this->activeStudents($section)->count();

        $distribution = collect([[0, 49], [50, 59], [60, 69], [70, 79], [80, 89], [90, 100]])
            ->map(fn ($band) => ['label' => "{$band[0]}–{$band[1]}%", 'min' => $band[0], 'max' => $band[1]]);
        $measured = $this->perStudent($sessions)->filter(fn ($s) => $s['attempts'] >= ChairAnalyticsService::DEVELOPING_ATTEMPTS);

        $allInRange = $this->between($allSections, $from, $to);

        return [
            // Readiness is a standing, so it stays cumulative to each period
            // end. Class accuracy is measured per period (only that period's
            // quizzes) so the line shows whether the class is improving now,
            // instead of a running average that older activity keeps flat.
            'trend' => $buckets->map(function ($bucket) use ($sessions, $subjectId) {
                $bands = $this->readinessBands($this->upTo($sessions, $bucket['end']), (bool) $subjectId);
                $inPeriod = $this->between($sessions, $bucket['start'], $bucket['end']);

                return [
                    'label' => $bucket['label'],
                    'readiness' => $bands['readiness_rate'],
                    'accuracy' => $this->accuracyOf($inPeriod),
                    'answered' => (int) $inPeriod->sum('items'),
                    'eligible' => $bands['eligible'],
                    'ready' => $bands['ready'],
                ];
            })->values(),
            'bands' => [
                'ready' => $bands['ready'],
                'developing' => $bands['developing'],
                'at_risk' => $bands['at_risk'],
                'eligible' => $bands['eligible'],
                'insufficient' => max(0, $activeStudents - $bands['eligible']),
            ],
            'by_subject' => $subjects->map(function ($subject) use ($inRange) {
                $rows = $inRange->where('subject_id', (int) $subject->id);

                $accuracy = $this->accuracyOf($rows);
                $threshold = (int) ($subject->passing_threshold ?? 75);

                return [
                    'code' => $subject->code,
                    'name' => $subject->name,
                    'accuracy' => $accuracy,
                    'threshold' => $threshold,
                    // Distance to the subject's own passing mark: the number the chair acts on.
                    'gap' => $accuracy === null ? null : $accuracy - $threshold,
                    'items' => (int) $rows->sum('items'),
                    'students' => $rows->pluck('student_id')->unique()->count(),
                ];
            })->values(),
            'by_section' => Section::where('is_active', true)->orderBy('year_level')->orderBy('name')->pluck('name')
                ->map(function ($name) use ($allInRange) {
                    $rows = $allInRange->where('section', $name);

                    return [
                        'section' => $name,
                        'accuracy' => $this->accuracyOf($rows),
                        'students' => $rows->pluck('student_id')->unique()->count(),
                        'items' => (int) $rows->sum('items'),
                    ];
                })->values(),
            'engagement' => $buckets->map(function ($bucket) use ($sessions) {
                $rows = $this->between($sessions, $bucket['start'], $bucket['end']);

                return [
                    'label' => $bucket['label'],
                    'students' => $rows->pluck('student_id')->unique()->count(),
                    'quizzes' => $this->quizCount($rows),
                    'items' => (int) $rows->sum('items'),
                    'accuracy' => $this->accuracyOf($rows),
                    'hours' => round($rows->sum('secs') / 3600, 1),
                ];
            })->values(),
            'distribution' => $distribution->map(fn ($band) => [
                'label' => $band['label'],
                'students' => $measured->filter(fn ($s) => $s['accuracy'] >= $band['min'] && $s['accuracy'] <= $band['max'])->count(),
            ])->values(),
        ];
    }

    // ── Faculty ────────────────────────────────────────────────────────

    /**
     * Subject narrows to faculty assigned that subject; section to faculty
     * handling that section. The date range applies to authored questions —
     * the one faculty activity with a timestamp worth charting.
     */
    private function faculty(Collection $buckets, Carbon $from, Carbon $to, ?int $subjectId, ?string $section): array
    {
        $workload = $this->analytics->facultyWorkload();

        $ids = $workload->pluck('id');
        if ($subjectId) {
            $ids = $ids->intersect(DB::table('faculty_subjects')->where('subject_id', $subjectId)->pluck('faculty_id'));
        }
        if ($section !== null) {
            $ids = $ids->intersect(DB::table('faculty_subject_sections')
                ->join('sections', 'sections.id', '=', 'faculty_subject_sections.section_id')
                ->where('sections.name', $section)
                ->when($subjectId, fn ($query) => $query->where('faculty_subject_sections.subject_id', $subjectId))
                ->pluck('faculty_subject_sections.faculty_id'));
        }
        $ids = $ids->map(fn ($id) => (int) $id)->unique()->values();
        $workload = $workload->whereIn('id', $ids->all())->values();

        $questions = DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereIn('questions.created_by', $ids->all())
            ->whereBetween('questions.created_at', [$from, $to])
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->select('questions.created_by', 'questions.created_at')
            ->get()
            ->map(fn ($q) => (object) ['by' => (int) $q->created_by, 'at' => Carbon::parse($q->created_at)->getTimestamp()]);

        $perSubject = DB::table('subjects')
            ->when($subjectId, fn ($query) => $query->where('id', $subjectId))
            ->orderBy('id')
            ->get(['id', 'code'])
            ->map(function ($subject) use ($section) {
                $query = $section === null
                    ? DB::table('faculty_subjects')->where('subject_id', $subject->id)
                    : DB::table('faculty_subject_sections')
                        ->join('sections', 'sections.id', '=', 'faculty_subject_sections.section_id')
                        ->where('sections.name', $section)
                        ->where('faculty_subject_sections.subject_id', $subject->id);

                return ['code' => $subject->code, 'faculty' => $query->distinct()->count('faculty_id')];
            })->values();

        return [
            'ids' => $ids,
            'count' => $ids->count(),
            'questions_total' => $questions->count(),
            'questions_trend' => $buckets->map(fn ($bucket) => [
                'label' => $bucket['label'],
                'added' => $questions->filter(fn ($q) => $q->at >= $bucket['start']->getTimestamp() && $q->at <= $bucket['end']->getTimestamp())->count(),
            ])->values(),
            'contributors' => $workload
                ->map(fn ($f) => ['name' => $f['name'], 'added' => $questions->where('by', $f['id'])->count()])
                ->sortByDesc('added')
                ->take(8)
                ->values(),
            'status' => collect(['ok' => 'Active', 'overloaded' => 'Heavy load (3+ subjects)', 'idle' => 'Not logged in for 30+ days', 'unassigned' => 'No subjects yet'])
                ->map(fn ($label, $flag) => ['flag' => $flag, 'label' => $label, 'count' => $workload->where('flag', $flag)->count()])
                ->values(),
            'per_subject' => $perSubject,
        ];
    }

    // ── At-risk ────────────────────────────────────────────────────────

    /**
     * System-wide intervention list. The readiness rule matches the faculty
     * performance page; inactivity is based on the latest quiz/login activity.
     * With a subject selected, readiness is judged on that subject's quizzes
     * only, while inactivity stays platform-wide.
     */
    public function atRiskStudents(?int $subjectId = null, ?string $section = null): Collection
    {
        // For one subject, read the per-subject slices so answers given in
        // mixed quizzes count toward that subject's readiness too.
        $quizActivity = $subjectId
            ? $this->sessionsUpTo(now()->endOfDay(), $subjectId)
                ->groupBy('student_id')
                ->map(fn (Collection $rows) => (object) [
                    'attempted' => (int) $rows->sum('items'),
                    'correct' => (int) $rows->sum('correct'),
                    'quizzes' => $this->quizCount($rows),
                    'last_quiz_at' => null,
                ])
            : DB::table('quiz_sessions')
                ->where('session_type', '!=', 'training')->where('is_practice_room', false)
                ->whereNotNull('completed_at')
                ->groupBy('student_id')
                ->select(
                    'student_id',
                    DB::raw('COALESCE(SUM(total_items), 0) as attempted'),
                    DB::raw('COALESCE(SUM(correct_answers), 0) as correct'),
                    DB::raw('COUNT(*) as quizzes'),
                    DB::raw('MAX(completed_at) as last_quiz_at')
                )
                ->get()
                ->keyBy('student_id');

        $lastQuizAnywhere = $subjectId
            ? DB::table('quiz_sessions')->whereNotNull('completed_at')
                ->groupBy('student_id')->select('student_id', DB::raw('MAX(completed_at) as at'))
                ->pluck('at', 'student_id')
            : null;

        return $this->activeStudents($section)
            ->map(function ($student) use ($quizActivity, $lastQuizAnywhere) {
                $activity = $quizActivity->get($student->id);
                $attempted = (int) ($activity->attempted ?? 0);
                $correct = (int) ($activity->correct ?? 0);
                $score = $attempted > 0 ? (int) round($correct / $attempted * 100) : null;

                $dates = collect([
                    $lastQuizAnywhere ? $lastQuizAnywhere->get($student->id) : $activity?->last_quiz_at,
                    $student->last_login_at,
                    $student->created_at,
                ])->filter()->map(fn ($date) => Carbon::parse($date));

                $lastActive = $dates->sortDesc()->first();
                $daysIdle = $lastActive ? (int) $lastActive->diffInDays(now()) : self::INACTIVITY_DAYS;
                $lowReadiness = $attempted >= WeaknessDetector::MIN_ATTEMPTS
                    && $score < (int) (WeaknessDetector::ACCURACY_THRESHOLD * 100);
                $inactive = $daysIdle >= self::INACTIVITY_DAYS;

                if (! $lowReadiness && ! $inactive) {
                    return null;
                }

                $reasons = [];
                if ($lowReadiness) {
                    $reasons[] = 'Low readiness';
                }
                if ($inactive) {
                    $reasons[] = $attempted === 0 ? 'No learning activity' : 'Inactive';
                }

                $isHigh = ($lowReadiness && $inactive) || ($score !== null && $score < 45) || $daysIdle >= 14;

                return [
                    'id' => (int) $student->id,
                    'name' => trim("{$student->first_name} {$student->last_name}"),
                    'email' => $student->email,
                    'section' => $student->section,
                    'initials' => strtoupper(substr((string) $student->first_name, 0, 1).substr((string) $student->last_name, 0, 1)),
                    'score' => $score,
                    'attempted' => $attempted,
                    'quizzes' => (int) ($activity->quizzes ?? 0),
                    'last_active' => $lastActive,
                    'days_idle' => $daysIdle,
                    'reasons' => $reasons,
                    'priority' => $isHigh ? 'high' : 'watch',
                ];
            })
            ->filter()
            ->sort(function (array $a, array $b) {
                $aRank = [$a['priority'] === 'high' ? 0 : 1, $a['score'] ?? -1, -$a['days_idle']];
                $bRank = [$b['priority'] === 'high' ? 0 : 1, $b['score'] ?? -1, -$b['days_idle']];

                return $aRank <=> $bRank;
            })
            ->values();
    }

    /** At-risk list plus the breakdowns the At-Risk tab charts. */
    private function atRiskBreakdown(?int $subjectId, ?string $section, ?string $priority = null, ?string $reason = null): array
    {
        // Computed across every section so the per-section chart stays whole;
        // priority and reason narrow everything, section only the rest.
        $all = $this->atRiskStudents($subjectId)
            ->when($priority, fn ($rows) => $rows->where('priority', $priority))
            ->when($reason, fn ($rows) => $rows->filter(fn ($s) => in_array(self::REASONS[$reason], $s['reasons'], true)))
            ->values();
        $students = $section === null ? $all : $all->where('section', $section)->values();

        $idleBands = [['0–6 days', 0, 6], ['7–13 days', 7, 13], ['14–29 days', 14, 29], ['30+ days', 30, PHP_INT_MAX]];

        return [
            'count' => $students->count(),
            'high' => $students->where('priority', 'high')->count(),
            'watch' => $students->where('priority', 'watch')->count(),
            'by_reason' => collect(['Low readiness', 'Inactive', 'No learning activity'])
                ->map(fn ($reason) => [
                    'reason' => $reason,
                    'students' => $students->filter(fn ($s) => in_array($reason, $s['reasons'], true))->count(),
                ])->values(),
            'by_section' => $all->groupBy(fn ($s) => $s['section'] ?: 'No section')
                ->map(fn ($group, $name) => [
                    'section' => $name,
                    'high' => $group->where('priority', 'high')->count(),
                    'watch' => $group->where('priority', 'watch')->count(),
                ])
                ->sortKeys()
                ->values(),
            'by_idle' => collect($idleBands)->map(fn ($band) => [
                'label' => $band[0],
                'students' => $students->filter(fn ($s) => $s['days_idle'] >= $band[1] && $s['days_idle'] <= $band[2])->count(),
            ])->values(),
            'students' => $students->map(fn ($s) => array_merge($s, [
                'last_active' => $s['last_active']?->diffForHumans(),
            ]))->values(),
        ];
    }

    // ── Shared helpers ─────────────────────────────────────────────────

    private function subjectRows(?int $subjectId): Collection
    {
        return DB::table('subjects')
            ->when($subjectId, fn ($query) => $query->where('id', $subjectId))
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'passing_threshold']);
    }

    /**
     * Completed, graded quiz sessions up to $to, each tagged with the student's
     * section — as per-subject slices.
     *
     * A mixed quiz (an all-subject test) has no subject of its own, but every
     * answer in it does. It is split into one slice per subject from its
     * answers, so a subject filter still counts that practice; before this, a
     * subject whose practice came mostly through mixed quizzes showed nothing
     * at all. Slices keep their quiz's id in 'session' so quizzes are counted
     * once, and only the first slice carries the quiz's duration. A mixed quiz
     * without answer rows (mock exams keep theirs elsewhere) stays whole and
     * is only counted when no subject is selected.
     */
    private function sessionsUpTo(Carbon $to, ?int $subjectId): Collection
    {
        $base = fn () => DB::table('quiz_sessions')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'quiz_sessions.student_id')
            ->whereNotNull('quiz_sessions.completed_at')
            ->where('quiz_sessions.session_type', '!=', 'training')->where('quiz_sessions.is_practice_room', false)
            ->where('quiz_sessions.completed_at', '<=', $to);

        $single = $base()
            ->whereNotNull('quiz_sessions.subject_id')
            ->when($subjectId, fn ($query) => $query->where('quiz_sessions.subject_id', $subjectId))
            ->select('quiz_sessions.id', 'quiz_sessions.student_id', 'quiz_sessions.subject_id',
                'quiz_sessions.total_items as items', 'quiz_sessions.correct_answers as correct',
                'quiz_sessions.duration_secs', 'quiz_sessions.completed_at', 'student_profiles.section')
            ->get();

        $slices = $base()
            ->whereNull('quiz_sessions.subject_id')
            ->join('quiz_answers', 'quiz_answers.session_id', '=', 'quiz_sessions.id')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereNotNull('quiz_answers.is_correct')
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->groupBy('quiz_sessions.id', 'quiz_sessions.student_id', 'topics.subject_id',
                'quiz_sessions.duration_secs', 'quiz_sessions.completed_at', 'student_profiles.section')
            ->select('quiz_sessions.id', 'quiz_sessions.student_id', 'topics.subject_id',
                DB::raw('COUNT(*) as items'), DB::raw('COALESCE(SUM(quiz_answers.is_correct), 0) as correct'),
                'quiz_sessions.duration_secs', 'quiz_sessions.completed_at', 'student_profiles.section')
            ->get();

        $unsplit = $subjectId ? collect() : $base()
            ->whereNull('quiz_sessions.subject_id')
            ->whereNotIn('quiz_sessions.id', $slices->pluck('id')->unique()->all())
            ->select('quiz_sessions.id', 'quiz_sessions.student_id', 'quiz_sessions.subject_id',
                'quiz_sessions.total_items as items', 'quiz_sessions.correct_answers as correct',
                'quiz_sessions.duration_secs', 'quiz_sessions.completed_at', 'student_profiles.section')
            ->get();

        $timed = [];

        return $single->concat($slices)->concat($unsplit)->map(function ($s) use (&$timed) {
            $firstSlice = ! isset($timed[$s->id]);
            $timed[$s->id] = true;

            return (object) [
                'session' => (int) $s->id,
                'student_id' => (int) $s->student_id,
                'subject_id' => $s->subject_id === null ? null : (int) $s->subject_id,
                'section' => $s->section,
                'items' => (int) $s->items,
                'correct' => (int) $s->correct,
                'secs' => $firstSlice ? (int) $s->duration_secs : 0,
                'at' => Carbon::parse($s->completed_at)->getTimestamp(),
            ];
        })->values();
    }

    /** Distinct quizzes behind a set of slices (a mixed quiz is several slices). */
    private function quizCount(Collection $sessions): int
    {
        return $sessions->pluck('session')->unique()->count();
    }

    private function activeStudents(?string $section): Collection
    {
        return User::query()
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->when($section !== null, fn ($query) => $query->where('student_profiles.section', $section))
            ->orderBy('users.first_name')
            ->get(['users.id', 'users.first_name', 'users.last_name', 'users.email',
                'users.last_login_at', 'users.created_at', 'student_profiles.section']);
    }

    private function between(Collection $sessions, Carbon $start, Carbon $end): Collection
    {
        $a = $start->getTimestamp();
        $b = $end->getTimestamp();

        return $sessions->filter(fn ($s) => $s->at >= $a && $s->at <= $b);
    }

    private function upTo(Collection $sessions, Carbon $end): Collection
    {
        $b = $end->getTimestamp();

        return $sessions->filter(fn ($s) => $s->at <= $b);
    }

    private function accuracyOf(Collection $sessions): ?int
    {
        $items = $sessions->sum('items');

        return $items > 0 ? (int) round($sessions->sum('correct') / $items * 100) : null;
    }

    private function perStudent(Collection $sessions): Collection
    {
        return $sessions->groupBy('student_id')->map(function (Collection $rows) {
            $attempts = (int) $rows->sum('items');

            return [
                'attempts' => $attempts,
                'accuracy' => $attempts ? (int) round($rows->sum('correct') / $attempts * 100) : 0,
                'subjects' => $rows->pluck('subject_id')->filter()->unique()->count(),
            ];
        });
    }

    /**
     * Ready / developing / at-risk counts, using the same per-student rules
     * as ChairAnalyticsService::studentReadinessRows().
     *
     * @param  bool  $singleSubject  Scoped to one subject, so the
     *                               READY_SUBJECTS breadth rule does not apply.
     */
    private function readinessBands(Collection $sessions, bool $singleSubject): array
    {
        $bands = $this->perStudent($sessions)
            ->map(function (array $s) use ($singleSubject) {
                if ($s['attempts'] < ChairAnalyticsService::DEVELOPING_ATTEMPTS) {
                    return null;
                }
                if ($s['attempts'] >= ChairAnalyticsService::READY_ATTEMPTS
                    && $s['accuracy'] >= ChairAnalyticsService::READY_ACCURACY
                    && ($singleSubject || $s['subjects'] >= ChairAnalyticsService::READY_SUBJECTS)) {
                    return 'ready';
                }

                return $s['accuracy'] >= ChairAnalyticsService::DEVELOPING_ACCURACY ? 'developing' : 'at_risk';
            })
            ->filter();

        $eligible = $bands->count();
        $ready = $bands->filter(fn ($band) => $band === 'ready')->count();
        $developing = $bands->filter(fn ($band) => $band === 'developing')->count();

        return [
            'eligible' => $eligible,
            'ready' => $ready,
            'developing' => $developing,
            'at_risk' => $eligible - $ready - $developing,
            'readiness_rate' => $eligible ? (int) round($ready / $eligible * 100) : null,
            'pass_projection' => $eligible ? (int) round(($ready + $developing * .5) / $eligible * 100) : null,
        ];
    }
}
