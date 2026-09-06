<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FacultyQuiz;
use App\Models\FacultyQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Student side of faculty-authored class quizzes. /q/{token} is the link the
 * faculty announces; a student opens it, starts the quiz, answers within the
 * deadline / time limit, and sees their score.
 */
class ClassQuizController extends Controller
{
    /** Every published quiz, with this student's attempt (if any) attached. */
    public function index()
    {
        $user = Auth::user();
        if ($user->isFaculty()) {
            return redirect()->route('faculty.quizzes');
        }
        abort_unless($user->isStudent(), 403);

        $quizzes = FacultyQuiz::with(['subject', 'faculty:id,first_name,last_name'])
            ->withCount('items')
            ->whereIn('status', [FacultyQuiz::STATUS_PUBLISHED, FacultyQuiz::STATUS_CLOSED])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->get();

        $attempts = FacultyQuizAttempt::where('student_id', $user->id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()
            ->keyBy('quiz_id');

        // Open quizzes the student hasn't finished float to the top.
        $quizzes = $quizzes->sortBy(function ($quiz) use ($attempts) {
            $attempt = $attempts->get($quiz->id);
            if ($quiz->isOpen() && ! ($attempt && $attempt->isSubmitted())) {
                return 0;
            }
            return $attempt && $attempt->isSubmitted() ? 1 : 2;
        })->values();

        return view('student.class-quizzes', compact('quizzes', 'attempts'));
    }

    /** Quiz landing page: what it is, when it's due, and the Start button. */
    public function show(string $token)
    {
        $quiz = $this->quizByToken($token);
        $user = Auth::user();

        $isOwner = $user->id === $quiz->faculty_id;
        // Drafts are invisible to everyone but the faculty who is building them.
        abort_if($quiz->isDraft() && ! $isOwner, 404);

        $attempt = $user->isStudent()
            ? FacultyQuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $user->id)->first()
            : null;

        $quiz->load(['subject', 'faculty:id,first_name,last_name'])->loadCount('items');

        return view('student.class-quiz-show', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'isOwner' => $isOwner,
            'canTake' => $user->isStudent(),
        ]);
    }

    public function start(string $token)
    {
        $quiz = $this->quizByToken($token);
        $user = Auth::user();
        abort_unless($user->isStudent(), 403, 'Only students can take this quiz.');

        $attempt = FacultyQuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $user->id)->first();
        if ($attempt && $attempt->isSubmitted()) {
            return redirect()->route('class-quiz.result', $token);
        }

        if (! $quiz->isOpen()) {
            return redirect()->route('class-quiz.show', $token)
                ->with('error', $this->unavailableMessage($quiz));
        }

        if (! $attempt) {
            $attempt = FacultyQuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $user->id,
                'started_at' => now(),
            ]);
        }

        return redirect()->route('class-quiz.take', $token);
    }

    public function take(string $token)
    {
        $quiz = $this->quizByToken($token);
        $attempt = $this->ownAttempt($quiz);

        if ($attempt->isSubmitted()) {
            return redirect()->route('class-quiz.result', $token);
        }
        if (! $quiz->isPublished()) {
            return redirect()->route('class-quiz.show', $token)->with('error', 'This quiz has been closed by your instructor.');
        }

        $quiz->load(['items', 'subject']);
        $items = $this->orderedItems($quiz, $attempt);

        // The timer counts down to whichever comes first: the per-attempt time
        // limit or the quiz deadline. Untimed quizzes with no deadline have no timer.
        $limits = array_filter([
            $attempt->secondsRemaining(),
            $quiz->due_at ? max(0, (int) now()->diffInSeconds($quiz->due_at, false)) : null,
        ], fn ($v) => $v !== null);
        $secondsLeft = $limits ? min($limits) : null;

        return view('student.class-quiz-take', compact('quiz', 'attempt', 'items', 'secondsLeft'));
    }

    public function submit(Request $request, string $token)
    {
        $quiz = $this->quizByToken($token);
        $attempt = $this->ownAttempt($quiz);

        if ($attempt->isSubmitted()) {
            return redirect()->route('class-quiz.result', $token);
        }
        if (! $quiz->isPublished()) {
            return redirect()->route('class-quiz.show', $token)->with('error', 'This quiz has been closed by your instructor.');
        }

        $quiz->load('items');
        $submitted = (array) $request->input('answers', []);

        $answers = [];
        $score = 0;
        $total = 0;
        foreach ($quiz->items as $item) {
            $total += $item->points;
            $picked = isset($submitted[$item->id]) ? strtoupper(trim((string) $submitted[$item->id])) : null;
            $valid = collect($item->choices)->pluck('label')->map(fn ($l) => strtoupper($l))->all();
            if ($picked === null || $picked === '' || ! in_array($picked, $valid, true)) {
                continue;
            }
            $answers[$item->id] = $picked;
            if ($picked === strtoupper((string) $item->correctLabel())) {
                $score += $item->points;
            }
        }

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'total_points' => $total,
            'percent' => $total > 0 ? round($score / $total * 100, 2) : 0,
            'submitted_at' => now(),
        ]);

        return redirect()->route('class-quiz.result', $token);
    }

    public function result(string $token)
    {
        $quiz = $this->quizByToken($token);
        $attempt = $this->ownAttempt($quiz);

        if (! $attempt->isSubmitted()) {
            return redirect()->route('class-quiz.take', $token);
        }

        $quiz->load(['items', 'subject', 'faculty:id,first_name,last_name']);
        $items = $this->orderedItems($quiz, $attempt);

        return view('student.class-quiz-result', compact('quiz', 'attempt', 'items'));
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function quizByToken(string $token): FacultyQuiz
    {
        return FacultyQuiz::where('share_token', $token)->firstOrFail();
    }

    private function ownAttempt(FacultyQuiz $quiz): FacultyQuizAttempt
    {
        abort_unless(Auth::user()->isStudent(), 403, 'Only students can take this quiz.');

        return FacultyQuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->firstOrFail();
    }

    /**
     * Question order for one attempt. Shuffled quizzes use a deterministic
     * order per attempt so the take page and the result page always match.
     */
    private function orderedItems(FacultyQuiz $quiz, FacultyQuizAttempt $attempt)
    {
        $items = $quiz->items;
        if (! $quiz->shuffle_questions) {
            return $items->values();
        }

        return $items->sortBy(fn ($item) => crc32($attempt->id . '-' . $item->id))->values();
    }

    private function unavailableMessage(FacultyQuiz $quiz): string
    {
        return match ($quiz->availability()) {
            'upcoming' => 'This quiz opens on ' . $quiz->opens_at->format('M j, Y g:i A') . '.',
            'expired' => 'The deadline for this quiz has passed.',
            'closed' => 'This quiz has been closed by your instructor.',
            default => 'This quiz is not available right now.',
        };
    }
}
