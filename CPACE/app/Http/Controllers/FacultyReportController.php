<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Subject;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Faculty "Reports" builder. Produces paper-ready student-performance reports
 * (Class Performance Summary, At-Risk, Subject Mastery, Question Quality) from
 * live data - quiz_sessions / quiz_answers / performance_records - scoped to the
 * subjects the Program Chair has assigned to the signed-in faculty member.
 *
 * Every figure is computed on the fly; nothing on the page is hard-coded. The
 * preview reloads on the GET filter form, PDF/Print use the browser's print
 * pipeline (print CSS), and CSV is streamed by {@see export()}.
 */
class FacultyReportController extends Controller
{
    /** Subject brand colours reused for the accuracy bars. */
    private const SUBJECT_COLORS = [
        'FAR'  => '#3b82f6',
        'AFAR' => '#059669',
        'MS'   => '#8b5cf6',
        'TAX'  => '#c0392b',
        'AUD'  => '#e8567d',
        'RFBT' => '#d97706',
    ];

    private const REPORT_TYPES = [
        'class_summary'    => 'Class Performance Summary',
        'at_risk'          => 'At-Risk Student Report',
        'subject_mastery'  => 'Subject Mastery Report',
        'question_quality' => 'Question Quality Report',
    ];

    /**
     * Render the report builder + live preview.
     */
    public function index(Request $request)
    {
        $data = $this->build($request);

        return view('faculty.reports', $data);
    }

    /**
     * Stream the current report as a CSV (respecting every active filter). The
     * columns depend on the selected report type.
     */
    public function export(Request $request)
    {
        $data = $this->build($request);

        $type = $data['filters']['report'];
        $slug = str_replace('_', '-', $type);
        $filename = "cpace-{$slug}-report-" . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($data, $type) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents

            switch ($type) {
                case 'subject_mastery':
                    fputcsv($out, ['Subject', 'Topic', 'Students', 'Attempts', 'Accuracy (%)', 'Mastery']);
                    foreach ($data['mastery'] as $subject) {
                        foreach ($subject['topics'] as $t) {
                            fputcsv($out, [
                                $subject['name'], $t['topic'], $t['students'],
                                $t['attempts'], $t['accuracy'], $t['level'],
                            ]);
                        }
                    }
                    break;

                case 'question_quality':
                    fputcsv($out, ['#', 'Subject', 'Topic', 'Difficulty', 'Question', 'Times Answered', 'Correct (%)', 'Flag']);
                    foreach ($data['questions'] as $i => $q) {
                        fputcsv($out, [
                            $i + 1, $q['subject'], $q['topic'], ucfirst($q['difficulty']),
                            $q['text'], $q['answered'], $q['answered'] ? $q['accuracy'] : 'n/a', $q['flag'],
                        ]);
                    }
                    break;

                default: // class_summary + at_risk share the student roster
                    fputcsv($out, ['Student', 'Email', 'Section', 'Avg Score (%)', 'Questions Attempted', 'Quizzes', 'Weak Areas', 'Last Active', 'Status']);
                    $roster = $type === 'at_risk' ? $data['atRisk'] : $data['students'];
                    foreach ($roster as $r) {
                        fputcsv($out, [
                            $r['name'], $r['email'], $r['section'] ?: '-', $r['score'],
                            $r['attempted'], $r['quizzes'], implode(' / ', $r['weak_areas']),
                            $r['last_active'] ? Carbon::parse($r['last_active'])->format('Y-m-d H:i') : '',
                            $r['status_label'],
                        ]);
                    }
            }

            fclose($out);
        }, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Data building
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve filters and compute every dataset the view / export can render.
     */
    private function build(Request $request): array
    {
        $filters = $this->filters($request);

        // Subjects assigned to this faculty (falls back to all subjects so a
        // brand-new faculty account still sees something useful).
        $assigned = Auth::user()->assignedSubjects()->orderBy('subjects.id')->get();
        if ($assigned->isEmpty()) {
            $assigned = Subject::orderBy('id')->get();
        }
        $assignedIds = $assigned->pluck('id')->all();

        // Effective subject scope: a single assigned subject, or all assigned.
        $scopeId = $filters['scope'];
        $subjectIds = ($scopeId && in_array($scopeId, $assignedIds, true))
            ? [$scopeId]
            : $assignedIds;

        $students = $this->studentRows($subjectIds, $filters['from']);
        $students = $this->applyGroup($students, $filters['group']);

        $totalAtt = (int) $students->sum('attempted');
        $totalCor = (int) $students->sum('correct');

        $weakTopics = $this->classWeakTopics($subjectIds);

        $stats = [
            'students'      => $students->count(),
            'accuracy'      => $totalAtt > 0 ? (int) round($totalCor / $totalAtt * 100) : 0,
            'at_risk'       => $students->where('at_risk', true)->count(),
            'weak_topics'   => $weakTopics->count(),
        ];

        $atRisk = $students->where('at_risk', true)
            ->sortBy('score')->values();

        $scopeLabel = ($scopeId && in_array($scopeId, $assignedIds, true))
            ? optional($assigned->firstWhere('id', $scopeId))->name
            : 'All Assigned Subjects';

        return [
            'filters'       => $filters,
            'reportTypes'   => self::REPORT_TYPES,
            'reportLabel'   => self::REPORT_TYPES[$filters['report']],
            'assigned'      => $assigned,
            'sections'      => $this->sectionList(),
            'scopeLabel'    => $scopeLabel,
            'rangeLabel'    => $this->rangeLabel($filters['range']),
            'stats'         => $stats,
            'students'      => $students,
            'atRisk'        => $atRisk,
            'subjectBars'   => $this->subjectAccuracy($subjectIds, $filters['from']),
            'distribution'  => $this->scoreDistribution($students),
            'weakTopics'    => $weakTopics,
            'mastery'       => $filters['report'] === 'subject_mastery'
                                ? $this->subjectMastery($subjectIds) : [],
            'questions'     => $filters['report'] === 'question_quality'
                                ? $this->questionQuality($subjectIds) : collect(),
            'recommendations' => $this->recommendations($stats, $weakTopics, $atRisk),
            'activeQuery'   => $this->activeQuery($filters),
            'generatedAt'   => now(),
        ];
    }

    /**
     * Normalise / validate the request filters.
     */
    private function filters(Request $request): array
    {
        $report = $request->input('report', 'class_summary');
        if (! array_key_exists($report, self::REPORT_TYPES)) {
            $report = 'class_summary';
        }

        $range = $request->input('range', 'term');
        if (! in_array($range, ['term', '30', '7', 'all'], true)) {
            $range = 'term';
        }

        $scope = $request->input('scope');
        $scope = is_numeric($scope) ? (int) $scope : null;

        $group = (string) $request->input('group', 'all');

        // "Current term" is treated as the last 120 days; All time = no floor.
        $from = match ($range) {
            '7'   => Carbon::now()->subDays(7),
            '30'  => Carbon::now()->subDays(30),
            'term'=> Carbon::now()->subDays(120),
            default => null,
        };

        $include = $request->input('include');
        if (! is_array($include)) {
            // Default preview sections when the form has not been submitted.
            $include = ['summary', 'charts', 'atrisk', 'weak', 'recommendations'];
        }

        return [
            'report'  => $report,
            'scope'   => $scope,
            'range'   => $range,
            'group'   => $group,
            'from'    => $from,
            'include' => $include,
        ];
    }

    private function rangeLabel(string $range): string
    {
        return [
            'term' => 'Current Term',
            '30'   => 'Last 30 Days',
            '7'    => 'Last 7 Days',
            'all'  => 'All Time',
        ][$range] ?? 'Current Term';
    }

    /**
     * Query-string of non-default filters, for the export / CSV links.
     */
    private function activeQuery(array $filters): array
    {
        return array_filter([
            'report' => $filters['report'] !== 'class_summary' ? $filters['report'] : null,
            'scope'  => $filters['scope'],
            'range'  => $filters['range'] !== 'term' ? $filters['range'] : null,
            'group'  => $filters['group'] !== 'all' ? $filters['group'] : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Distinct student sections (for the "Student Group" selector).
     */
    private function sectionList(): array
    {
        return DB::table('student_profiles')
            ->whereNotNull('section')
            ->where('section', '!=', '')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->all();
    }

    /**
     * One row per student with completed activity in the scope/period window.
     */
    private function studentRows(array $subjectIds, ?Carbon $from)
    {
        if (empty($subjectIds)) {
            return collect();
        }

        $agg = DB::table('quiz_sessions')
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->whereIn('subject_id', $subjectIds)
            ->when($from, fn ($q) => $q->where('started_at', '>=', $from))
            ->groupBy('student_id')
            ->select(
                'student_id',
                DB::raw('COUNT(*) as quizzes'),
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct'),
                DB::raw('MAX(completed_at) as last_active')
            )
            ->get()
            ->keyBy('student_id');

        if ($agg->isEmpty()) {
            return collect();
        }

        $studentIds = $agg->keys()->all();

        $users = DB::table('users')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.role_id', Role::STUDENT)
            ->whereIn('users.id', $studentIds)
            ->select(
                'users.id', 'users.first_name', 'users.last_name', 'users.email',
                'users.last_login_at', 'student_profiles.section'
            )
            ->get()
            ->keyBy('id');

        // Open (unresolved) weak areas per student, scoped to these subjects,
        // used both for the "weak areas" column and the at-risk detail.
        $weakAreas = DB::table('weakness_reports')
            ->join('topics', 'topics.id', '=', 'weakness_reports.topic_id')
            ->whereIn('weakness_reports.student_id', $studentIds)
            ->whereIn('topics.subject_id', $subjectIds)
            ->whereNull('weakness_reports.resolved_at')
            ->select('weakness_reports.student_id', 'topics.name')
            ->get()
            ->groupBy('student_id')
            ->map(fn ($g) => $g->pluck('name')->unique()->values()->all());

        $rows = collect();
        foreach ($agg as $sid => $a) {
            $user = $users->get($sid);
            if (! $user) {
                continue;
            }

            $attempted = (int) $a->attempted;
            $correct   = (int) $a->correct;
            $score     = $attempted > 0 ? (int) round($correct / $attempted * 100) : 0;

            $name = trim("{$user->first_name} {$user->last_name}");
            $lastActive = $a->last_active ?: $user->last_login_at;
            $daysIdle = $lastActive ? (int) Carbon::parse($lastActive)->diffInDays(now()) : null;

            $atRisk = $attempted >= WeaknessDetector::MIN_ATTEMPTS
                && $score < (int) (WeaknessDetector::ACCURACY_THRESHOLD * 100);

            [$statusKey, $statusLabel] = $this->status($atRisk, $score, $daysIdle);

            $rows->push([
                'id'           => (int) $sid,
                'name'         => $name,
                'email'        => $user->email,
                'section'      => $user->section,
                'score'        => $score,
                'attempted'    => $attempted,
                'correct'      => $correct,
                'quizzes'      => (int) $a->quizzes,
                'weak_areas'   => $weakAreas->get($sid, []),
                'last_active'  => $lastActive,
                'days_idle'    => $daysIdle,
                'at_risk'      => $atRisk,
                'status'       => $statusKey,
                'status_label' => $statusLabel,
            ]);
        }

        return $rows->values();
    }

    /**
     * Filter the roster by the "Student Group" selector.
     */
    private function applyGroup($rows, string $group)
    {
        if ($group === 'all' || $group === '') {
            return $rows;
        }
        if ($group === 'at_risk') {
            return $rows->where('at_risk', true)->values();
        }

        return $rows->filter(fn ($r) => $r['section'] === $group)->values();
    }

    /**
     * Intervention status for a student.
     * @return array{0:string,1:string} [key, label]
     */
    private function status(bool $atRisk, int $score, ?int $daysIdle): array
    {
        if ($atRisk && ($score < 45 || ($daysIdle !== null && $daysIdle >= 5))) {
            return ['high', 'High'];
        }
        if ($atRisk || ($daysIdle !== null && $daysIdle >= 7)) {
            return ['watch', 'Watch'];
        }
        return ['ontrack', 'On Track'];
    }

    /**
     * Class accuracy per subject in scope (from completed, non-training quizzes).
     */
    private function subjectAccuracy(array $subjectIds, ?Carbon $from)
    {
        if (empty($subjectIds)) {
            return collect();
        }

        return DB::table('quiz_sessions')
            ->join('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->where('quiz_sessions.session_type', '!=', 'training')
            ->whereNotNull('quiz_sessions.completed_at')
            ->whereIn('quiz_sessions.subject_id', $subjectIds)
            ->when($from, fn ($q) => $q->where('quiz_sessions.started_at', '>=', $from))
            ->groupBy('subjects.id', 'subjects.code')
            ->select(
                'subjects.code',
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct')
            )
            ->get()
            ->map(function ($r) {
                $acc = $r->attempted > 0 ? (int) round($r->correct / $r->attempted * 100) : 0;
                return [
                    'code'     => $r->code,
                    'accuracy' => $acc,
                    'color'    => self::SUBJECT_COLORS[$r->code] ?? '#7B1D1D',
                ];
            })
            ->sortByDesc('accuracy')
            ->values();
    }

    /**
     * Count students in each score band for the distribution bars.
     */
    private function scoreDistribution($rows): array
    {
        $withAttempts = $rows->where('attempted', '>', 0);
        $total = $withAttempts->count();

        $bands = [
            ['label' => '90-100%',   'color' => '#059669', 'count' => $withAttempts->where('score', '>=', 90)->count()],
            ['label' => '75-89%',    'color' => '#3b82f6', 'count' => $withAttempts->whereBetween('score', [75, 89])->count()],
            ['label' => '60-74%',    'color' => '#d97706', 'count' => $withAttempts->whereBetween('score', [60, 74])->count()],
            ['label' => 'Below 60%', 'color' => '#c0392b', 'count' => $withAttempts->where('score', '<', 60)->count()],
        ];

        foreach ($bands as &$b) {
            $b['pct'] = $total > 0 ? round($b['count'] / $total * 100, 1) : 0;
        }

        return ['total' => $total, 'bands' => $bands];
    }

    /**
     * The class's weakest topics across the scoped subjects (>= 5 attempts).
     */
    private function classWeakTopics(array $subjectIds)
    {
        if (empty($subjectIds)) {
            return collect();
        }

        return DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->whereIn('topics.subject_id', $subjectIds)
            ->groupBy('topics.id', 'topics.name', 'subjects.code')
            ->havingRaw('SUM(performance_records.total_attempts) >= 5')
            ->select(
                'topics.name as topic',
                'subjects.code as subject_code',
                DB::raw('COUNT(DISTINCT performance_records.student_id) as students'),
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts')
            )
            ->get()
            ->map(function ($r) {
                $r->accuracy = $r->attempts > 0 ? (int) round($r->correct / $r->attempts * 100) : 0;
                return $r;
            })
            ->filter(fn ($r) => $r->accuracy < 60)
            ->sortBy('accuracy')
            ->values();
    }

    /**
     * Per-subject topic mastery breakdown (Subject Mastery report).
     */
    private function subjectMastery(array $subjectIds): array
    {
        if (empty($subjectIds)) {
            return [];
        }

        $rows = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->whereIn('topics.subject_id', $subjectIds)
            ->groupBy('subjects.id', 'subjects.code', 'subjects.name', 'topics.id', 'topics.name')
            ->select(
                'subjects.id as subject_id',
                'subjects.code as subject_code',
                'subjects.name as subject_name',
                'topics.name as topic',
                DB::raw('COUNT(DISTINCT performance_records.student_id) as students'),
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts')
            )
            ->get()
            ->groupBy('subject_id');

        $out = [];
        foreach ($rows as $topics) {
            $first = $topics->first();
            $subjAtt = (int) $topics->sum('attempts');
            $subjCor = (int) $topics->sum('correct');

            $out[] = [
                'code'     => $first->subject_code,
                'name'     => $first->subject_name,
                'accuracy' => $subjAtt > 0 ? (int) round($subjCor / $subjAtt * 100) : 0,
                'color'    => self::SUBJECT_COLORS[$first->subject_code] ?? '#7B1D1D',
                'topics'   => $topics->map(function ($t) {
                    $acc = $t->attempts > 0 ? (int) round($t->correct / $t->attempts * 100) : 0;
                    return [
                        'topic'    => $t->topic,
                        'students' => (int) $t->students,
                        'attempts' => (int) $t->attempts,
                        'accuracy' => $acc,
                        'level'    => $this->masteryLevel($acc),
                    ];
                })->sortByDesc('accuracy')->values()->all(),
            ];
        }

        // Best-mastered subjects first.
        usort($out, fn ($a, $b) => $b['accuracy'] <=> $a['accuracy']);

        return $out;
    }

    private function masteryLevel(int $accuracy): string
    {
        return match (true) {
            $accuracy >= 85 => 'Mastered',
            $accuracy >= 70 => 'Proficient',
            $accuracy >= 60 => 'Developing',
            default         => 'Needs Work',
        };
    }

    /**
     * Question-level quality for the scoped subjects: how often each active
     * question has been answered and how well students did on it.
     */
    private function questionQuality(array $subjectIds)
    {
        if (empty($subjectIds)) {
            return collect();
        }

        return DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->leftJoin('quiz_answers', function ($join) {
                $join->on('quiz_answers.question_id', '=', 'questions.id')
                    ->whereNotNull('quiz_answers.is_correct');
            })
            ->whereIn('topics.subject_id', $subjectIds)
            ->where('questions.is_active', 1)
            ->groupBy(
                'questions.id', 'questions.question_text', 'questions.difficulty',
                'topics.name', 'subjects.code'
            )
            ->select(
                'questions.id',
                'questions.question_text as text',
                'questions.difficulty',
                'topics.name as topic',
                'subjects.code as subject',
                DB::raw('COUNT(quiz_answers.id) as answered'),
                DB::raw('COALESCE(SUM(quiz_answers.is_correct),0) as correct')
            )
            ->get()
            ->map(function ($q) {
                $answered = (int) $q->answered;
                $accuracy = $answered > 0 ? (int) round($q->correct / $answered * 100) : 0;

                // Quality flag: never used, too hard, or too easy (weak discriminator).
                if ($answered === 0) {
                    $flag = 'Unused';
                } elseif ($answered >= 5 && $accuracy < 40) {
                    $flag = 'Too Hard';
                } elseif ($answered >= 5 && $accuracy > 95) {
                    $flag = 'Too Easy';
                } else {
                    $flag = 'Healthy';
                }

                return [
                    'id'         => (int) $q->id,
                    'text'       => $q->text,
                    'difficulty' => $q->difficulty,
                    'topic'      => $q->topic,
                    'subject'    => $q->subject,
                    'answered'   => $answered,
                    'accuracy'   => $accuracy,
                    'flag'       => $flag,
                ];
            })
            ->sortBy([
                ['answered', 'asc'],
                ['accuracy', 'asc'],
            ])
            ->values();
    }

    /**
     * Data-driven recommended actions for the report footer.
     */
    private function recommendations(array $stats, $weakTopics, $atRisk): array
    {
        $recs = [];

        if ($weakTopics->isNotEmpty()) {
            $names = $weakTopics->take(2)->pluck('topic')->implode(', ');
            $recs[] = "Schedule a focused review for topics under 60% accuracy (e.g. {$names}).";
        }

        if ($stats['at_risk'] > 0) {
            $recs[] = "Send a follow-up quiz or reminder to the {$stats['at_risk']} at-risk student"
                . ($stats['at_risk'] === 1 ? '' : 's') . ' before the next assessment.';
        }

        if ($stats['accuracy'] > 0 && $stats['accuracy'] < 70) {
            $recs[] = 'Class accuracy is below the 70% target - add more moderate-level practice for frequently missed topics.';
        }

        $recs[] = 'Review question explanations for items with high wrong-answer rates to close comprehension gaps.';

        if ($stats['students'] === 0) {
            $recs = ['No completed activity in the selected scope yet - widen the date range or subject scope, or encourage students to start a quiz.'];
        }

        return $recs;
    }
}
