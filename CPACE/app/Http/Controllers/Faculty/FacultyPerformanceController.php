<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;

use App\Mail\StudentReminderMail;
use App\Models\Role;
use App\Models\Subject;
use App\Services\WeaknessDetector;
use App\Support\FacultySectionScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Faculty "Student Performance" monitor. Every figure on the page is computed
 * live from quiz_sessions / quiz_answers and the per-topic performance_records -
 * nothing is hard-coded. The page supports searching, subject / period / sort
 * filters, pagination, a per-student detail modal, a CSV export and a
 * "send reminder" action that drops real rows into the notifications table.
 */
class FacultyPerformanceController extends Controller
{
    public function __construct(private WeaknessDetector $weakness) {}

    private const PER_PAGE = 10;

    /** Subject brand colours reused for the avatars / dots. */
    private const SUBJECT_COLORS = [
        'FAR'  => '#3b82f6',
        'AFAR' => '#17a2b8',
        'MS'   => '#8b5cf6',
        'TAX'  => '#27ae60',
        'AUD'  => '#e8567d',
        'RFBT' => '#f59e0b',
    ];

    /**
     * The performance dashboard.
     */
    public function index(Request $request)
    {
        $filters = $this->filters($request, $this->assignedSubjectIds(Auth::user()));

        $rows = $this->studentRows($filters);          // every qualifying student
        $search = trim((string) $filters['search']);
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name']), $needle)
                || str_contains(mb_strtolower($r['email']), $needle))->values();
        }

        $rows = $this->sortRows($rows, $filters['sort']);

        // ── Headline stats (over the filtered set, before pagination) ──────
        $withAttempts = $rows->where('attempted', '>', 0);
        $stats = [
            'active'  => $rows->count(),
            'avg'     => $withAttempts->count() ? (int) round($withAttempts->avg('score')) : 0,
            'at_risk' => $rows->where('at_risk', true)->count(),
            'top'     => $withAttempts->count() ? (int) $withAttempts->max('score') : 0,
        ];

        // ── Pagination (manual: the view has its own styled controls) ──────
        $total   = $rows->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page    = min(max(1, (int) $request->input('page', 1)), $lastPage);
        $pageRows = $rows->forPage($page, self::PER_PAGE)->values();

        // Per-student detail (subject breakdown + weak topics) for the modal -
        // only for the students actually shown on this page.
        $details = $this->studentDetails($pageRows->pluck('id')->all());

        $pagination = [
            'total'   => $total,
            'from'    => $total ? (($page - 1) * self::PER_PAGE) + 1 : 0,
            'to'      => min($page * self::PER_PAGE, $total),
            'current' => $page,
            'last'    => $lastPage,
        ];

        $data = [
            'stats'        => $stats,
            'students'     => $pageRows,
            'details'      => $details,
            'pagination'   => $pagination,
            'atRisk'       => $rows->where('at_risk', true)->sortBy('score')->take(5)->values(),
            'weakTopics'   => $this->classWeakTopics($filters),
            'distribution' => $this->scoreDistribution($rows),
            'subjects'     => $this->subjectsFor(Auth::user())->orderBy('id')->get(),
            'filters'      => $filters,
            'activeQuery'  => $this->activeQuery($filters),
        ];

        // Live search / sort / filter / pagination only need the data area,
        // so the page never does a full reload - the controls swap the
        // stats + table + side panels in place.
        if ($request->ajax()) {
            return view('faculty.partials.performance-content', $data);
        }

        return view('faculty.performance', $data);
    }

    /**
     * The active filters as a query-string array (used for export, reminder and
     * pagination links). Defaults are omitted so URLs stay clean.
     */
    private function activeQuery(array $filters): array
    {
        return array_filter([
            'search'  => $filters['search'] !== '' ? $filters['search'] : null,
            'subject' => $filters['subject'],
            'period'  => $filters['period'] !== '30' ? $filters['period'] : null,
            'sort'    => $filters['sort'] !== 'score_desc' ? $filters['sort'] : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Subjects a faculty member is allowed to see performance data for: only
     * the subjects the Program Chair assigned to them. Chair/admin see
     * everything (subjects are unrestricted for them everywhere below).
     */
    private function subjectsFor(\App\Models\User $user)
    {
        return $user->isChair()
            ? Subject::where('is_active', true)
            : $user->assignedSubjects()->where('is_active', true);
    }

    /** Null means "no restriction" (chair/admin); otherwise the faculty's assigned subject ids. */
    private function assignedSubjectIds(\App\Models\User $user): ?array
    {
        return $user->isChair() ? null : $user->assignedSubjects()->pluck('subjects.id')->all();
    }

    /**
     * Normalise the request filters into the shape the rest of the controller
     * works with (and the view echoes back into the controls). $assignedIds
     * is null for a chair (no restriction) or the faculty's assigned subject
     * ids otherwise — an explicit ?subject= outside that set is ignored so a
     * faculty member can't browse another subject's students via the URL.
     */
    private function filters(Request $request, ?array $assignedIds): array
    {
        $period = $request->input('period', '30');
        if (! in_array($period, ['7', '30', '90', 'all'], true)) {
            $period = '30';
        }

        $sort = $request->input('sort', 'score_desc');
        if (! in_array($sort, ['score_desc', 'score_asc', 'active', 'name'], true)) {
            $sort = 'score_desc';
        }

        $subjectId = $request->input('subject');
        $subjectId = is_numeric($subjectId) ? (int) $subjectId : null;
        if ($subjectId !== null && $assignedIds !== null && ! in_array($subjectId, $assignedIds, true)) {
            $subjectId = null;
        }

        $from = match ($period) {
            '7'  => Carbon::now()->subDays(7),
            '30' => Carbon::now()->subDays(30),
            '90' => Carbon::now()->subDays(90),
            default => null,
        };

        return [
            'search'      => (string) $request->input('search', ''),
            'subject'     => $subjectId,
            // The ids actually applied to every query below: the one chosen
            // subject, or (for a faculty with no subject picked) all of
            // theirs, or null for chair/admin ("All Subjects" = everything).
            'subject_ids' => $subjectId !== null ? [$subjectId] : $assignedIds,
            'period'      => $period,
            'sort'        => $sort,
            'from'        => $from,
        ];
    }

    /**
     * Build one row per student that has completed activity within the current
     * period / subject window: average score, quiz count, subjects covered,
     * trend vs the previous week, last-active time and a weak-area flag.
     */
    private function studentRows(array $filters)
    {
        $from       = $filters['from'];
        $subjectIds = $filters['subject_ids'];
        $faculty    = Auth::user();

        // Reusable base query honouring the period + subject (+ section,
        // when the faculty is restricted to specific sections) filters.
        $base = function () use ($from, $subjectIds, $faculty) {
            $query = DB::table('quiz_sessions')
                ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'quiz_sessions.student_id')
                ->where('quiz_sessions.session_type', '!=', 'training')
                ->whereNotNull('quiz_sessions.completed_at')
                ->when($from, fn ($q) => $q->where('quiz_sessions.started_at', '>=', $from));

            return $subjectIds !== null
                ? FacultySectionScope::apply($query, $faculty, $subjectIds, 'quiz_sessions.subject_id', 'student_profiles.section')
                : $query;
        };

        // Headline aggregate per student.
        $agg = $base()
            ->groupBy('quiz_sessions.student_id')
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

        // Subjects covered (distinct subject codes per student) in the window.
        $covered = $base()
            ->join('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->whereNotNull('quiz_sessions.subject_id')
            ->whereIn('quiz_sessions.student_id', $studentIds)
            ->distinct()
            ->select('quiz_sessions.student_id', 'subjects.code')
            ->get()
            ->groupBy('student_id')
            ->map(fn ($g) => $g->pluck('code')->unique()->values()->all());

        // Trend: accuracy in the last 7 days vs the previous 7 (subject filter
        // applies, but the trend window is fixed so it always means "recent").
        $now   = Carbon::now();
        $trend = $this->trendAccuracy($subjectIds, $now, $studentIds);

        // User identity + fallback last-login.
        $users = DB::table('users')
            ->where('role_id', Role::STUDENT)
            ->whereIn('id', $studentIds)
            ->select('id', 'first_name', 'last_name', 'email', 'last_login_at', 'is_active')
            ->get()
            ->keyBy('id');

        $rows = collect();
        foreach ($agg as $sid => $a) {
            $user = $users->get($sid);
            if (! $user) {
                continue; // session belongs to a non-student / removed account
            }

            $attempted = (int) $a->attempted;
            $correct   = (int) $a->correct;
            $score     = $attempted > 0 ? (int) round($correct / $attempted * 100) : 0;

            $last7 = $trend['last'][$sid] ?? null;
            $prev7 = $trend['prev'][$sid] ?? null;
            $dir   = 'flat';
            if ($last7 !== null && $prev7 !== null) {
                if ($last7 > $prev7 + 2) {
                    $dir = 'up';
                } elseif ($last7 < $prev7 - 2) {
                    $dir = 'down';
                }
            } elseif ($last7 !== null && $prev7 === null) {
                $dir = 'up';
            }

            $name = trim("{$user->first_name} {$user->last_name}");

            $rows->push([
                'id'         => (int) $sid,
                'name'       => $name,
                'email'      => $user->email,
                'initials'   => $this->initials($name),
                'color'      => $this->avatarColor($name),
                'score'      => $score,
                'attempted'  => $attempted,
                'quizzes'    => (int) $a->quizzes,
                'subjects'   => $covered->get($sid, []),
                'trend'      => $dir,
                'last_active'=> $a->last_active ?: $user->last_login_at,
                // Weak: low class-style accuracy on a real sample.
                'at_risk'    => $attempted >= WeaknessDetector::MIN_ATTEMPTS
                                && $score < (int) (WeaknessDetector::ACCURACY_THRESHOLD * 100),
            ]);
        }

        return $rows->values();
    }

    /**
     * Last-7-days and previous-7-days accuracy per student, keyed by id.
     */
    private function trendAccuracy(?array $subjectIds, Carbon $now, array $studentIds): array
    {
        $window = function (Carbon $start, Carbon $end) use ($subjectIds, $studentIds) {
            return DB::table('quiz_sessions')
                ->where('session_type', '!=', 'training')
                ->whereNotNull('completed_at')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('started_at', [$start, $end])
                ->when($subjectIds !== null, fn ($q) => $q->whereIn('subject_id', $subjectIds))
                ->groupBy('student_id')
                ->select(
                    'student_id',
                    DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                    DB::raw('COALESCE(SUM(correct_answers),0) as correct')
                )
                ->get()
                ->mapWithKeys(fn ($r) => [
                    (int) $r->student_id => $r->attempted > 0 ? (int) round($r->correct / $r->attempted * 100) : null,
                ])
                ->all();
        };

        return [
            'last' => $window($now->copy()->subDays(7), $now->copy()),
            'prev' => $window($now->copy()->subDays(14), $now->copy()->subDays(7)),
        ];
    }

    /**
     * Apply the requested sort to the student rows.
     */
    private function sortRows($rows, string $sort)
    {
        return match ($sort) {
            'score_asc' => $rows->sortBy('score')->values(),
            'active'    => $rows->sortByDesc('quizzes')->values(),
            'name'      => $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            default     => $rows->sortByDesc('score')->values(),
        };
    }

    /**
     * Per-student subject breakdown + weak topics for the detail modal. Computed
     * straight from performance_records so it matches the student's own
     * Performance page. Keyed by student id.
     */
    private function studentDetails(array $studentIds): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $records = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->whereIn('performance_records.student_id', $studentIds)
            ->where('performance_records.total_attempts', '>', 0)
            ->select(
                'performance_records.student_id',
                'performance_records.topic_id',
                'performance_records.correct_count',
                'performance_records.total_attempts',
                'performance_records.consecutive_wrong',
                'topics.name as topic',
                'subjects.code as subject_code',
                'subjects.name as subject_name'
            )
            ->get()
            ->groupBy('student_id');

        // Weak (student, topic) pairs across the whole page, evaluated once so we
        // can fetch each one's "why" (the misconception behind it) in one query.
        $weakByStudent = [];
        $pairs = [];
        foreach ($studentIds as $sid) {
            $weakByStudent[$sid] = $records->get($sid, collect())->filter(function ($r) {
                [$isWeak] = $this->weakness->evaluate($r);
                return $isWeak;
            });
            foreach ($weakByStudent[$sid] as $r) {
                $pairs[] = [$sid, $r->topic_id];
            }
        }
        $misses = $this->topMissedQuestions($pairs, 'quiz_sessions.student_id');

        $details = [];
        foreach ($studentIds as $sid) {
            $recs = $records->get($sid, collect());

            // Aggregate per subject.
            $subjects = $recs->groupBy('subject_code')->map(function ($g) {
                $att = (int) $g->sum('total_attempts');
                $cor = (int) $g->sum('correct_count');
                return [
                    'code'     => $g->first()->subject_code,
                    'name'     => $g->first()->subject_name,
                    'accuracy' => $att > 0 ? (int) round($cor / $att * 100) : 0,
                    'attempts' => $att,
                ];
            })->sortByDesc('accuracy')->values()->all();

            // Weak topics, using the shared detector rule - with the reason and
            // the specific misconception driving it, so faculty know *why*.
            $weak = $weakByStudent[$sid]->map(function ($r) use ($misses, $sid) {
                [, $reason] = $this->weakness->evaluate($r);
                $att = (int) $r->total_attempts;
                $miss = $misses[$sid . ':' . $r->topic_id] ?? null;

                return [
                    'topic'    => $r->topic,
                    'subject'  => $r->subject_code,
                    'accuracy' => $att > 0 ? (int) round($r->correct_count / $att * 100) : 0,
                    'why'      => $reason === 'consecutive_wrong'
                        ? "Missed {$r->consecutive_wrong} in a row most recently"
                        : "Below 60% accuracy over {$att} attempts",
                    'miss'     => $miss ? "Often picks \"{$miss['choice']}\" on \"{$miss['question']}\"" : null,
                ];
            })->sortBy('accuracy')->values()->all();

            $details[$sid] = [
                'subjects' => $subjects,
                'weak'     => $weak,
            ];
        }

        return $details;
    }

    /**
     * For each (owner, topic_id) pair — owner being a student id (per-student
     * view) or null (class-wide view) — find the single question that owner
     * gets wrong the most within that topic, and the wrong choice they pick
     * most often on it. This is the concrete "why" behind a weak-topic flag:
     * not just a low percentage, but the actual misconception driving it.
     *
     * @param  array<int, array{0: int|null, 1: int}>  $pairs  [owner, topic_id]
     * @param  string  $ownerColumn  Fully-qualified column identifying the owner
     *                               (student_id for per-student, or a topic-only
     *                               grouping when $pairs entries use owner=null).
     * @return array<string, array{question: string, choice: string, times: int}>
     */
    private function topMissedQuestions(array $pairs, string $ownerColumn): array
    {
        if (empty($pairs)) {
            return [];
        }

        $topicIds = array_unique(array_column($pairs, 1));
        $studentIds = array_filter(array_unique(array_column($pairs, 0)), fn ($v) => $v !== null);

        $query = DB::table('quiz_answers')
            ->join('quiz_sessions', 'quiz_sessions.id', '=', 'quiz_answers.session_id')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('question_choices', 'question_choices.id', '=', 'quiz_answers.selected_choice')
            ->whereIn('questions.topic_id', $topicIds)
            ->where('quiz_answers.is_correct', false)
            ->whereNotNull('quiz_answers.selected_choice');

        if (! empty($studentIds)) {
            $query->whereIn('quiz_sessions.student_id', $studentIds);
        }

        $rows = $query
            ->select(
                DB::raw("{$ownerColumn} as owner_id"),
                'questions.topic_id',
                'questions.question_text',
                'question_choices.choice_text',
                DB::raw('COUNT(*) as times')
            )
            ->groupBy('owner_id', 'questions.topic_id', 'questions.id', 'questions.question_text', 'question_choices.id', 'question_choices.choice_text')
            ->get()
            ->groupBy(fn ($r) => ($r->owner_id ?? 'class') . ':' . $r->topic_id);

        $out = [];
        foreach ($rows as $key => $group) {
            $top = $group->sortByDesc('times')->first();
            $out[$key] = [
                'question' => \Illuminate\Support\Str::limit($top->question_text, 60),
                'choice'   => \Illuminate\Support\Str::limit($top->choice_text, 40),
                'times'    => (int) $top->times,
            ];
        }

        return $out;
    }

    /**
     * The class's weakest topics: aggregate accuracy across every student in the
     * current subject/period window, lowest 5 (with a real sample behind them).
     */
    private function classWeakTopics(array $filters)
    {
        $subjectIds = $filters['subject_ids'];

        $topics = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->when($subjectIds !== null, fn ($q) => $q->whereIn('subjects.id', $subjectIds))
            ->groupBy('topics.id', 'topics.name', 'subjects.code')
            ->havingRaw('SUM(performance_records.total_attempts) >= 5')
            ->select(
                'topics.id as topic_id',
                'topics.name as topic',
                'subjects.code as subject_code',
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts'),
                DB::raw('COUNT(DISTINCT CASE WHEN performance_records.is_weak_area = 1 THEN performance_records.student_id END) as students_affected')
            )
            ->get()
            ->map(function ($r) {
                $r->accuracy = $r->attempts > 0 ? (int) round($r->correct / $r->attempts * 100) : 0;
                return $r;
            })
            ->sortBy('accuracy')
            ->take(5)
            ->values();

        // The class-wide misconception behind each weak topic - the wrong
        // choice picked most often across every student, on the question
        // most students get wrong within it.
        $misses = $this->topMissedQuestions(
            $topics->map(fn ($t) => [null, $t->topic_id])->all(),
            'NULL'
        );

        return $topics->map(function ($t) use ($misses) {
            $miss = $misses['class:' . $t->topic_id] ?? null;
            $t->why = $t->students_affected > 0
                ? "{$t->students_affected} student" . ($t->students_affected === 1 ? '' : 's') . " flagged weak"
                : "Class average below par over {$t->attempts} attempts";
            $t->miss = $miss ? "Most often pick \"{$miss['choice']}\" on \"{$miss['question']}\"" : null;
            return $t;
        });
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
     * Export the currently filtered student list as a CSV (opens in Excel).
     */
    public function export(Request $request)
    {
        $filters = $this->filters($request, $this->assignedSubjectIds(Auth::user()));
        $rows = $this->studentRows($filters);

        $search = trim((string) $filters['search']);
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name']), $needle)
                || str_contains(mb_strtolower($r['email']), $needle))->values();
        }
        $rows = $this->sortRows($rows, $filters['sort']);

        $filename = 'student-performance-' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($out, ['Student', 'Email', 'Avg Score (%)', 'Questions Attempted', 'Quizzes', 'Subjects Covered', 'Trend', 'At Risk', 'Last Active']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['name'],
                    $r['email'],
                    $r['score'],
                    $r['attempted'],
                    $r['quizzes'],
                    implode(' / ', $r['subjects']),
                    ucfirst($r['trend']),
                    $r['at_risk'] ? 'Yes' : 'No',
                    $r['last_active'] ? Carbon::parse($r['last_active'])->format('Y-m-d H:i') : '',
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Drop a real "review reminder" notification into each at-risk student's
     * inbox AND email them over SMTP. Used by both "Send Report" (whole
     * filtered set) and "Send Reminder to All" (at-risk only) buttons.
     */
    public function sendReminder(Request $request)
    {
        $filters = $this->filters($request, $this->assignedSubjectIds(Auth::user()));
        $rows = $this->studentRows($filters);

        $scope = $request->input('scope', 'at_risk');
        $targets = $scope === 'all' ? $rows : $rows->where('at_risk', true);

        if ($targets->isEmpty()) {
            return back()->with('status', 'No students matched - nothing to send.');
        }

        $sender = Auth::user();
        $now = now();
        $subjectLine = $scope === 'all'
            ? 'Your performance check-in from ' . $sender->name
            : 'A study reminder from ' . $sender->name;

        $message = fn ($r) => $scope === 'all'
            ? "Your instructor {$sender->name} sent you a performance check-in. Keep up your reviews!"
            : "Your instructor {$sender->name} noticed you may need extra practice. Try a focused review of your weak topics.";

        $payload = $targets->map(fn ($r) => [
            'recipient_id'   => $r['id'],
            'type'           => 'review_reminder',
            'title'          => 'Study reminder from your instructor',
            'message'        => $message($r),
            'is_read'        => 0,
            'reference_type' => 'faculty',
            'reference_id'   => $sender->id,
            'created_at'     => $now,
        ])->all();

        DB::table('notifications')->insert($payload);

        // Send the actual SMTP email to each targeted student. Failures (bad
        // address, mail server down, etc.) are logged and skipped so one
        // bad recipient doesn't block the rest of the batch.
        $emailed = 0;
        $failed = 0;
        foreach ($targets as $r) {
            if (empty($r['email'])) {
                continue;
            }

            try {
                Mail::to($r['email'])->send(new StudentReminderMail(
                    studentName: $r['name'],
                    facultyName: $sender->name,
                    subjectLine: $subjectLine,
                    bodyMessage: $message($r),
                    ctaUrl: route('dashboard'),
                    ctaLabel: 'Go to CPACE',
                ));
                $emailed++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Failed to send student reminder email', [
                    'student_id' => $r['id'],
                    'email'      => $r['email'],
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $count = count($payload);
        $status = "Reminder sent to {$count} student" . ($count === 1 ? '' : 's') . " ({$emailed} email" . ($emailed === 1 ? '' : 's') . ' delivered' . ($failed ? ", {$failed} failed" : '') . ').';

        return back()->with('status', $status);
    }

    /** Two-letter initials from a name. */
    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
        return mb_strtoupper($first . $last) ?: '?';
    }

    /** Deterministic avatar colour from the name. */
    private function avatarColor(string $name): string
    {
        $palette = ['#e8567d', '#3b82f6', '#27ae60', '#f59e0b', '#8b5cf6', '#17a2b8', '#c0392b'];
        return $palette[abs(crc32($name)) % count($palette)];
    }
}
