<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiTutorService;
use App\Services\StreakService;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiTutorController extends Controller
{
    /**
     * Chat endpoint for the floating AI Tutor widget. The client sends the
     * whole conversation each turn; the service handles the Gemini →
     * OpenRouter fallback.
     */
    public function chat(Request $request, AiTutorService $ai)
    {
        $data = $request->validate([
            'messages'           => ['required', 'array', 'min:1', 'max:30'],
            'messages.*.role'    => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:6000'],
        ]);

        try {
            $result = $ai->chat($data['messages']);

            return response()->json(['ok' => true] + $result);
        } catch (\Throwable $e) {
            Log::error('AI Tutor: all providers failed.', ['error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'The AI tutor is unavailable right now. Please try again in a moment.',
            ], 503);
        }
    }

    /**
     * AI-generated insights for the student Performance page. The student's
     * real quiz stats are summarised server-side and sent to the AI, which
     * returns render-ready insight cards. Results are cached per student and
     * keyed by a hash of the data, so the AI only re-runs when the student's
     * performance actually changes.
     */
    public function performanceInsights(Request $request, AiTutorService $ai, WeaknessDetector $weakness, StreakService $streak)
    {
        $studentId = Auth::id();
        $summary   = $this->performanceSummary($studentId, $weakness, $streak);

        $cacheKey = 'ai_perf_insights.' . $studentId . '.' . md5(json_encode($summary));

        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        try {
            $result = Cache::remember($cacheKey, now()->addHours(12), fn () => $ai->performanceInsights($summary));

            return response()->json(['ok' => true] + $result);
        } catch (\Throwable $e) {
            Log::error('AI Tutor: performance insights failed.', ['error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'AI insights are unavailable right now.',
            ], 503);
        }
    }

    /**
     * Compact snapshot of the student's performance for the AI prompt. Uses
     * the same sources as the Performance page (completed non-training
     * sessions + performance_records) so the AI sees what the student sees.
     */
    private function performanceSummary(int $studentId, WeaknessDetector $weakness, StreakService $streak): array
    {
        $sessions = fn () => DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at');

        $window = function ($from = null) use ($sessions) {
            $q = $sessions();
            if ($from) {
                $q->where('started_at', '>=', $from);
            }
            $r = $q->selectRaw('COALESCE(SUM(total_items),0) attempted, COALESCE(SUM(correct_answers),0) correct, COALESCE(SUM(duration_secs),0) duration')->first();

            return ['attempted' => (int) $r->attempted, 'correct' => (int) $r->correct, 'duration' => (int) $r->duration];
        };

        $all      = $window();
        $thisWeek = $window(now()->subDays(7));

        $acc = fn ($w) => $w['attempted'] > 0 ? (int) round($w['correct'] / $w['attempted'] * 100) : null;

        // Per-topic accuracy, flagged with the same weak-area rule as the
        // Performance page and Spaced Repetition Calendar.
        $topics = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->where('performance_records.student_id', $studentId)
            ->where('performance_records.total_attempts', '>', 0)
            ->select('topics.name as topic', 'subjects.code as subject', 'performance_records.correct_count', 'performance_records.total_attempts', 'performance_records.consecutive_wrong')
            ->get()
            ->map(function ($r) use ($weakness) {
                [$isWeak] = $weakness->evaluate($r);

                return [
                    'topic'    => $r->topic,
                    'subject'  => $r->subject,
                    'accuracy' => (int) round((int) $r->correct_count / max((int) $r->total_attempts, 1) * 100),
                    'attempts' => (int) $r->total_attempts,
                    'is_weak'  => $isWeak,
                ];
            });

        $subjects = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('performance_records', function ($join) use ($studentId) {
                $join->on('performance_records.topic_id', '=', 'topics.id')
                     ->where('performance_records.student_id', '=', $studentId);
            })
            ->groupBy('subjects.id', 'subjects.code', 'subjects.passing_threshold')
            ->orderBy('subjects.id')
            ->select('subjects.code', 'subjects.passing_threshold', DB::raw('COALESCE(SUM(performance_records.correct_count),0) correct'), DB::raw('COALESCE(SUM(performance_records.total_attempts),0) attempts'))
            ->get()
            ->map(fn ($r) => [
                'subject'           => $r->code,
                'accuracy'          => $r->attempts > 0 ? (int) round($r->correct / $r->attempts * 100) : null,
                'passing_threshold' => (int) $r->passing_threshold,
            ]);

        $modes = $sessions()
            ->groupBy('mode')
            ->select('mode', DB::raw('COUNT(*) quizzes'))
            ->pluck('quizzes', 'mode');

        return [
            'overall_accuracy_pct'      => $acc($all),
            'this_week_accuracy_pct'    => $acc($thisWeek),
            'total_questions_attempted' => $all['attempted'],
            'total_wrong'               => max(0, $all['attempted'] - $all['correct']),
            'avg_seconds_per_question'  => $all['attempted'] > 0 ? (int) round($all['duration'] / $all['attempted']) : null,
            'study_streak_days'         => $streak->current($studentId),
            'weak_topics'               => $topics->where('is_weak', true)->sortBy('accuracy')->take(6)->values(),
            'strong_topics'             => $topics->where('accuracy', '>=', 75)->where('attempts', '>=', WeaknessDetector::MIN_ATTEMPTS)->sortByDesc('accuracy')->take(4)->values(),
            'accuracy_by_subject'       => $subjects,
            'quizzes_by_mode'           => $modes,
        ];
    }
}
