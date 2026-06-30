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
        $filters = $this->filters($request);

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
            'subjects'     => Subject::orderBy('id')->get(),
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
     * Normalise the request filters into the shape the rest of the controller
     * works with (and the view echoes back into the controls).
     */
    private function filters(Request $request): array
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

        $from = match ($period) {
            '7'  => Carbon::now()->subDays(7),
            '30' => Carbon::now()->subDays(30),
            '90' => Carbon::now()->subDays(90),
            default => null,
        };

        return [
            'search'  => (string) $request->input('search', ''),
            'subject' => $subjectId,
            'period'  => $period,
            'sort'    => $sort,
            'from'    => $from,
        ];
    }

    /**
     * Build one row per student that has completed activity within the current
     * period / subject window: average score, quiz count, subjects covered,
     * trend vs the previous week, last-active time and a weak-area flag.
     */
    private function studentRows(array $filters)
    {
        $from      = $filters['from'];
        $subjectId = $filters['subject'];

        // Reusable base query honouring the period + subject filters.
        $base = fn () => DB::table('quiz_sessions')
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->when($from, fn ($q) => $q->where('started_at', '>=', $from))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId));

        // Headline aggregate per student.
        $agg = $base()
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
        $trend = $this->trendAccuracy($subjectId, $now, $studentIds);

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
    private function trendAccuracy(?int $subjectId, Carbon $now, array $studentIds): array
    {
        $window = function (Carbon $start, Carbon $end) use ($subjectId, $studentIds) {
            return DB::table('quiz_sessions')
                ->where('session_type', '!=', 'training')
                ->whereNotNull('completed_at')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('started_at', [$start, $end])
                ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
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
                'performance_records.correct_count',
                'performance_records.total_attempts',
                'performance_records.consecutive_wrong',
                'topics.name as topic',
                'subjects.code as subject_code',
                'subjects.name as subject_name'
            )
            ->get()
            ->groupBy('student_id');

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

            // Weak topics, using the shared detector rule.
            $weak = $recs->filter(function ($r) {
                [$isWeak] = $this->weakness->evaluate($r);
                return $isWeak;
            })->map(function ($r) {
                $att = (int) $r->total_attempts;
                return [
                    'topic'    => $r->topic,
                    'subject'  => $r->subject_code,
                    'accuracy' => $att > 0 ? (int) round($r->correct_count / $att * 100) : 0,
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
     * The class's weakest topics: aggregate accuracy across every student in the
     * current subject/period window, lowest 5 (with a real sample behind them).
     */
    private function classWeakTopics(array $filters)
    {
        $subjectId = $filters['subject'];

        return DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->when($subjectId, fn ($q) => $q->where('subjects.id', $subjectId))
            ->groupBy('topics.id', 'topics.name', 'subjects.code')
            ->havingRaw('SUM(performance_records.total_attempts) >= 5')
            ->select(
                'topics.name as topic',
                'subjects.code as subject_code',
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts')
            )
            ->get()
            ->map(function ($r) {
                $r->accuracy = $r->attempts > 0 ? (int) round($r->correct / $r->attempts * 100) : 0;
                return $r;
            })
            ->sortBy('accuracy')
            ->take(5)
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
     * Export the currently filtered student list as a CSV (opens in Excel).
     */
    public function export(Request $request)
    {
        $filters = $this->filters($request);
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
     * inbox. Used by both "Send Report" (whole filtered set) and "Send Reminder
     * to All" (at-risk only) buttons.
     */
    public function sendReminder(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->studentRows($filters);

        $scope = $request->input('scope', 'at_risk');
        $targets = $scope === 'all' ? $rows : $rows->where('at_risk', true);

        if ($targets->isEmpty()) {
            return back()->with('status', 'No students matched - nothing to send.');
        }

        $sender = Auth::user();
        $now = now();
        $payload = $targets->map(fn ($r) => [
            'recipient_id'   => $r['id'],
            'type'           => 'review_reminder',
            'title'          => 'Study reminder from your instructor',
            'message'        => $scope === 'all'
                ? "Your instructor {$sender->name} sent you a performance check-in. Keep up your reviews!"
                : "Your instructor {$sender->name} noticed you may need extra practice. Try a focused review of your weak topics.",
            'is_read'        => 0,
            'reference_type' => 'faculty',
            'reference_id'   => $sender->id,
            'created_at'     => $now,
        ])->all();

        DB::table('notifications')->insert($payload);

        $count = count($payload);
        return back()->with('status', "Reminder sent to {$count} student" . ($count === 1 ? '' : 's') . '.');
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
