<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpacedRepetitionScheduler;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarApiController extends Controller
{
    private const SUBJECT_PALETTE = [
        1 => ['#e9f1fd', '#3b7ddd'],
        2 => ['#eaf0fb', '#2f63c4'],
        3 => ['#fef3e2', '#e8910b'],
        4 => ['#e8f7ee', '#21a366'],
        5 => ['#fdeaea', '#c0392b'],
        6 => ['#f0eafb', '#8e5bd0'],
    ];

    public function __construct(
        private SpacedRepetitionScheduler $scheduler,
        private WeaknessDetector $weakness,
    ) {}

    public function index(Request $request)
    {
        $studentId = Auth::id();
        $today     = Carbon::today();

        $this->ensureSchedule($studentId);

        $monthParam = (string) $request->query('month', '');
        $cursor = preg_match('/^\d{4}-\d{2}$/', $monthParam)
            ? Carbon::createFromFormat('Y-m-d', $monthParam . '-01')->startOfMonth()
            : $today->copy()->startOfMonth();

        $topicMeta = $this->topicMeta($studentId);

        $items = DB::table('spaced_repetition_items as sr')
            ->join('questions as q', 'q.id', '=', 'sr.question_id')
            ->join('topics as t', 't.id', '=', 'q.topic_id')
            ->join('subjects as s', 's.id', '=', 't.subject_id')
            ->where('sr.student_id', $studentId)
            ->select('sr.next_review_at', 'q.topic_id', 't.name as topic', 's.code as subject_code', 's.id as subject_id')
            ->get();

        $byDate = [];
        foreach ($items as $it) {
            $date = Carbon::parse($it->next_review_at)->toDateString();
            $key  = (int) $it->topic_id;
            if (! isset($byDate[$date][$key])) {
                $byDate[$date][$key] = $this->makeEvent($it, $topicMeta);
            }
            $byDate[$date][$key]['count']++;
        }

        // Build month grid
        $gridStart = $cursor->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $weeks = [];
        $day   = $gridStart->copy();
        while ($day <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $ds = $day->toDateString();
                $week[] = [
                    'day'      => $day->day,
                    'date'     => $ds,
                    'muted'    => $day->month !== $cursor->month,
                    'is_today' => $day->isSameDay($today),
                    'events'   => array_values(collect($byDate[$ds] ?? [])->sortByDesc('priority_rank')->all()),
                ];
                $day->addDay();
            }
            $weeks[] = $week;
        }

        // Today's due reviews
        $dueByTopic = [];
        foreach ($byDate as $date => $topics) {
            if (Carbon::parse($date)->gt($today)) continue;
            foreach ($topics as $topicId => $event) {
                if (! isset($dueByTopic[$topicId])) {
                    $dueByTopic[$topicId] = $event;
                    $dueByTopic[$topicId]['count'] = 0;
                }
                $dueByTopic[$topicId]['count'] += $event['count'];
            }
        }
        $todayReviews = collect($dueByTopic)->sortByDesc('priority_rank')->values();

        // Upcoming 7 days
        $upcoming = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = $today->copy()->addDays($i);
            $ds   = $date->toDateString();
            foreach (($byDate[$ds] ?? []) as $event) {
                $event['date_label'] = $date->format('M j');
                $upcoming[] = $event;
            }
        }

        return response()->json([
            'month_label'  => $cursor->format('F Y'),
            'prev_month'   => $cursor->copy()->subMonth()->format('Y-m'),
            'next_month'   => $cursor->copy()->addMonth()->format('Y-m'),
            'weeks'        => $weeks,
            'today_reviews'=> $todayReviews,
            'upcoming'     => array_slice($upcoming, 0, 6),
            'due_count'    => $todayReviews->sum('count'),
            'has_data'     => $items->isNotEmpty(),
        ]);
    }

    private function makeEvent(object $it, array $topicMeta): array
    {
        $meta   = $topicMeta[(int) $it->topic_id] ?? null;
        $isWeak = (bool) ($meta['is_weak'] ?? false);
        $acc    = $meta['accuracy'] ?? null;
        [$bg, $dot] = self::SUBJECT_PALETTE[(int) $it->subject_id] ?? ['#eef0f2', '#7a7a7a'];

        if ($isWeak)                          { $priority = 'High';   $rank = 3; }
        elseif ($acc !== null && $acc < 75)   { $priority = 'Medium'; $rank = 2; }
        else                                  { $priority = 'Low';    $rank = 1; }

        return ['topic' => $it->topic, 'subject_code' => $it->subject_code, 'subject_id' => (int) $it->subject_id, 'count' => 0, 'priority' => $priority, 'priority_rank' => $rank, 'is_weak' => $isWeak, 'bg' => $bg, 'dot' => $dot];
    }

    private function topicMeta(int $studentId): array
    {
        return DB::table('performance_records as pr')
            ->join('topics as t', 't.id', '=', 'pr.topic_id')
            ->join('subjects as s', 's.id', '=', 't.subject_id')
            ->where('pr.student_id', $studentId)
            ->select('pr.topic_id', 'pr.correct_count', 'pr.total_attempts', 'pr.consecutive_wrong', 't.name as topic', 's.code as subject_code', 's.id as subject_id')
            ->get()
            ->mapWithKeys(function ($r) {
                [$isWeak] = $this->weakness->evaluate($r);
                return [(int) $r->topic_id => ['topic' => $r->topic, 'subject_code' => $r->subject_code, 'subject_id' => (int) $r->subject_id, 'accuracy' => $r->total_attempts > 0 ? (int) round($r->correct_count / $r->total_attempts * 100) : null, 'is_weak' => $isWeak]];
            })
            ->all();
    }

    private function ensureSchedule(int $studentId): void
    {
        $records = DB::table('performance_records')->where('student_id', $studentId)->where('total_attempts', '>', 0)->get();
        foreach ($records as $record) { $this->weakness->sync($studentId, (int) $record->topic_id); }

        if (DB::table('spaced_repetition_items')->where('student_id', $studentId)->exists() || $records->isEmpty()) return;

        $rows = [];
        foreach ($records as $record) {
            $accuracy   = $record->correct_count / max($record->total_attempts, 1);
            $reviewedOn = $record->last_attempted ? Carbon::parse($record->last_attempted) : Carbon::now();
            $questions  = DB::table('questions')->where('topic_id', $record->topic_id)->where('is_active', true)->orderBy('id')->get(['id', 'difficulty']);
            if ($questions->isEmpty()) continue;

            $rememberCount = (int) round($accuracy * $questions->count());
            foreach ($questions->values() as $i => $q) {
                $state = $i < $rememberCount
                    ? $this->scheduler->mature(1 + ($i % 4), $this->scheduler->qualityFromAnswer(true, $q->difficulty))
                    : $this->scheduler->next(['repetition_num' => 0, 'ease_factor' => SpacedRepetitionScheduler::EF_DEFAULT, 'interval_days' => 0], $this->scheduler->qualityFromAnswer(false, $q->difficulty), $reviewedOn);

                $interval = (int) $state['interval_days'];
                $rows[] = ['student_id' => $studentId, 'question_id' => $q->id, 'repetition_num' => $state['repetition_num'], 'ease_factor' => $state['ease_factor'], 'interval_days' => $interval, 'quality_score' => $state['quality_score'] ?? null, 'last_reviewed' => $reviewedOn->toDateString(), 'next_review_at' => $reviewedOn->copy()->addDays($interval)->toDateString()];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('spaced_repetition_items')->insert($chunk);
        }
    }
}
