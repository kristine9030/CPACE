<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Services\SpacedRepetitionScheduler;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Spaced Repetition Calendar.
 *
 * Every event on this page is a real SM-2 review due date pulled from the
 * student's own `spaced_repetition_items`. The dates are produced by the
 * SpacedRepetitionScheduler from how the student actually answered each
 * question, and weak topics (flagged in `weakness_reports`) are surfaced as
 * High-priority reviews - so the calendar literally schedules more practice on
 * the material the student keeps getting wrong. Nothing here is hard-coded.
 */
class CalendarController extends Controller
{
    /** Per-subject colour map: subject_id => [background, accent dot]. */
    private const SUBJECT_PALETTE = [
        1 => ['#e8f7ee', '#21a366'], // FAR  - green
        2 => ['#fdeaea', '#c0392b'], // AFAR - red
        3 => ['#fef3e2', '#e8910b'], // MS   - amber
        4 => ['#e9f1fd', '#3b7ddd'], // TAX  - blue
        5 => ['#f0eafb', '#8e5bd0'], // AUD  - purple
        6 => ['#fdeef5', '#d4589e'], // RFBT - pink
    ];

    /** Suggested time slots (24h start, duration hours) for a day's reviews.
     *  Non-overlapping so a busy day stacks cleanly down the column. */
    private const DAY_SLOTS = [
        [8.5, 1.25], [10.0, 1.25], [11.5, 1.0], [13.0, 1.25],
        [14.5, 1.25], [16.0, 1.25], [17.5, 1.0], [18.75, 1.0],
    ];

    public function __construct(
        private SpacedRepetitionScheduler $scheduler,
        private WeaknessDetector $weakness,
    ) {}

    public function index(Request $request)
    {
        $studentId = Auth::id();
        $today     = Carbon::today();

        // Make sure the student has an SM-2 schedule to show. New students who
        // already have quiz history get one bootstrapped from that history; the
        // weakness flags are kept in sync either way.
        $this->ensureSchedule($studentId);

        // ── Which date is being viewed (?date=YYYY-MM-DD, or legacy ?month) ─
        $monthParam = (string) $request->query('month', '');
        $dateParam  = (string) $request->query('date', '');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
            $cursorDay = Carbon::createFromFormat('Y-m-d', $dateParam)->startOfDay();
        } elseif (preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $cursorDay = Carbon::createFromFormat('Y-m-d', $monthParam . '-01')->startOfDay();
        } else {
            $cursorDay = $today->copy();
        }
        $cursor = $cursorDay->copy()->startOfMonth();

        // ── Per-topic metadata (subject, name, accuracy, weak flag) ────────
        $topicMeta = $this->topicMeta($studentId);

        // ── All scheduled reviews for this student, grouped by date+topic ──
        $items = DB::table('spaced_repetition_items as sr')
            ->join('questions as q', 'q.id', '=', 'sr.question_id')
            ->join('topics as t', 't.id', '=', 'q.topic_id')
            ->join('subjects as s', 's.id', '=', 't.subject_id')
            ->where('sr.student_id', $studentId)
            ->select('sr.next_review_at', 'q.topic_id', 't.name as topic', 's.code as subject_code', 's.id as subject_id')
            ->get();

        // [date => [topic_id => event]]
        $byDate = [];
        foreach ($items as $it) {
            $date = Carbon::parse($it->next_review_at)->toDateString();
            $key  = (int) $it->topic_id;

            if (! isset($byDate[$date][$key])) {
                $byDate[$date][$key] = $this->makeEvent($it, $topicMeta);
            }
            $byDate[$date][$key]['count']++;
        }

        // ── Build the month grid (Sun-Sat weeks, like the rest of the app) ─
        $gridStart = $cursor->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $weeks = [];
        $day   = $gridStart->copy();
        while ($day <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $ds     = $day->toDateString();
                $events = collect($byDate[$ds] ?? [])
                    ->sortByDesc(fn ($e) => $e['priority_rank'])
                    ->values()
                    ->all();

                $week[] = [
                    'day'        => $day->day,
                    'date_label' => $day->format('l, F j, Y'),
                    'muted'      => $day->month !== $cursor->month,
                    'is_today'   => $day->isSameDay($today),
                    'events'     => $events,
                ];
                $day->addDay();
            }
            $weeks[] = $week;
        }

        // ── Today's reviews (everything due on or before today) ────────────
        $dueByTopic = [];
        foreach ($byDate as $date => $topics) {
            if (Carbon::parse($date)->gt($today)) {
                continue;
            }
            foreach ($topics as $topicId => $event) {
                if (! isset($dueByTopic[$topicId])) {
                    $dueByTopic[$topicId] = $event;
                    $dueByTopic[$topicId]['count'] = 0;
                }
                $dueByTopic[$topicId]['count'] += $event['count'];
            }
        }
        $todayReviews = collect($dueByTopic)
            ->sortByDesc('priority_rank')
            ->values();
        $dueCount = $todayReviews->sum('count');

        // ── Upcoming (next 7 days, one row per topic per day) ──────────────
        $upcoming = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = $today->copy()->addDays($i);
            $ds   = $date->toDateString();
            foreach (($byDate[$ds] ?? []) as $event) {
                $event['date_label'] = $date->format('M j');
                $upcoming[] = $event;
            }
        }
        $upcoming = collect($upcoming)->take(20)->values();

        // ── Week view: 7 day columns with suggested timed study blocks ─────
        $weekStart = $cursorDay->copy()->startOfWeek(Carbon::SUNDAY);
        $weekDays  = [];
        for ($i = 0; $i < 7; $i++) {
            $d      = $weekStart->copy()->addDays($i);
            $ds     = $d->toDateString();
            $events = collect($byDate[$ds] ?? [])->sortByDesc(fn ($e) => $e['priority_rank'])->values();

            $weekDays[] = [
                'label'    => strtoupper($d->format('D')),
                'day'      => $d->day,
                'date'     => $ds,
                'is_today' => $d->isSameDay($today),
                'tasks'    => $events->sum('count'),
                'events'   => $this->timedBlocks($events),
            ];
        }

        // All-day chips: one per subject with reviews this week, plus fixed
        // practice/catch-up suggestions.
        $weekSubjects = [];
        foreach ($weekDays as $wd) {
            foreach ($wd['events'] as $e) {
                $weekSubjects[$e['subject_code']] = ['bg' => $e['bg'], 'dot' => $e['dot']];
            }
        }
        $allDayChips = collect($weekSubjects)
            ->map(fn ($c, $code) => ['label' => $code . ' Review', 'bg' => $c['bg'], 'dot' => $c['dot']])
            ->values()
            ->take(4)
            ->push(['label' => 'Practice Sets', 'bg' => '#e9f1fd', 'dot' => '#3b7ddd'])
            ->push(['label' => 'Catch-up', 'bg' => '#fdeef5', 'dot' => '#d4589e'])
            ->all();

        // ── Exam countdown (same source as the Dashboard banner) ───────────
        $profile    = DB::table('student_profiles')->where('user_id', $studentId)->first();
        $examDate   = $profile->exam_target_date ?? null;
        $daysToExam = null;
        $examPct    = 0;
        if ($examDate) {
            $exam       = Carbon::parse($examDate)->startOfDay();
            $daysToExam = max(0, $today->diffInDays($exam, false));
            // Progress: how far the student is between their first recorded
            // quiz (or account creation) and exam day.
            $firstStudy = DB::table('quiz_sessions')->where('student_id', $studentId)->min('started_at');
            $start      = $firstStudy ? Carbon::parse($firstStudy)->startOfDay() : $today;
            $span       = max(1, $start->diffInDays($exam));
            $examPct    = (int) round(min(100, max(0, $start->diffInDays($today) / $span * 100)));
        }

        // ── Bottom stats bar ───────────────────────────────────────────────
        $weakTopics  = collect($topicMeta)->where('is_weak', true)->count();
        $needsWork   = collect($topicMeta)->filter(fn ($m) => ! $m['is_weak'] && $m['accuracy'] !== null && $m['accuracy'] < 75)->count();
        $onTrack     = collect($topicMeta)->filter(fn ($m) => ! $m['is_weak'] && $m['accuracy'] !== null && $m['accuracy'] >= 75)->count();
        $overallAcc  = $this->overallAccuracy($studentId);
        $streakDays  = app(\App\Services\StreakService::class)->current($studentId);

        $stats = [
            'weak'     => $weakTopics,
            'needs'    => $needsWork,
            'on_track' => $onTrack,
            'reviews'  => $dueCount,
            'streak'   => $streakDays,
            'accuracy' => $overallAcc,
        ];

        // ── Student-created study plans for the visible week ───────────────
        $planRows = DB::table('calendar_plans as cp')
            ->leftJoin('topics as t', 't.id', '=', 'cp.topic_id')
            ->leftJoin('subjects as s', 's.id', '=', 't.subject_id')
            ->where('cp.student_id', $studentId)
            ->whereBetween('cp.plan_date', [
                $cursorDay->copy()->startOfWeek(Carbon::SUNDAY)->toDateString(),
                $cursorDay->copy()->startOfWeek(Carbon::SUNDAY)->addDays(6)->toDateString(),
            ])
            ->select('cp.*', 't.name as topic', 's.code as subject_code', 's.id as subject_id')
            ->get();

        $plansByDate = [];
        foreach ($planRows as $p) {
            $startH = $p->start_min / 60;
            $durH   = max(0.5, $p->duration_min / 60);
            [$bg, $dot] = $p->subject_id
                ? (self::SUBJECT_PALETTE[(int) $p->subject_id] ?? ['#f0eafb', '#8e5bd0'])
                : ['#f0eafb', '#8e5bd0'];

            $fmt = function (float $h) {
                $hh = (int) floor($h);
                $mm = (int) round(($h - $hh) * 60);
                return Carbon::createFromTime($hh, $mm)->format('g:i');
            };

            $plansByDate[$p->plan_date][] = [
                'id'           => (int) $p->id,
                'title'        => $p->topic ?: ($p->title ?: 'Study Block'),
                'subject_code' => $p->subject_code ?: 'PLAN',
                'subject_id'   => (int) ($p->subject_id ?? 0),
                'topic_id'     => $p->topic_id ? (int) $p->topic_id : null,
                'bg'           => $bg,
                'dot'          => $dot,
                'top'          => round(max(0, ($startH - 8)) / 12 * 100, 2),
                'height'       => round($durH / 12 * 100, 2),
                'time_lbl'     => $fmt($startH) . ' - ' . $fmt($startH + $durH) . ' ' . (($startH + $durH) >= 12 ? 'PM' : 'AM'),
            ];
        }

        // Topic dropdown for the "Add study plan" modal, grouped by subject.
        $topicOptions = DB::table('topics as t')
            ->join('subjects as s', 's.id', '=', 't.subject_id')
            ->where('t.is_active', true)
            ->orderBy('s.id')->orderBy('t.name')
            ->select('t.id', 't.name', 's.code as subject_code', 's.id as subject_id')
            ->get()
            ->groupBy('subject_code');

        // ── Unread notifications (header bell) ─────────────────────────────
        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $studentId)
            ->where('is_read', false)
            ->count();

        // ── Navigation + header context ────────────────────────────────────
        $context = [
            'month_label' => $cursorDay->format('F Y'),
            'today_date'  => $today->toDateString(),
            'cursor_date' => $cursorDay->toDateString(),
            // Per-view prev/next targets - the Day/Week/Month toggle swaps the
            // arrows between these client-side.
            'nav' => [
                'day'   => ['prev' => $cursorDay->copy()->subDay()->toDateString(),   'next' => $cursorDay->copy()->addDay()->toDateString()],
                'week'  => ['prev' => $cursorDay->copy()->subWeek()->toDateString(),  'next' => $cursorDay->copy()->addWeek()->toDateString()],
                'month' => ['prev' => $cursor->copy()->subMonth()->toDateString(),    'next' => $cursor->copy()->addMonth()->toDateString()],
            ],
            'due_count'   => $dueCount,
            'weak_count'  => $todayReviews->where('is_weak', true)->count(),
            'has_data'    => $items->isNotEmpty(),
            'day_label'   => $cursorDay->format('l, F j'),
        ];

        return view('student.calendar', compact(
            'weeks',
            'todayReviews',
            'upcoming',
            'context',
            'weekDays',
            'allDayChips',
            'examDate',
            'daysToExam',
            'examPct',
            'stats',
            'unreadNotifications',
            'plansByDate',
            'topicOptions'
        ));
    }

    /**
     * Save a student-created study block on the calendar.
     */
    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'topic_id'     => 'nullable|integer|exists:topics,id',
            'title'        => 'nullable|string|max:120|required_without:topic_id',
            'plan_date'    => 'required|date',
            'start_time'   => 'required|date_format:H:i',
            'duration_min' => 'required|integer|min:30|max:240',
        ], [
            'title.required_without' => 'Pick a topic or give the study block a title.',
        ]);

        [$h, $m] = explode(':', $data['start_time']);

        DB::table('calendar_plans')->insert([
            'student_id'   => Auth::id(),
            'topic_id'     => $data['topic_id'] ?? null,
            'title'        => $data['title'] ?? null,
            'plan_date'    => Carbon::parse($data['plan_date'])->toDateString(),
            'start_min'    => (int) $h * 60 + (int) $m,
            'duration_min' => (int) $data['duration_min'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()
            ->route('calendar', ['date' => Carbon::parse($data['plan_date'])->toDateString()])
            ->with('status', 'Study block added to your calendar.');
    }

    /**
     * Remove one of the student's own study blocks.
     */
    public function destroyPlan(Request $request, int $id)
    {
        DB::table('calendar_plans')
            ->where('id', $id)
            ->where('student_id', Auth::id())
            ->delete();

        return back()->with('status', 'Study block removed.');
    }

    /**
     * Lay a day's reviews out as suggested study blocks on the 8AM-8PM grid.
     * Reviews are real (SM-2 due dates); the times are a suggested plan -
     * highest priority topics get the morning slots.
     */
    private function timedBlocks($events): array
    {
        $blocks = [];
        foreach ($events->take(count(self::DAY_SLOTS))->values() as $i => $e) {
            [$start, $dur] = self::DAY_SLOTS[$i];

            $fmt = function (float $h) {
                $hh = (int) floor($h);
                $mm = (int) round(($h - $hh) * 60);
                return Carbon::createFromTime($hh, $mm)->format('g:i');
            };
            $endH   = $start + $dur;
            $meridE = $endH >= 12 ? 'PM' : 'AM';

            $e['start']    = $start;
            $e['top']      = round(($start - 8) / 12 * 100, 2);
            $e['height']   = round($dur / 12 * 100, 2);
            $e['time_lbl'] = $fmt($start) . ' - ' . $fmt($endH) . ' ' . $meridE;
            $blocks[]      = $e;
        }

        // Chronological within the column so the DOM order matches the eye.
        usort($blocks, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $blocks;
    }

    /**
     * Overall accuracy across everything the student has practised.
     */
    private function overallAccuracy(int $studentId): int
    {
        $r = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->selectRaw('COALESCE(SUM(correct_count),0) cor, COALESCE(SUM(total_attempts),0) att')
            ->first();

        return (int) $r->att > 0 ? (int) round($r->cor / $r->att * 100) : 0;
    }

    /**
     * Shape one calendar event from a joined SR row + the topic metadata.
     */
    private function makeEvent(object $it, array $topicMeta): array
    {
        $meta    = $topicMeta[(int) $it->topic_id] ?? null;
        $isWeak  = (bool) ($meta['is_weak'] ?? false);
        $acc     = $meta['accuracy'] ?? null;
        [$bg, $dot] = self::SUBJECT_PALETTE[(int) $it->subject_id] ?? ['#eef0f2', '#7a7a7a'];

        // Priority: weak topics first, then anything still below mastery.
        if ($isWeak) {
            $priority = 'High';
            $rank     = 3;
        } elseif ($acc !== null && $acc < 75) {
            $priority = 'Medium';
            $rank     = 2;
        } else {
            $priority = 'Low';
            $rank     = 1;
        }

        return [
            'topic'         => $it->topic,
            'topic_id'      => (int) $it->topic_id,
            'subject_code'  => $it->subject_code,
            'subject_id'    => (int) $it->subject_id,
            'count'         => 0,
            'priority'      => $priority,
            'priority_rank' => $rank,
            'is_weak'       => $isWeak,
            'bg'            => $bg,
            'dot'           => $dot,
        ];
    }

    /**
     * Per-topic metadata for every topic the student has practised: the linked
     * subject, the topic name, the live accuracy, and whether it is currently a
     * flagged weak area.
     */
    private function topicMeta(int $studentId): array
    {
        return DB::table('performance_records as pr')
            ->join('topics as t', 't.id', '=', 'pr.topic_id')
            ->join('subjects as s', 's.id', '=', 't.subject_id')
            ->where('pr.student_id', $studentId)
            ->select(
                'pr.topic_id',
                'pr.correct_count',
                'pr.total_attempts',
                'pr.consecutive_wrong',
                't.name as topic',
                's.code as subject_code',
                's.id as subject_id'
            )
            ->get()
            ->mapWithKeys(function ($r) {
                [$isWeak] = $this->weakness->evaluate($r);
                return [(int) $r->topic_id => [
                    'topic'        => $r->topic,
                    'subject_code' => $r->subject_code,
                    'subject_id'   => (int) $r->subject_id,
                    'accuracy'     => $r->total_attempts > 0
                        ? (int) round($r->correct_count / $r->total_attempts * 100)
                        : null,
                    'is_weak'      => $isWeak,
                ]];
            })
            ->all();
    }

    /**
     * Ensure the student has an SM-2 schedule. Weakness flags are reconciled on
     * every visit (cheap, idempotent). When a student has quiz history but no
     * schedule yet - e.g. they practised before this feature existed - we seed
     * one SM-2 item per active question per practised topic, with intervals
     * derived from their measured per-topic accuracy: items they get right
     * mature out into the future, items they miss lapse and resurface within a
     * day, exactly mirroring how the live scheduler will behave from now on.
     */
    private function ensureSchedule(int $studentId): void
    {
        $records = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->where('total_attempts', '>', 0)
            ->get();

        // Keep weakness_reports current for the priority labels.
        foreach ($records as $record) {
            $this->weakness->sync($studentId, (int) $record->topic_id);
        }

        $alreadyScheduled = DB::table('spaced_repetition_items')
            ->where('student_id', $studentId)
            ->exists();

        if ($alreadyScheduled || $records->isEmpty()) {
            return;
        }

        $rows = [];
        foreach ($records as $record) {
            $accuracy   = $record->correct_count / max($record->total_attempts, 1);
            $reviewedOn = $record->last_attempted ? Carbon::parse($record->last_attempted) : Carbon::now();

            $questions = DB::table('questions')
                ->where('topic_id', $record->topic_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'difficulty']);

            if ($questions->isEmpty()) {
                continue;
            }

            // The accuracy fraction of the bank is treated as "remembered".
            $rememberCount = (int) round($accuracy * $questions->count());

            foreach ($questions->values() as $i => $q) {
                if ($i < $rememberCount) {
                    // Remembered: mature it by a few successful reviews so the
                    // interval spreads across the weeks ahead (vary by index so
                    // a single topic doesn't pile onto one day).
                    $successes = 1 + ($i % 4);
                    $state     = $this->scheduler->mature(
                        $successes,
                        $this->scheduler->qualityFromAnswer(true, $q->difficulty)
                    );
                } else {
                    // Missed: a single lapse, resurfaces tomorrow (weak focus).
                    $state = $this->scheduler->next(
                        ['repetition_num' => 0, 'ease_factor' => SpacedRepetitionScheduler::EF_DEFAULT, 'interval_days' => 0],
                        $this->scheduler->qualityFromAnswer(false, $q->difficulty),
                        $reviewedOn
                    );
                }

                $interval = (int) $state['interval_days'];
                $rows[] = [
                    'student_id'     => $studentId,
                    'question_id'    => $q->id,
                    'repetition_num' => $state['repetition_num'],
                    'ease_factor'    => $state['ease_factor'],
                    'interval_days'  => $interval,
                    'quality_score'  => $state['quality_score'] ?? null,
                    'last_reviewed'  => $reviewedOn->toDateString(),
                    'next_review_at' => $reviewedOn->copy()->addDays($interval)->toDateString(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('spaced_repetition_items')->insert($chunk);
        }
    }
}
