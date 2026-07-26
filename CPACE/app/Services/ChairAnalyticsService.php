<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChairAnalyticsService
{
    public const READY_ACCURACY = 60;
    public const READY_ATTEMPTS = 50;
    public const READY_SUBJECTS = 3;
    public const DEVELOPING_ACCURACY = 50;
    public const DEVELOPING_ATTEMPTS = 20;
    public const COVERAGE_TARGET = 25;

    /** A topic needs this many recorded attempts before its accuracy is reportable. */
    public const TOPIC_MIN_ATTEMPTS = 10;

    /** A student counts as engaged when they completed a quiz within this window. */
    public const ACTIVE_WINDOW_DAYS = 7;

    public function dashboardSummary(): array
    {
        $subjects = $this->subjectPerformance();
        $readiness = $this->readinessSummary();
        $coverage = $this->coverageReport();
        $trend = $this->readinessTrend(8);
        $engagement = $this->engagementTrend(8);
        $last = $trend->last();
        $previous = $trend->count() > 1 ? $trend->slice(-2, 1)->first() : null;

        return [
            'class_accuracy' => $subjects->sum('attempts') > 0
                ? (int) round($subjects->sum('correct') / $subjects->sum('attempts') * 100)
                : null,
            'weakest_subject' => $subjects->where('attempts', '>', 0)->sortBy('accuracy')->first(),
            'readiness_rate' => $readiness['readiness_rate'],
            'readiness_change' => $last && $previous
                ? $last['rate'] - $previous['rate']
                : null,
            'thin_topics' => $coverage->whereIn('status', ['critical', 'thin'])->count(),
            'pass_projection' => $readiness['pass_projection'],
            'eligible_students' => $readiness['eligible'],
            'readiness' => $readiness,
            'subjects' => $subjects,
            'trend' => $trend,
            'engagement' => $engagement,
            'cohort' => $this->cohortSummary(),
        ];
    }

    public function performanceReport(?int $subjectId = null): array
    {
        $subjects = $this->subjectPerformance($subjectId);
        $readiness = $this->readinessSummary($subjectId);

        return [
            'subjects' => $subjects,
            'overall_accuracy' => $subjects->sum('attempts') > 0
                ? (int) round($subjects->sum('correct') / $subjects->sum('attempts') * 100)
                : null,
            'total_attempts' => (int) $subjects->sum('attempts'),
            'participating_students' => $this->participatingStudentCount($subjectId),
            'readiness' => $readiness,
            'trend' => $this->readinessTrend(8, $subjectId),
            'engagement' => $this->engagementTrend(8, $subjectId),
            'distribution' => $this->scoreDistribution($subjectId),
            'difficulty' => $this->difficultyPerformance($subjectId),
            'weak_topics' => $this->weakestTopics(10, $subjectId),
            'cohort' => $this->cohortSummary(),
        ];
    }

    public function subjectPerformance(?int $subjectId = null): Collection
    {
        $metrics = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->groupBy('topics.subject_id')
            ->select(
                'topics.subject_id',
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts'),
                DB::raw('COUNT(DISTINCT performance_records.student_id) as students')
            )
            ->get()
            ->keyBy('subject_id');

        return DB::table('subjects')
            ->when($subjectId, fn ($query) => $query->where('id', $subjectId))
            ->orderBy('id')
            ->get()
            ->map(function ($subject) use ($metrics) {
                $metric = $metrics->get($subject->id);
                $attempts = (int) ($metric->attempts ?? 0);
                $correct = (int) ($metric->correct ?? 0);
                $accuracy = $attempts > 0 ? (int) round($correct / $attempts * 100) : null;
                $threshold = (int) ($subject->passing_threshold ?? 75);

                return [
                    'id' => (int) $subject->id,
                    'code' => $subject->code,
                    'name' => $subject->name,
                    'correct' => $correct,
                    'attempts' => $attempts,
                    'students' => (int) ($metric->students ?? 0),
                    'accuracy' => $accuracy,
                    'threshold' => $threshold,
                    // Distance to the subject's own passing mark: the number the chair acts on.
                    'gap_to_threshold' => $accuracy === null ? null : $accuracy - $threshold,
                    'meets_threshold' => $accuracy !== null && $accuracy >= $threshold,
                ];
            });
    }

    public function readinessSummary(?int $subjectId = null): array
    {
        $rows = $this->studentReadinessRows($subjectId);
        $eligible = $rows->where('attempts', '>=', self::DEVELOPING_ATTEMPTS);
        $ready = $eligible->where('band', 'ready')->count();
        $developing = $eligible->where('band', 'developing')->count();
        $atRisk = $eligible->where('band', 'at_risk')->count();
        $eligibleCount = $eligible->count();

        return [
            'ready' => $ready,
            'developing' => $developing,
            'at_risk' => $atRisk,
            'eligible' => $eligibleCount,
            'insufficient' => $rows->count() - $eligibleCount,
            'readiness_rate' => $eligibleCount ? (int) round($ready / $eligibleCount * 100) : null,
            'pass_projection' => $eligibleCount
                ? (int) round(($ready + ($developing * .5)) / $eligibleCount * 100)
                : null,
        ];
    }

    public function readinessTrend(int $weeks = 8, ?int $subjectId = null): Collection
    {
        $sessions = DB::table('quiz_sessions')
            ->whereNotNull('completed_at')
            ->where('session_type', '!=', 'training')
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->select('student_id', 'subject_id', 'total_items', 'correct_answers', 'completed_at')
            ->get();

        return collect(range($weeks - 1, 0))->map(function ($offset) use ($sessions, $subjectId) {
            $cutoff = now()->startOfWeek()->subWeeks($offset)->endOfWeek();
            $upToDate = $sessions->filter(fn ($session) => Carbon::parse($session->completed_at)->lte($cutoff));
            $items = (int) $upToDate->sum('total_items');
            $correct = (int) $upToDate->sum('correct_answers');

            $eligible = $upToDate
                ->groupBy('student_id')
                ->map(function ($studentSessions) use ($subjectId) {
                    $attempts = (int) $studentSessions->sum('total_items');
                    $correct = (int) $studentSessions->sum('correct_answers');
                    $subjects = $studentSessions->pluck('subject_id')->filter()->unique()->count();
                    $accuracy = $attempts ? (int) round($correct / $attempts * 100) : 0;

                    return [
                        'attempts' => $attempts,
                        'ready' => $attempts >= self::READY_ATTEMPTS
                            && $accuracy >= self::READY_ACCURACY
                            && ($subjectId || $subjects >= self::READY_SUBJECTS),
                    ];
                })
                ->filter(fn ($row) => $row['attempts'] >= self::DEVELOPING_ATTEMPTS);

            $ready = $eligible->where('ready', true)->count();

            return [
                'label' => $cutoff->format('M j'),
                'rate' => $eligible->count() ? (int) round($ready / $eligible->count() * 100) : 0,
                'eligible' => $eligible->count(),
                'ready' => $ready,
                // Cumulative class accuracy on the same axis (both are percentages).
                'accuracy' => $items ? (int) round($correct / $items * 100) : 0,
            ];
        });
    }

    /**
     * Week-by-week platform usage. Unlike the readiness trend this is *not*
     * cumulative — it answers "is the cohort still practising?", which is the
     * leading indicator behind every readiness movement.
     */
    public function engagementTrend(int $weeks = 8, ?int $subjectId = null): Collection
    {
        $from = now()->startOfWeek()->subWeeks($weeks - 1);

        $sessions = DB::table('quiz_sessions')
            ->whereNotNull('completed_at')
            ->where('session_type', '!=', 'training')
            ->where('completed_at', '>=', $from)
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->select('student_id', 'total_items', 'correct_answers', 'duration_secs', 'completed_at')
            ->get();

        return collect(range($weeks - 1, 0))->map(function ($offset) use ($sessions) {
            $start = now()->startOfWeek()->subWeeks($offset);
            $end = $start->copy()->endOfWeek();

            $bucket = $sessions->filter(function ($session) use ($start, $end) {
                $at = Carbon::parse($session->completed_at);

                return $at->gte($start) && $at->lte($end);
            });

            $items = (int) $bucket->sum('total_items');
            $correct = (int) $bucket->sum('correct_answers');

            return [
                'label' => $start->format('M j'),
                'active_students' => $bucket->pluck('student_id')->unique()->count(),
                'quizzes' => $bucket->count(),
                'items' => $items,
                'accuracy' => $items ? (int) round($correct / $items * 100) : null,
                'hours' => round($bucket->sum('duration_secs') / 3600, 1),
            ];
        });
    }

    /**
     * How the measured class is spread, not just its average. A 62% mean built
     * from a bimodal cohort needs a different intervention than a tight 62%.
     */
    public function scoreDistribution(?int $subjectId = null): Collection
    {
        $bands = [
            ['label' => '0–49%', 'min' => 0, 'max' => 49],
            ['label' => '50–59%', 'min' => 50, 'max' => 59],
            ['label' => '60–69%', 'min' => 60, 'max' => 69],
            ['label' => '70–79%', 'min' => 70, 'max' => 79],
            ['label' => '80–89%', 'min' => 80, 'max' => 89],
            ['label' => '90–100%', 'min' => 90, 'max' => 100],
        ];

        $rows = $this->studentReadinessRows($subjectId)
            ->where('attempts', '>=', self::DEVELOPING_ATTEMPTS);

        return collect($bands)->map(fn ($band) => [
            'label' => $band['label'],
            'students' => $rows
                ->filter(fn ($row) => $row['accuracy'] >= $band['min'] && $row['accuracy'] <= $band['max'])
                ->count(),
        ]);
    }

    /**
     * Class accuracy split by authored difficulty. Accuracy that does not fall
     * as difficulty rises means the difficulty labels in the bank are wrong.
     */
    public function difficultyPerformance(?int $subjectId = null): Collection
    {
        $rows = DB::table('quiz_answers')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereNotNull('quiz_answers.is_correct')
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->groupBy(DB::raw('LOWER(questions.difficulty)'))
            ->select(
                DB::raw('LOWER(questions.difficulty) as difficulty'),
                DB::raw('COUNT(*) as answered'),
                DB::raw('COALESCE(SUM(quiz_answers.is_correct), 0) as correct')
            )
            ->get();

        $buckets = [
            'easy' => ['label' => 'Easy', 'answered' => 0, 'correct' => 0],
            'moderate' => ['label' => 'Moderate', 'answered' => 0, 'correct' => 0],
            'difficult' => ['label' => 'Difficult', 'answered' => 0, 'correct' => 0],
        ];

        foreach ($rows as $row) {
            $key = $this->normaliseDifficulty($row->difficulty);
            if ($key === null) {
                continue;
            }
            $buckets[$key]['answered'] += (int) $row->answered;
            $buckets[$key]['correct'] += (int) $row->correct;
        }

        return collect($buckets)->map(fn ($bucket) => [
            'label' => $bucket['label'],
            'answered' => $bucket['answered'],
            'accuracy' => $bucket['answered'] > 0
                ? (int) round($bucket['correct'] / $bucket['answered'] * 100)
                : null,
        ])->values();
    }

    /** Lowest-scoring topics across the whole cohort — the remediation shortlist. */
    public function weakestTopics(int $limit = 10, ?int $subjectId = null): Collection
    {
        return DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->when($subjectId, fn ($query) => $query->where('subjects.id', $subjectId))
            ->groupBy('topics.id', 'topics.name', 'subjects.code')
            ->havingRaw('SUM(performance_records.total_attempts) >= ?', [self::TOPIC_MIN_ATTEMPTS])
            ->select(
                'topics.id',
                'topics.name',
                'subjects.code',
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts'),
                DB::raw('COUNT(DISTINCT performance_records.student_id) as students'),
                DB::raw('SUM(CASE WHEN performance_records.is_weak_area = 1 THEN 1 ELSE 0 END) as flagged')
            )
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'subject_code' => $row->code,
                'attempts' => (int) $row->attempts,
                'students' => (int) $row->students,
                'flagged' => (int) $row->flagged,
                'accuracy' => (int) round((int) $row->correct / max(1, (int) $row->attempts) * 100),
            ])
            ->sortBy('accuracy')
            ->take($limit)
            ->values();
    }

    /**
     * Coverage is reported per curriculum area (a top-level topic), with the
     * questions of every nested subtopic rolled up into their area. The syllabus
     * tree is up to five levels deep and questions are authored at whatever level
     * the faculty chose, so counting each node separately reports hundreds of
     * empty branches as "critical" and buries the real gaps.
     */
    public function coverageReport(?int $subjectId = null): Collection
    {
        $topics = DB::table('topics')
            ->select('id', 'parent_id', 'subject_id', 'is_active')
            ->get();

        $byId = $topics->keyBy('id');
        $rootOf = [];
        foreach ($topics as $topic) {
            $node = $topic;
            $hops = 0;
            // Hop-capped rather than recursive so a malformed parent cycle cannot hang the report.
            while ($node && $node->parent_id && $hops < 12) {
                $node = $byId->get($node->parent_id);
                $hops++;
            }
            $rootOf[(int) $topic->id] = (int) ($node->id ?? $topic->id);
        }

        $subtopics = [];
        foreach ($topics as $topic) {
            if ($topic->parent_id === null || ! $topic->is_active) {
                continue;
            }
            $root = $rootOf[(int) $topic->id];
            $subtopics[$root] = ($subtopics[$root] ?? 0) + 1;
        }

        $counts = DB::table('questions')
            ->select(
                'topic_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw("SUM(CASE WHEN LOWER(difficulty) = 'easy' THEN 1 ELSE 0 END) as easy"),
                DB::raw("SUM(CASE WHEN LOWER(difficulty) IN ('medium','moderate') THEN 1 ELSE 0 END) as moderate"),
                DB::raw("SUM(CASE WHEN LOWER(difficulty) IN ('hard','difficult') THEN 1 ELSE 0 END) as difficult"),
                DB::raw('MAX(created_at) as last_added')
            )
            ->groupBy('topic_id')
            ->get();

        $rolled = [];
        foreach ($counts as $count) {
            $root = $rootOf[(int) $count->topic_id] ?? (int) $count->topic_id;
            $bucket = $rolled[$root] ?? [
                'total' => 0, 'active' => 0, 'easy' => 0,
                'moderate' => 0, 'difficult' => 0, 'last_added' => null,
            ];

            $bucket['total'] += (int) $count->total;
            $bucket['active'] += (int) $count->active;
            $bucket['easy'] += (int) $count->easy;
            $bucket['moderate'] += (int) $count->moderate;
            $bucket['difficult'] += (int) $count->difficult;

            if ($count->last_added) {
                $added = Carbon::parse($count->last_added);
                if ($bucket['last_added'] === null || $added->gt($bucket['last_added'])) {
                    $bucket['last_added'] = $added;
                }
            }

            $rolled[$root] = $bucket;
        }

        return DB::table('topics')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->when($subjectId, fn ($query) => $query->where('subjects.id', $subjectId))
            ->where('topics.is_active', true)
            ->whereNull('topics.parent_id')
            ->orderBy('subjects.id')
            ->orderBy('topics.sort_order')
            ->select('topics.id', 'topics.name', 'subjects.id as subject_id', 'subjects.code', 'subjects.name as subject_name')
            ->get()
            ->map(function ($topic) use ($rolled, $subtopics) {
                $bucket = $rolled[(int) $topic->id] ?? null;
                $active = (int) ($bucket['active'] ?? 0);
                $status = $active === 0 ? 'critical' : ($active < self::COVERAGE_TARGET ? 'thin' : 'adequate');

                return [
                    'id' => (int) $topic->id,
                    'name' => $topic->name,
                    'subject_id' => (int) $topic->subject_id,
                    'subject_code' => $topic->code,
                    'subject_name' => $topic->subject_name,
                    'subtopics' => (int) ($subtopics[(int) $topic->id] ?? 0),
                    'total' => (int) ($bucket['total'] ?? 0),
                    'active' => $active,
                    'easy' => (int) ($bucket['easy'] ?? 0),
                    'moderate' => (int) ($bucket['moderate'] ?? 0),
                    'difficult' => (int) ($bucket['difficult'] ?? 0),
                    'last_added' => $bucket['last_added'] ?? null,
                    'status' => $status,
                    'gap' => max(0, self::COVERAGE_TARGET - $active),
                    'coverage' => (int) min(100, round($active / self::COVERAGE_TARGET * 100)),
                ];
            });
    }

    /** Subject-level rollup of the coverage report — the chair's planning unit. */
    public function subjectCoverageRollup(?int $subjectId = null): Collection
    {
        return $this->coverageReport($subjectId)
            ->groupBy('subject_code')
            ->map(function (Collection $areas, $code) {
                $target = $areas->count() * self::COVERAGE_TARGET;
                $active = (int) $areas->sum('active');

                return [
                    'code' => $code,
                    'name' => $areas->first()['subject_name'],
                    'areas' => $areas->count(),
                    'subtopics' => (int) $areas->sum('subtopics'),
                    'adequate' => $areas->where('status', 'adequate')->count(),
                    'thin' => $areas->where('status', 'thin')->count(),
                    'critical' => $areas->where('status', 'critical')->count(),
                    'total' => (int) $areas->sum('total'),
                    'active' => $active,
                    'inactive' => (int) $areas->sum('total') - $active,
                    'easy' => (int) $areas->sum('easy'),
                    'moderate' => (int) $areas->sum('moderate'),
                    'difficult' => (int) $areas->sum('difficult'),
                    'gap' => (int) $areas->sum('gap'),
                    'coverage' => $target > 0 ? (int) min(100, round($active / $target * 100)) : 0,
                ];
            })
            ->values();
    }

    /** Month-by-month test-bank growth — is authoring keeping up with the gap? */
    public function bankGrowth(int $months = 6, ?int $subjectId = null): Collection
    {
        $from = now()->startOfMonth()->subMonths($months - 1);

        $questions = DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->where('questions.created_at', '>=', $from)
            ->select('questions.created_at', 'questions.is_active')
            ->get();

        return collect(range($months - 1, 0))->map(function ($offset) use ($questions) {
            $start = now()->startOfMonth()->subMonths($offset);
            $end = $start->copy()->endOfMonth();

            $bucket = $questions->filter(function ($question) use ($start, $end) {
                $at = Carbon::parse($question->created_at);

                return $at->gte($start) && $at->lte($end);
            });

            return [
                'label' => $start->format('M Y'),
                'added' => $bucket->count(),
                'active' => $bucket->where('is_active', true)->count(),
            ];
        });
    }

    /** Enrollment health: who is on the platform and who has gone quiet. */
    public function cohortSummary(): array
    {
        $students = DB::table('users')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.role_id', Role::STUDENT)
            ->select('users.id', 'users.is_active', 'users.last_login_at',
                'student_profiles.is_alumni', 'student_profiles.is_shifted')
            ->get();

        $recent = DB::table('quiz_sessions')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(self::ACTIVE_WINDOW_DAYS))
            ->distinct()
            ->pluck('student_id');

        $everPractised = DB::table('quiz_sessions')
            ->whereNotNull('completed_at')
            ->distinct()
            ->pluck('student_id');

        $enrolled = $students->filter(fn ($student) => ! $student->is_alumni && ! $student->is_shifted);

        return [
            'total' => $students->count(),
            'enrolled' => $enrolled->count(),
            'active_accounts' => $enrolled->where('is_active', true)->count(),
            'deactivated' => $enrolled->where('is_active', false)->count(),
            'alumni' => $students->where('is_alumni', true)->count(),
            'shifted' => $students->where('is_shifted', true)->count(),
            'practising' => $enrolled->filter(fn ($s) => $recent->contains($s->id))->count(),
            'never_practised' => $enrolled->filter(fn ($s) => ! $everPractised->contains($s->id))->count(),
        ];
    }

    private function normaliseDifficulty(?string $value): ?string
    {
        return match (strtolower(trim((string) $value))) {
            'easy' => 'easy',
            'medium', 'moderate' => 'moderate',
            'hard', 'difficult' => 'difficult',
            default => null,
        };
    }

    private function studentReadinessRows(?int $subjectId = null): Collection
    {
        return DB::table('users')
            ->leftJoin('quiz_sessions', function ($join) use ($subjectId) {
                $join->on('quiz_sessions.student_id', '=', 'users.id')
                    ->whereNotNull('quiz_sessions.completed_at')
                    ->where('quiz_sessions.session_type', '!=', 'training');
                if ($subjectId) {
                    $join->where('quiz_sessions.subject_id', '=', $subjectId);
                }
            })
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->groupBy('users.id')
            ->select(
                'users.id',
                DB::raw('COALESCE(SUM(quiz_sessions.total_items), 0) as attempts'),
                DB::raw('COALESCE(SUM(quiz_sessions.correct_answers), 0) as correct'),
                DB::raw('COUNT(DISTINCT quiz_sessions.subject_id) as subjects')
            )
            ->get()
            ->map(function ($row) use ($subjectId) {
                $attempts = (int) $row->attempts;
                $accuracy = $attempts ? (int) round((int) $row->correct / $attempts * 100) : 0;
                $ready = $attempts >= self::READY_ATTEMPTS
                    && $accuracy >= self::READY_ACCURACY
                    && ($subjectId || (int) $row->subjects >= self::READY_SUBJECTS);

                return [
                    'attempts' => $attempts,
                    'accuracy' => $accuracy,
                    'band' => $ready
                        ? 'ready'
                        : (($attempts >= self::DEVELOPING_ATTEMPTS && $accuracy >= self::DEVELOPING_ACCURACY) ? 'developing' : 'at_risk'),
                ];
            });
    }

    private function participatingStudentCount(?int $subjectId = null): int
    {
        return DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->where('performance_records.total_attempts', '>', 0)
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->distinct('performance_records.student_id')
            ->count('performance_records.student_id');
    }
}
