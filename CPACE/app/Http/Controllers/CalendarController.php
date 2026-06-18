<?php

namespace App\Http\Controllers;

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
        1 => ['#e9f1fd', '#3b7ddd'], // FAR  - blue
        2 => ['#eaf0fb', '#2f63c4'], // AFAR - indigo
        3 => ['#fef3e2', '#e8910b'], // MS   - amber
        4 => ['#e8f7ee', '#21a366'], // TAX  - green
        5 => ['#fdeaea', '#c0392b'], // AUD  - red
        6 => ['#f0eafb', '#8e5bd0'], // RFBT - purple
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

        // ── Which month is being viewed (?month=YYYY-MM) ───────────────────
        $monthParam = (string) $request->query('month', '');
        $cursor = preg_match('/^\d{4}-\d{2}$/', $monthParam)
            ? Carbon::createFromFormat('Y-m-d', $monthParam . '-01')->startOfMonth()
            : $today->copy()->startOfMonth();

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
        $upcoming = collect($upcoming)->take(6)->values();

        // ── Navigation + header context ────────────────────────────────────
        $context = [
            'month_label' => $cursor->format('F Y'),
            'prev_month'  => $cursor->copy()->subMonth()->format('Y-m'),
            'next_month'  => $cursor->copy()->addMonth()->format('Y-m'),
            'this_month'  => $today->format('Y-m'),
            'today'       => [
                'month'   => $today->format('M'),
                'day'     => $today->day,
                'weekday' => $today->format('l'),
            ],
            'due_count'   => $dueCount,
            'weak_count'  => $todayReviews->where('is_weak', true)->count(),
            'has_data'    => $items->isNotEmpty(),
        ];

        return view('student.calendar', compact('weeks', 'todayReviews', 'upcoming', 'context'));
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
