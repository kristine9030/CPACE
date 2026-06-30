<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\Subject;
use App\Services\QuestionParaphraser;
use App\Services\SpacedRepetitionScheduler;
use App\Services\StreakService;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizApiController extends Controller
{
    private const QUIZ_LENGTH             = 10;
    private const MAX_QUIZ_LENGTH         = 100;
    private const MODES                   = ['adaptive', 'topic', 'timed', 'challenge'];
    private const TIMED_SECONDS_PER_QUESTION = 60;

    public function index()
    {
        $studentId = Auth::id();

        $subjects = Subject::where('is_active', true)->orderBy('id')->get()->map(function ($subject) {
            return [
                'id'             => $subject->id,
                'code'           => $subject->code,
                'name'           => $subject->name,
                'color'          => $subject->color,
                'icon'           => $subject->icon,
                'question_count' => Question::where('is_active', true)
                    ->whereIn('topic_id', $subject->topics()->pluck('id'))
                    ->count(),
            ];
        });

        $agg = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->selectRaw('COALESCE(SUM(correct_count),0) c, COALESCE(SUM(total_attempts),0) t')
            ->first();
        $accuracy = ($agg && $agg->t > 0) ? (int) round($agg->c / $agg->t * 100) : 0;

        $mastery = $this->masteryBreakdown($studentId);

        $totalAttempted = (int) DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->where('session_type', '!=', 'training')
            ->sum('total_items');

        $recentSessions = QuizSession::with('subject')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->where('session_type', '!=', 'training')
            ->orderByDesc('completed_at')
            ->limit(4)
            ->get()
            ->map(fn ($s) => $this->sessionPayload($s));

        return response()->json([
            'subjects'        => $subjects,
            'accuracy'        => $accuracy,
            'total_attempted' => $totalAttempted,
            'recent_sessions' => $recentSessions,
            'mastery'         => $mastery,
        ]);
    }

    public function history(Request $request)
    {
        $studentId = Auth::id();
        $page      = max(1, (int) $request->query('page', 1));

        $sessions = QuizSession::with('subject')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->paginate(15, ['*'], 'page', $page);

        return response()->json([
            'data'         => $sessions->map(fn ($s) => $this->sessionPayload($s)),
            'current_page' => $sessions->currentPage(),
            'last_page'    => $sessions->lastPage(),
            'total'        => $sessions->total(),
        ]);
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'subject_id'   => 'required|exists:subjects,id',
            'mode'         => 'nullable|string',
            'count'        => 'required|integer|min:1',
            'session_type' => 'nullable|string',
        ]);

        $mode        = in_array($data['mode'] ?? null, self::MODES, true) ? $data['mode'] : 'adaptive';
        $sessionType = in_array($data['session_type'] ?? null, ['training', 'testing'], true)
            ? $data['session_type']
            : 'testing';

        $count    = max(1, min((int) $data['count'], self::MAX_QUIZ_LENGTH));
        $topicIds = DB::table('topics')->where('subject_id', $data['subject_id'])->pluck('id');

        [$questionIds, $focusTopicId] = $this->selectQuestions($mode, $topicIds, Auth::id(), $count);

        if ($questionIds->isEmpty()) {
            return response()->json(['message' => 'No questions are available for that subject yet.'], 422);
        }

        $session = QuizSession::create([
            'student_id'      => Auth::id(),
            'session_type'    => $sessionType,
            'mode'            => $mode,
            'subject_id'      => $data['subject_id'],
            'topic_id'        => $focusTopicId,
            'started_at'      => now(),
            'total_items'     => $questionIds->count(),
            'correct_answers' => 0,
        ]);

        foreach ($questionIds as $qid) {
            QuizAnswer::create([
                'session_id'  => $session->id,
                'question_id' => $qid,
                'answered_at' => now(),
            ]);
        }

        $timeLimit = $mode === 'timed' ? $questionIds->count() * self::TIMED_SECONDS_PER_QUESTION : null;

        return response()->json([
            'session_id' => $session->id,
            'time_limit' => $timeLimit,
        ], 201);
    }

    public function take(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if ($session->completed_at) {
            return response()->json(['completed' => true, 'session_id' => $session->id]);
        }

        $questionIds = $session->answers()->pluck('question_id');
        $questions   = $this->presentQuestions(
            Question::with(['choices', 'variants'])->whereIn('id', $questionIds)->get(),
            $session->id
        );

        $timeLimit = $session->mode === 'timed'
            ? $questions->count() * self::TIMED_SECONDS_PER_QUESTION
            : null;

        return response()->json([
            'session'    => $this->sessionPayload($session->loadMissing('subject')),
            'questions'  => $questions->map(fn ($q) => $this->questionPayload($q)),
            'time_limit' => $timeLimit,
        ]);
    }

    public function submit(Request $request, int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if ($session->completed_at) {
            return response()->json(['already_completed' => true]);
        }

        $submitted = $request->input('answers', []);

        $questions = Question::with('choices')
            ->whereIn('id', $session->answers()->pluck('question_id'))
            ->get()
            ->keyBy('id');

        $countsTowardProgress = $session->session_type !== 'training';

        $correctCount  = 0;
        $topicTally    = [];
        $answerResults = [];

        DB::transaction(function () use ($session, $questions, $submitted, $countsTowardProgress, &$correctCount, &$topicTally, &$answerResults) {
            foreach ($questions as $question) {
                $correctChoiceId = optional($question->choices->firstWhere('is_correct', true))->id;
                $selected        = $submitted[$question->id] ?? null;
                $selected        = $selected !== null ? (int) $selected : null;
                $isCorrect       = $selected !== null && $selected === $correctChoiceId;

                if ($isCorrect) { $correctCount++; }

                $answerResults[] = [
                    'question_id' => $question->id,
                    'difficulty'  => $question->difficulty,
                    'correct'     => $isCorrect,
                ];

                $session->answers()
                    ->where('question_id', $question->id)
                    ->update([
                        'selected_choice' => $selected,
                        'is_correct'      => $isCorrect,
                        'answered_at'     => now(),
                    ]);

                $topicTally[$question->topic_id] ??= ['attempts' => 0, 'correct' => 0];
                $topicTally[$question->topic_id]['attempts']++;
                $topicTally[$question->topic_id]['correct'] += $isCorrect ? 1 : 0;
            }

            $total = $questions->count();
            $session->update([
                'completed_at'    => now(),
                'correct_answers' => $correctCount,
                'score_percent'   => $total > 0 ? round($correctCount / $total * 100, 2) : 0,
                'duration_secs'   => max(0, (int) $session->started_at->diffInSeconds(now())),
            ]);

            if ($countsTowardProgress) {
                $this->updatePerformanceRecords($session->student_id, $topicTally);
                $this->awardPoints($session->student_id, $correctCount, $session->mode);
            }
        });

        app(StreakService::class)->refresh($session->student_id);

        if ($countsTowardProgress) {
            try {
                app(SpacedRepetitionScheduler::class)->recordAnswers($session->student_id, $answerResults);
                app(WeaknessDetector::class)->syncMany($session->student_id, array_keys($topicTally));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['session_id' => $session->id, 'score_percent' => $session->score_percent]);
    }

    public function results(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if (! $session->completed_at) {
            return response()->json(['message' => 'Quiz not yet completed.'], 422);
        }

        $answers   = $session->answers()->get()->keyBy('question_id');
        $questions = $this->presentQuestions(
            Question::with(['choices', 'variants'])->whereIn('id', $answers->keys())->get(),
            $session->id
        );

        $session->loadMissing('subject');

        return response()->json([
            'session'   => $this->sessionPayload($session),
            'questions' => $questions->map(fn ($q) => array_merge(
                $this->questionPayload($q),
                [
                    'selected_choice' => $answers[$q->id]->selected_choice ?? null,
                    'is_correct'      => $answers[$q->id]->is_correct ?? null,
                ]
            )),
        ]);
    }

    public function cancel(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if (! $session->completed_at) {
            $session->answers()->delete();
            $session->delete();
        }

        return response()->json(['ok' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function sessionPayload(QuizSession $s): array
    {
        return [
            'id'              => $s->id,
            'mode'            => $s->mode,
            'session_type'    => $s->session_type,
            'total_items'     => $s->total_items,
            'correct_answers' => $s->correct_answers,
            'score_percent'   => $s->score_percent,
            'duration_secs'   => $s->duration_secs,
            'started_at'      => $s->started_at,
            'completed_at'    => $s->completed_at,
            'subject_code'    => $s->subject->code ?? null,
            'subject_name'    => $s->subject->name ?? null,
        ];
    }

    private function questionPayload($q): array
    {
        return [
            'id'            => $q->id,
            'question_text' => $q->question_text,
            'question_type' => $q->question_type,
            'difficulty'    => $q->difficulty,
            'explanation'   => $q->explanation,
            'choices'       => $q->choices->map(fn ($c) => [
                'id'           => $c->id,
                'choice_text'  => $c->choice_text,
                'choice_label' => $c->choice_label ?? null,
            ]),
        ];
    }

    private function presentQuestions($questions, int $sessionId)
    {
        $labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        $questions->transform(function ($question) use ($sessionId, $labels) {
            if ($question->question_type !== 'true_false') {
                $choices = $question->choices
                    ->sortBy(fn ($c) => crc32($sessionId . '-' . $question->id . '-' . $c->id))
                    ->values();
            } else {
                $choices = $question->choices->values();
            }

            foreach ($choices as $idx => $choice) {
                $choice->choice_label = $labels[$idx] ?? (string) ($idx + 1);
            }

            $question->setRelation('choices', $choices);
            $question->question_text = QuestionParaphraser::forDisplay(
                $question,
                crc32($sessionId . '-text-' . $question->id),
                $question->question_type
            );

            return $question;
        });

        return $questions
            ->sortBy(fn ($q) => crc32($sessionId . '-q-' . $q->id))
            ->values();
    }

    private function ownedSession(int $sessionId): QuizSession
    {
        return QuizSession::where('id', $sessionId)
            ->where('student_id', Auth::id())
            ->firstOrFail();
    }

    private function selectQuestions(string $mode, $topicIds, int $studentId, int $count): array
    {
        $base = fn () => Question::where('is_active', true)->whereIn('topic_id', $topicIds);

        switch ($mode) {
            case 'topic':
                $focusTopicId = $this->pickFocusTopic($topicIds, $studentId);
                $ids = $base()->where('topic_id', $focusTopicId)->inRandomOrder()->limit($count)->pluck('id');
                return [$ids, $focusTopicId];

            case 'challenge':
                $hard = $base()->whereIn('difficulty', ['difficult', 'moderate'])->inRandomOrder()->limit($count)->pluck('id');
                return [$this->fill($hard, $base(), $count), null];

            case 'adaptive':
                $weakTopicIds = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $topicIds)
                    ->where('is_weak_area', true)
                    ->pluck('topic_id');

                $weak = $weakTopicIds->isNotEmpty()
                    ? $base()->whereIn('topic_id', $weakTopicIds)->inRandomOrder()->limit($count)->pluck('id')
                    : collect();
                return [$this->fill($weak, $base(), $count), null];

            default:
                return [$base()->inRandomOrder()->limit($count)->pluck('id'), null];
        }
    }

    private function fill($ids, $query, int $target)
    {
        $ids = collect($ids);
        if ($ids->count() >= $target) return $ids->take($target)->values();
        $extra = $query->whereNotIn('id', $ids->all())->inRandomOrder()->limit($target - $ids->count())->pluck('id');
        return $ids->concat($extra)->values();
    }

    private function pickFocusTopic($topicIds, int $studentId): ?int
    {
        $weak = DB::table('performance_records')
            ->where('student_id', $studentId)->whereIn('topic_id', $topicIds)
            ->where('is_weak_area', true)->orderByDesc('consecutive_wrong')->value('topic_id');

        if ($weak) return (int) $weak;

        $topic = Question::where('is_active', true)->whereIn('topic_id', $topicIds)
            ->groupBy('topic_id')->selectRaw('topic_id, COUNT(*) as c')
            ->orderByDesc('c')->inRandomOrder()->value('topic_id');

        return $topic ? (int) $topic : (int) $topicIds->first();
    }

    private function masteryBreakdown(int $studentId): array
    {
        $records = DB::table('performance_records')
            ->where('student_id', $studentId)->where('total_attempts', '>', 0)
            ->get(['correct_count', 'total_attempts']);

        $strong = $medium = $weak = 0;
        foreach ($records as $r) {
            $acc = $r->correct_count / max($r->total_attempts, 1);
            if ($acc >= 0.8) $strong++;
            elseif ($acc >= 0.6) $medium++;
            else $weak++;
        }

        $total = $strong + $medium + $weak;
        if ($total === 0) return ['has_data' => false, 'strong' => 0, 'medium' => 0, 'weak' => 0];

        return [
            'has_data' => true,
            'strong'   => (int) round($strong / $total * 100),
            'medium'   => (int) round($medium / $total * 100),
            'weak'     => max(0, 100 - (int) round($strong / $total * 100) - (int) round($medium / $total * 100)),
        ];
    }

    private function updatePerformanceRecords(int $studentId, array $topicTally): void
    {
        $weakness = app(WeaknessDetector::class);
        foreach ($topicTally as $topicId => $tally) {
            $record = DB::table('performance_records')->where('student_id', $studentId)->where('topic_id', $topicId)->first();
            $wrong  = $tally['attempts'] - $tally['correct'];

            if ($record) {
                $totalAttempts    = $record->total_attempts + $tally['attempts'];
                $correctCount     = $record->correct_count + $tally['correct'];
                $consecutiveWrong = $wrong === 0 ? 0 : $record->consecutive_wrong + $wrong;
                [$isWeak]         = $weakness->evaluate((object) compact('total_attempts', 'correct_count') + ['consecutive_wrong' => $consecutiveWrong]);
                // reuse evaluated values
                [$isWeak] = $weakness->evaluate((object) ['total_attempts' => $totalAttempts, 'correct_count' => $correctCount, 'consecutive_wrong' => $consecutiveWrong]);
                DB::table('performance_records')->where('id', $record->id)->update([
                    'total_attempts'    => $totalAttempts,
                    'correct_count'     => $correctCount,
                    'consecutive_wrong' => $consecutiveWrong,
                    'is_weak_area'      => $isWeak,
                    'last_attempted'    => now(),
                ]);
            } else {
                [$isWeak] = $weakness->evaluate((object) ['total_attempts' => $tally['attempts'], 'correct_count' => $tally['correct'], 'consecutive_wrong' => $wrong]);
                DB::table('performance_records')->insert([
                    'student_id'        => $studentId,
                    'topic_id'          => $topicId,
                    'total_attempts'    => $tally['attempts'],
                    'correct_count'     => $tally['correct'],
                    'consecutive_wrong' => $wrong,
                    'is_weak_area'      => $isWeak,
                    'last_attempted'    => now(),
                ]);
            }
        }
    }

    private function awardPoints(int $studentId, int $correctCount, string $mode): void
    {
        $points = (int) round($correctCount * 10 * ($mode === 'challenge' ? 1.5 : 1.0));
        if ($points <= 0) return;

        DB::table('points_log')->insert(['student_id' => $studentId, 'points' => $points, 'reason' => 'quiz_completed', 'created_at' => now()]);
        DB::table('student_profiles')->where('user_id', $studentId)->increment('total_points', $points);
    }
}
