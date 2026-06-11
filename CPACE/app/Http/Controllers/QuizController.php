<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /** Number of questions served per quiz. */
    private const QUIZ_LENGTH = 10;

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

        $totalAnswered = (int) DB::table('quiz_sessions')->where('student_id', $studentId)->sum('total_items');

        $recentSessions = QuizSession::with('subject')
            ->where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->limit(4)
            ->get();

        return view('student.adaptive-quizzes', compact('subjects', 'accuracy', 'totalAnswered', 'recentSessions'));
    }

    /**
     * Start a new quiz: pick questions for the chosen subject and create the session.
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'mode'       => 'nullable|string',
        ]);

        $topicIds = DB::table('topics')->where('subject_id', $data['subject_id'])->pluck('id');

        $questionIds = Question::where('is_active', true)
            ->whereIn('topic_id', $topicIds)
            ->inRandomOrder()
            ->limit(self::QUIZ_LENGTH)
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return back()->with('error', 'No questions are available for that subject yet.');
        }

        $session = QuizSession::create([
            'student_id'      => Auth::id(),
            'session_type'    => 'testing',
            'subject_id'      => $data['subject_id'],
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
     * Show the quiz questions for an in-progress session.
     */
    public function take(int $sessionId)
    {
        $session = $this->ownedSession($sessionId);

        if ($session->completed_at) {
            return redirect()->route('quiz.results', $session->id);
        }

        $questionIds = $session->answers()->pluck('question_id');
        $questions = Question::with('choices')
            ->whereIn('id', $questionIds)
            ->get();

        return view('student.take-quiz', compact('session', 'questions'));
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
                'duration_secs'   => max(0, now()->diffInSeconds($session->started_at)),
            ]);

            $this->updatePerformanceRecords($session->student_id, $topicTally);
            $this->awardPoints($session->student_id, $correctCount);
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
        $questions = Question::with('choices')
            ->whereIn('id', $answers->keys())
            ->get();

        $session->loadMissing('subject');

        return view('student.quiz-results', compact('session', 'questions', 'answers'));
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
     */
    private function awardPoints(int $studentId, int $correctCount): void
    {
        $points = $correctCount * 10;
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
