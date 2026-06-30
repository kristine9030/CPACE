<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StreakService;
use App\Services\WeaknessDetector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceApiController extends Controller
{
    public function __construct(private WeaknessDetector $weakness) {}

    public function index()
    {
        $studentId = Auth::id();
        $now       = Carbon::now();
        $weekStart = $now->copy()->subDays(7);
        $prevStart = $now->copy()->subDays(14);

        $sessions = fn () => DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at');

        $sumWindow = function ($from = null, $to = null) use ($sessions) {
            $q = $sessions();
            if ($from) $q->where('started_at', '>=', $from);
            if ($to)   $q->where('started_at', '<', $to);
            $r = $q->selectRaw('COALESCE(SUM(total_items),0) attempted, COALESCE(SUM(correct_answers),0) correct, COALESCE(SUM(duration_secs),0) duration')->first();
            return ['attempted' => (int) $r->attempted, 'correct' => (int) $r->correct, 'duration' => (int) $r->duration];
        };

        $all     = $sumWindow();
        $week    = $sumWindow($weekStart, null);
        $prev    = $sumWindow($prevStart, $weekStart);

        $totalAttempted  = $all['attempted'];
        $totalCorrect    = $all['correct'];
        $overallAccuracy = $totalAttempted > 0 ? (int) round($totalCorrect / $totalAttempted * 100) : 0;

        $accThisWeek = $week['attempted'] > 0 ? (int) round($week['correct'] / $week['attempted'] * 100) : 0;
        $accPrevWeek = $prev['attempted'] > 0 ? (int) round($prev['correct'] / $prev['attempted'] * 100) : 0;

        $avgSecs = $totalAttempted > 0 ? (int) round($all['duration'] / $totalAttempted) : 0;

        $stats = [
            'accuracy'        => $overallAccuracy,
            'accuracy_delta'  => $accThisWeek - $accPrevWeek,
            'attempted'       => $totalAttempted,
            'attempted_delta' => $week['attempted'] - $prev['attempted'],
            'correct'         => $totalCorrect,
            'correct_delta'   => $week['correct'] - $prev['correct'],
            'wrong'           => max(0, $totalAttempted - $totalCorrect),
            'avg_secs'        => $avgSecs,
        ];

        $streakDays = app(StreakService::class)->current($studentId);

        // 7-day daily accuracy
        $dailySeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $day  = $now->copy()->subDays($i)->startOfDay();
            $next = $day->copy()->addDay();
            $att  = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('total_items');
            $cor  = (int) $sessions()->whereBetween('started_at', [$day, $next])->sum('correct_answers');
            $dailySeries[] = [
                'label'    => $day->format('M j'),
                'accuracy' => $att > 0 ? (int) round($cor / $att * 100) : 0,
                'has_data' => $att > 0,
            ];
        }

        $topicStats = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->where('performance_records.student_id', $studentId)
            ->where('performance_records.total_attempts', '>', 0)
            ->select('topics.name as topic', 'subjects.code as subject_code', 'performance_records.correct_count', 'performance_records.total_attempts', 'performance_records.consecutive_wrong')
            ->get()
            ->map(function ($r) {
                [$isWeak] = $this->weakness->evaluate($r);
                return [
                    'topic'        => $r->topic,
                    'subject_code' => $r->subject_code,
                    'correct'      => (int) $r->correct_count,
                    'attempts'     => (int) $r->total_attempts,
                    'accuracy'     => $r->total_attempts > 0 ? (int) round($r->correct_count / $r->total_attempts * 100) : 0,
                    'is_weak'      => $isWeak,
                ];
            });

        $strengths  = $topicStats->filter(fn ($t) => $t['attempts'] >= WeaknessDetector::MIN_ATTEMPTS && $t['accuracy'] >= 75)->sortByDesc('accuracy')->values();
        $weaknesses = $topicStats->filter(fn ($t) => $t['is_weak'])->sortBy('accuracy')->values();

        $subjectAccuracy = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('performance_records', function ($j) use ($studentId) {
                $j->on('performance_records.topic_id', '=', 'topics.id')->where('performance_records.student_id', '=', $studentId);
            })
            ->groupBy('subjects.id', 'subjects.code', 'subjects.name', 'subjects.color')
            ->orderBy('subjects.id')
            ->select('subjects.code', 'subjects.name', 'subjects.color', DB::raw('COALESCE(SUM(performance_records.correct_count),0) as correct'), DB::raw('COALESCE(SUM(performance_records.total_attempts),0) as attempts'))
            ->get()
            ->map(fn ($r) => ['code' => $r->code, 'name' => $r->name, 'color' => $r->color, 'accuracy' => $r->attempts > 0 ? (int) round($r->correct / $r->attempts * 100) : 0]);

        $byQuizType = $this->byQuizType($studentId);

        return response()->json([
            'stats'           => $stats,
            'streak_days'     => $streakDays,
            'daily_series'    => $dailySeries,
            'strengths'       => $strengths->values(),
            'weaknesses'      => $weaknesses->values(),
            'subject_accuracy'=> $subjectAccuracy,
            'by_quiz_type'    => $byQuizType,
        ]);
    }

    private function byQuizType(int $studentId): array
    {
        $byMode = DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->groupBy('mode')
            ->select('mode', DB::raw('COUNT(*) as sessions'), DB::raw('COALESCE(SUM(total_items),0) as attempted'), DB::raw('COALESCE(SUM(correct_answers),0) as correct'))
            ->get()->keyBy('mode');

        $modes = ['adaptive' => 'Adaptive', 'topic' => 'Topic', 'timed' => 'Timed', 'challenge' => 'Challenge'];
        $rows = [];
        foreach ($modes as $mode => $label) {
            $att = (int) ($byMode[$mode]->attempted ?? 0);
            $cor = (int) ($byMode[$mode]->correct ?? 0);
            $rows[] = ['mode' => $mode, 'label' => $label, 'sessions' => (int) ($byMode[$mode]->sessions ?? 0), 'attempted' => $att, 'correct' => $cor, 'accuracy' => $att > 0 ? (int) round($cor / $att * 100) : 0];
        }
        return $rows;
    }
}
