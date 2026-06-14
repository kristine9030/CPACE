<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\Subject;
use App\Services\QuestionParaphraser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /** Default number of questions served when the student doesn't choose. */
    private const QUIZ_LENGTH = 10;

    /** Hard ceiling on how many questions a single sitting may contain. */
    private const MAX_QUIZ_LENGTH = 100;

    /** Practice modes the student can pick on the landing page. */
    private const MODES = ['adaptive', 'topic', 'timed', 'challenge'];

    /** Seconds the student gets per question in Timed mode. */
    private const TIMED_SECONDS_PER_QUESTION = 45;

    /**
     * Adaptive quiz landing page with live per-subject question counts.
     */
    public function index()
    {
        $studentId = Auth::id();

        $subjects = Subject::orderBy('id')->get()->map(function ($subject) {
            $subject->question_count = Question::where('is_active', true)
                ->whereIn('topic_id', $subject->topics()->pluck('id'))
                ->count();
            return $subject;
        });

        // Overall mastery + accuracy for the stats sidebar.
        $agg = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->selectRaw('COALESCE(SUM(correct_count),0) c, COALESCE(SUM(total_attempts),0) t')
            ->first();
        $accuracy = ($agg && $agg->t > 0) ? (int) round($agg->c / $agg->t * 100) : 0;

        // Real Strong / Medium / Weak breakdown from per-topic performance.
        $mastery = $this->masteryBreakdown($studentId);

        // "Questions Attempted" = every question served across the student's
        // completed quizzes (total_items), so a skipped question still counts.
        // Matches the Dashboard and Performance pages.
        $totalAttempted = (int) DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->sum('total_items');

        $recentSessions = QuizSession::with('subject')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->limit(4)
            ->get();

        return view('student.adaptive-quizzes', compact('subjects', 'accuracy', 'totalAttempted', 'recentSessions', 'mastery'));
    }

    /**
     * Classify the student's attempted topics into Strong / Medium / Weak based
     * on their per-topic accuracy, and return each band as a percentage of the
     * topics they have practised (plus the slice angles for the donut chart).
     */
    private function masteryBreakdown(int $studentId): array
    {
        $records = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->where('total_attempts', '>', 0)
            ->get(['correct_count', 'total_attempts']);

        $strong = $medium = $weak = 0;
        foreach ($records as $r) {
            $acc = $r->correct_count / max($r->total_attempts, 1);
            if ($acc >= 0.8) {
                $strong++;
            } elseif ($acc >= 0.6) {
                $medium++;
            } else {
                $weak++;
            }
        }

        $total = $strong + $medium + $weak;
        if ($total === 0) {
            return ['has_data' => false, 'strong' => 0, 'medium' => 0, 'weak' => 0,
                    'strong_deg' => 0, 'medium_deg' => 0];
        }

        $strongPct = (int) round($strong / $total * 100);
        $mediumPct = (int) round($medium / $total * 100);

        return [
            'has_data'   => true,
            'strong'     => $strongPct,
            'medium'     => $mediumPct,
            'weak'       => max(0, 100 - $strongPct - $mediumPct),
            // Donut slice boundaries (degrees) using exact fractions.
            'strong_deg' => round($strong / $total * 360, 1),
            'medium_deg' => round(($strong + $medium) / $total * 360, 1),
        ];
    }

    /**
     * Start a new quiz: pick questions for the chosen subject and create the session.
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'mode'       => 'nullable|string',
            'count'      => 'required|integer|min:1',
        ], [
            'count.required' => 'Please choose how many questions before starting a quiz.',
            'count.min'      => 'Please choose at least 1 question.',
        ]);

        $mode = in_array($data['mode'] ?? null, self::MODES, true) ? $data['mode'] : 'adaptive';

        // How many questions the student wants this sitting (capped to the max).
        $count = max(1, min((int) $data['count'], self::MAX_QUIZ_LENGTH));

        $topicIds = DB::table('topics')->where('subject_id', $data['subject_id'])->pluck('id');

        // Mode decides HOW the questions are chosen from the faculty's bank.
        [$questionIds, $focusTopicId] = $this->selectQuestions($mode, $topicIds, Auth::id(), $count);

        if ($questionIds->isEmpty()) {
            return back()->with('error', 'No questions are available for that subject yet.');
        }

        $session = QuizSession::create([
            'student_id'      => Auth::id(),
            'session_type'    => 'testing',
            'mode'            => $mode,
            'subject_id'      => $data['subject_id'],
            'topic_id'        => $focusTopicId,
            'started_at'      => now(),
            'total_items'     => $questionIds->count(),
            'correct_answers' => 0,
        ]);

        // Placeholder answer rows lock in which questions were served (reload-safe).
        foreach ($questionIds as $qid) {
            QuizAnswer::create([
                'session_id'  => $session->id,
                'question_id' => $qid,
                'answered_at' => now(),
            ]);
        }

        return redirect()->route('quiz.take', $session->id);
    }

    /**
     * Cancel an in-progress quiz: delete the session and its placeholder answers
     * so an abandoned, never-submitted quiz does not count as answered. A quiz
     * that was already completed is left untouched.
     */
    public function cancel(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if (! $session->completed_at) {
            $session->answers()->delete();
            $session->delete();
        }

        return redirect()->route('adaptive-quizzes');
    }

    /**
     * Pick the question IDs for a new quiz based on the chosen mode.
     *
     * Returns [Collection $questionIds, ?int $focusTopicId]. The faculty's
     * stored questions are never modified - only WHICH ones are served and
     * (later, at render time) the order they appear in.
     */
    private function selectQuestions(string $mode, $topicIds, int $studentId, int $count = self::QUIZ_LENGTH): array
    {
        // Fresh query builder each call - inRandomOrder()/limit() mutate state.
        $base = fn () => Question::where('is_active', true)->whereIn('topic_id', $topicIds);

        switch ($mode) {
            case 'topic':
                // Focus the whole quiz on one topic so the student drills a
                // single competency. Prefer a weak topic, else the topic with
                // the most questions, else a random one.
                $focusTopicId = $this->pickFocusTopic($topicIds, $studentId);
                $ids = $base()
                    ->where('topic_id', $focusTopicId)
                    ->inRandomOrder()
                    ->limit($count)
                    ->pluck('id');

                return [$ids, $focusTopicId];

            case 'challenge':
                // Hardest questions first; only fall back to easier ones if a
                // subject doesn't have enough difficult/moderate items.
                $hard = $base()
                    ->whereIn('difficulty', ['difficult', 'moderate'])
                    ->inRandomOrder()
                    ->limit($count)
                    ->pluck('id');
                $ids = $this->fill($hard, $base(), $count);

                return [$ids, null];

            case 'adaptive':
                // Weight the quiz toward the student's weak topics, then fill
                // the rest with a random spread across the subject.
                $weakTopicIds = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $topicIds)
                    ->where('is_weak_area', true)
                    ->pluck('topic_id');

                $weak = $weakTopicIds->isNotEmpty()
                    ? $base()->whereIn('topic_id', $weakTopicIds)->inRandomOrder()->limit($count)->pluck('id')
                    : collect();
                $ids = $this->fill($weak, $base(), $count);

                return [$ids, null];

            case 'timed':
            default:
                // Broad random draw - the challenge here is speed, not selection.
                $ids = $base()
                    ->inRandomOrder()
                    ->limit($count)
                    ->pluck('id');

                return [$ids, null];
        }
    }

    /**
     * Top up a partial question list with more random questions from the pool,
     * never repeating an already-chosen id, until it reaches $target (or the
     * pool runs dry).
     */
    private function fill($ids, $query, int $target)
    {
        $ids = collect($ids);
        if ($ids->count() >= $target) {
            return $ids->take($target)->values();
        }

        $extra = $query
            ->whereNotIn('id', $ids->all())
            ->inRandomOrder()
            ->limit($target - $ids->count())
            ->pluck('id');

        return $ids->concat($extra)->values();
    }

    /**
     * Choose the topic a Topic-mode quiz should concentrate on.
     */
    private function pickFocusTopic($topicIds, int $studentId): ?int
    {
        $weak = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->whereIn('topic_id', $topicIds)
            ->where('is_weak_area', true)
            ->orderByDesc('consecutive_wrong')
            ->value('topic_id');

        if ($weak) {
            return (int) $weak;
        }

        // Otherwise the topic with the most active questions (tie broken randomly).
        $topic = Question::where('is_active', true)
            ->whereIn('topic_id', $topicIds)
            ->groupBy('topic_id')
            ->selectRaw('topic_id, COUNT(*) as c')
            ->orderByDesc('c')
            ->inRandomOrder()
            ->value('topic_id');

        return $topic ? (int) $topic : (int) $topicIds->first();
    }

    /**
     * Show the quiz questions for an in-progress session.
     */
    public function take(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if ($session->completed_at) {
            return redirect()->route('quiz.results', $session->id);
        }

        $questionIds = $session->answers()->pluck('question_id');
        $questions = $this->presentQuestions(
            Question::with(['choices', 'variants'])->whereIn('id', $questionIds)->get(),
            $session->id
        );

        $timeLimit = $session->mode === 'timed'
            ? $questions->count() * self::TIMED_SECONDS_PER_QUESTION
            : null;

        return view('student.take-quiz', compact('session', 'questions', 'timeLimit'));
    }

    /**
     * Grade a submitted quiz, persist answers and update performance analytics.
     */
    public function submit(Request $request, int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if ($session->completed_at) {
            return redirect()->route('quiz.results', $session->id);
        }

        $submitted = $request->input('answers', []); // [question_id => choice_id]

        $questions = Question::with('choices')
            ->whereIn('id', $session->answers()->pluck('question_id'))
            ->get()
            ->keyBy('id');

        $correctCount = 0;
        // Per-topic tally for performance records: [topic_id => ['attempts'=>, 'correct'=>]]
        $topicTally = [];

        DB::transaction(function () use ($session, $questions, $submitted, &$correctCount, &$topicTally) {
            foreach ($questions as $question) {
                $correctChoiceId = optional($question->choices->firstWhere('is_correct', true))->id;
                $selected = $submitted[$question->id] ?? null;
                $selected = $selected !== null ? (int) $selected : null;
                $isCorrect = $selected !== null && $selected === $correctChoiceId;

                if ($isCorrect) {
                    $correctCount++;
                }

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

            $this->updatePerformanceRecords($session->student_id, $topicTally);
            $this->awardPoints($session->student_id, $correctCount, $session->mode);
        });

        return redirect()->route('quiz.results', $session->id);
    }

    /**
     * Show the graded results with per-question feedback.
     */
    public function results(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if (! $session->completed_at) {
            return redirect()->route('quiz.take', $session->id);
        }

        $answers = $session->answers()->get()->keyBy('question_id');
        $questions = $this->presentQuestions(
            Question::with(['choices', 'variants'])->whereIn('id', $answers->keys())->get(),
            $session->id
        );

        $session->loadMissing('subject');

        return view('student.quiz-results', compact('session', 'questions', 'answers'));
    }

    /**
     * Re-order questions and their choices AND re-word the question text for
     * display, so a student who retakes a quiz never sees the same sequence,
     * the answer in the same slot, or the exact same wording - this is what
     * stops rote memorisation and pushes understanding. The faculty's stored
     * data is untouched; everything here is a deterministic function of the
     * session id, so a single session always renders identically (the quiz page
     * and its results match) while a different session (a retake) varies.
     */
    private function presentQuestions($questions, int $sessionId)
    {
        $labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        $questions->transform(function ($question) use ($sessionId, $labels) {
            // True/False keeps its natural order; everything else is shuffled.
            if ($question->question_type !== 'true_false') {
                $choices = $question->choices
                    ->sortBy(fn ($c) => crc32($sessionId . '-' . $question->id . '-' . $c->id))
                    ->values();
            } else {
                $choices = $question->choices->values();
            }

            // Relabel A/B/C/D by the position the student actually sees, so the
            // correct answer's letter changes between attempts too.
            foreach ($choices as $idx => $choice) {
                $choice->choice_label = $labels[$idx] ?? (string) ($idx + 1);
            }

            $question->setRelation('choices', $choices);

            // Re-word the stem on this in-memory copy only. forDisplay() picks
            // from any stored faculty/AI variants first, then falls back to the
            // rule-based paraphraser - the faculty's stored text is untouched.
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

    /**
     * Fetch a session that belongs to the authenticated student or 404.
     */
    private function ownedSession(int $sessionId): QuizSession
    {
        return QuizSession::where('id', $sessionId)
            ->where('student_id', Auth::id())
            ->firstOrFail();
    }

    /**
     * Upsert per-topic performance records used by the dashboard & weakness detection.
     */
    private function updatePerformanceRecords(int $studentId, array $topicTally): void
    {
        foreach ($topicTally as $topicId => $tally) {
            $record = DB::table('performance_records')
                ->where('student_id', $studentId)
                ->where('topic_id', $topicId)
                ->first();

            $wrong = $tally['attempts'] - $tally['correct'];

            if ($record) {
                $totalAttempts = $record->total_attempts + $tally['attempts'];
                $correctCount  = $record->correct_count + $tally['correct'];
                // Reset the wrong-streak on a clean session, otherwise extend it.
                $consecutiveWrong = $wrong === 0 ? 0 : $record->consecutive_wrong + $wrong;

                // Note: accuracy_rate is a STORED generated column in the
                // database (computed from correct_count / total_attempts), so it
                // is never written here - the DB keeps it in sync automatically.
                DB::table('performance_records')
                    ->where('id', $record->id)
                    ->update([
                        'total_attempts'    => $totalAttempts,
                        'correct_count'     => $correctCount,
                        'consecutive_wrong' => $consecutiveWrong,
                        'is_weak_area'      => ($correctCount / max($totalAttempts, 1)) < 0.6 || $consecutiveWrong >= 3,
                        'last_attempted'    => now(),
                    ]);
            } else {
                DB::table('performance_records')->insert([
                    'student_id'        => $studentId,
                    'topic_id'          => $topicId,
                    'total_attempts'    => $tally['attempts'],
                    'correct_count'     => $tally['correct'],
                    'consecutive_wrong' => $wrong,
                    'is_weak_area'      => ($tally['correct'] / max($tally['attempts'], 1)) < 0.6 || $wrong >= 3,
                    'last_attempted'    => now(),
                ]);
            }
        }
    }

    /**
     * Award gamification points for a completed quiz.
     * Challenge mode pays a bonus for taking on harder questions.
     */
    private function awardPoints(int $studentId, int $correctCount, string $mode = 'adaptive'): void
    {
        $multiplier = $mode === 'challenge' ? 1.5 : 1.0;
        $points = (int) round($correctCount * 10 * $multiplier);
        if ($points <= 0) {
            return;
        }

        DB::table('points_log')->insert([
            'student_id' => $studentId,
            'points'     => $points,
            'reason'     => 'quiz_completed',
            'created_at' => now(),
        ]);

        DB::table('student_profiles')
            ->where('user_id', $studentId)
            ->increment('total_points', $points);
    }
}
