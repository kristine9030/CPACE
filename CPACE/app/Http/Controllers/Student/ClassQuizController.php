<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FacultyQuiz;
use App\Models\FacultyQuizAttempt;
use App\Models\Question;
use App\Models\Subject;
use App\Services\PerformanceRecorder;
use App\Services\SpacedRepetitionScheduler;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Student side of faculty-authored class quizzes. /q/{token} is the link the
 * faculty announces; a student opens it, starts the quiz, answers within the
 * deadline / time limit, and sees their score.
 */
class ClassQuizController extends Controller
{
    /**
     * Class list — one card per subject the student is enrolled in, whether
     * or not a quiz has been posted there yet. There is no per-student
     * enrolment table, so every active subject counts as "their class"; a
     * subject with nothing posted still shows up, just empty.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->isFaculty()) {
            return redirect()->route('faculty.quizzes');
        }
        abort_unless($user->isStudent(), 403);

        $quizzes = $this->visibleQuizzes()->get();
        $attempts = $this->attemptsFor($user->id, $quizzes);
        $bySubject = $quizzes->groupBy(fn (FacultyQuiz $quiz) => $quiz->subject_id ?? 'general');

        $classes = Subject::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (Subject $subject) => $this->classCard($subject, $bySubject->get($subject->id, collect()), $attempts));

        // Quizzes posted with no subject at all still need a home.
        if ($general = $bySubject->get('general')) {
            $classes->push($this->classCard(null, $general, $attempts));
        }

        // Classes with quizzes posted come first (unfinished work floats to
        // the very top of those), empty classes trail at the end.
        $classes = $classes->sortBy([
            ['total', 'desc'],
            ['todo', 'desc'],
            ['code', 'asc'],
        ])->values();

        return view('student.class-quizzes', compact('classes'));
    }

    /** One class-list card's worth of data for a subject (or "general"). */
    private function classCard(?Subject $subject, $group, $attempts): array
    {
        $todo = $group->filter(fn ($quiz) => $quiz->isOpen() && ! $attempts->get($quiz->id)?->isSubmitted());
        $faculty = $group->isNotEmpty()
            ? $group->pluck('faculty')->filter()->unique('id')->values()
            : ($subject?->faculty()->get() ?? collect());

        return [
            'key' => $subject?->id ?? 'general',
            'code' => $subject?->code ?? 'General',
            'name' => $subject?->name ?? 'Quizzes without a subject',
            'icon' => self::SUBJECT_ICONS[strtoupper((string) $subject?->code)] ?? 'fa-layer-group',
            'icon2' => self::SUBJECT_ICONS_2[strtoupper((string) $subject?->code)] ?? 'fa-shapes',
            'theme' => self::theme($subject?->code),
            'faculty' => $faculty,
            'total' => $group->count(),
            'done' => $group->filter(fn ($quiz) => $attempts->get($quiz->id)?->isSubmitted())->count(),
            'todo' => $todo->count(),
            // Headline on the card: the soonest deadline still waiting on them.
            'next' => $todo->filter(fn ($quiz) => $quiz->due_at)->sortBy('due_at')->first() ?: $todo->first(),
        ];
    }

    /** One class: every quiz posted in a single subject, Classroom-style. */
    public function subject(string $subject)
    {
        $user = Auth::user();
        if ($user->isFaculty()) {
            return redirect()->route('faculty.quizzes');
        }
        abort_unless($user->isStudent(), 403);

        $isGeneral = $subject === 'general';
        $subjectModel = $isGeneral ? null : Subject::find($subject);
        abort_if(! $isGeneral && ! $subjectModel, 404);

        $quizzes = $this->visibleQuizzes()
            ->when(
                $isGeneral,
                fn ($query) => $query->whereNull('subject_id'),
                fn ($query) => $query->where('subject_id', $subjectModel->id)
            )
            ->get();

        $attempts = $this->attemptsFor($user->id, $quizzes);
        $quizzes = $this->todoFirst($quizzes, $attempts);

        $faculty = $quizzes->isNotEmpty()
            ? $quizzes->pluck('faculty')->filter()->unique('id')->values()
            : ($subjectModel?->faculty()->get() ?? collect());

        return view('student.class-quiz-subject', [
            'subject' => $subjectModel,
            'code' => $subjectModel?->code ?? 'General',
            'name' => $subjectModel?->name ?? 'Quizzes without a subject',
            'icon' => self::SUBJECT_ICONS[strtoupper((string) $subjectModel?->code)] ?? 'fa-layer-group',
            'icon2' => self::SUBJECT_ICONS_2[strtoupper((string) $subjectModel?->code)] ?? 'fa-shapes',
            'theme' => self::theme($subjectModel?->code),
            'faculty' => $faculty,
            'quizzes' => $quizzes,
            'attempts' => $attempts,
            'done' => $quizzes->filter(fn ($quiz) => $attempts->get($quiz->id)?->isSubmitted())->count(),
        ]);
    }

    /** Published / closed quizzes, soonest deadline first. */
    private function visibleQuizzes()
    {
        return FacultyQuiz::with(['subject', 'faculty:id,first_name,last_name,profile_photo'])
            ->withCount('items')
            ->whereIn('status', [FacultyQuiz::STATUS_PUBLISHED, FacultyQuiz::STATUS_CLOSED])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at');
    }

    /** This student's attempts on the given quizzes, keyed by quiz id. */
    private function attemptsFor(int $studentId, $quizzes)
    {
        return FacultyQuizAttempt::where('student_id', $studentId)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()
            ->keyBy('quiz_id');
    }

    /** Open quizzes the student hasn't finished float to the top. */
    private function todoFirst($quizzes, $attempts)
    {
        return $quizzes->sortBy(function (FacultyQuiz $quiz) use ($attempts) {
            $submitted = (bool) $attempts->get($quiz->id)?->isSubmitted();
            if ($quiz->isOpen() && ! $submitted) {
                return 0;
            }
            return $submitted ? 1 : 2;
        })->values();
    }

    /**
     * Card colour per subject — the same palette already used on Quiz History
     * and Adaptive Quizzes, so a subject reads as the same colour everywhere
     * in the student side, not a different one invented for this page.
     */
    private const SUBJECT_COLORS = [
        'FAR' => '#4A90E2', 'AFAR' => '#17A2B8', 'MS' => '#F39C12',
        'TAX' => '#27AE60', 'AUD' => '#c0392b', 'RFBT' => '#9B59B6',
    ];

    /** Same icon each subject wears on Adaptive Quizzes — one identity per subject everywhere. */
    private const SUBJECT_ICONS = [
        'FAR' => 'fa-chart-line', 'AFAR' => 'fa-coins', 'MS' => 'fa-gears',
        'TAX' => 'fa-file-invoice-dollar', 'AUD' => 'fa-magnifying-glass', 'RFBT' => 'fa-scale-balanced',
    ];

    /** A second, complementary icon for the little illustration cluster on each card. */
    private const SUBJECT_ICONS_2 = [
        'FAR' => 'fa-calculator', 'AFAR' => 'fa-file-invoice', 'MS' => 'fa-chart-pie',
        'TAX' => 'fa-percent', 'AUD' => 'fa-clipboard-list', 'RFBT' => 'fa-book',
    ];

    /** [from, to] gradient stops for a subject's banner: its exact brand
     *  colour, deepened for the second stop so the banner still has depth. */
    private static function theme(?string $code): array
    {
        $base = self::SUBJECT_COLORS[strtoupper((string) $code)] ?? '#7B1D1D';

        return [
            'base' => $base,
            'dark' => self::darken($base, 0.32),
            'pastel' => self::tint($base, 0.88),
            'soft' => self::tint($base, 0.72),
        ];
    }

    /** Darken a #rrggbb colour by the given fraction (0–1) toward black. */
    private static function darken(string $hex, float $amount): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', $r * (1 - $amount), $g * (1 - $amount), $b * (1 - $amount));
    }

    /** Lighten a #rrggbb colour toward white — the pastel card background. */
    private static function tint(string $hex, float $amount): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', $r + (255 - $r) * $amount, $g + (255 - $g) * $amount, $b + (255 - $b) * $amount);
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

        // Items copied from the Test Bank carry source_question_id; look up
        // their topic/difficulty in one query so grading stays a single pass.
        $sourceIds = $quiz->items->pluck('source_question_id')->filter()->unique();
        $sourceQuestions = Question::whereIn('id', $sourceIds)->get(['id', 'topic_id', 'difficulty'])->keyBy('id');

        $answers = [];
        $score = 0;
        $total = 0;
        $topicTally = [];
        $answerResults = [];
        foreach ($quiz->items as $item) {
            $total += $item->points;
            $picked = isset($submitted[$item->id]) ? strtoupper(trim((string) $submitted[$item->id])) : null;
            $valid = collect($item->choices)->pluck('label')->map(fn ($l) => strtoupper($l))->all();
            if ($picked === null || $picked === '' || ! in_array($picked, $valid, true)) {
                continue;
            }
            $answers[$item->id] = $picked;
            $isCorrect = $picked === strtoupper((string) $item->correctLabel());
            if ($isCorrect) {
                $score += $item->points;
            }

            $source = $item->source_question_id ? $sourceQuestions->get($item->source_question_id) : null;
            if ($source) {
                $topicTally[$source->topic_id] ??= ['attempts' => 0, 'correct' => 0];
                $topicTally[$source->topic_id]['attempts']++;
                $topicTally[$source->topic_id]['correct'] += $isCorrect ? 1 : 0;
                $answerResults[] = [
                    'question_id' => $source->id,
                    'difficulty' => $source->difficulty,
                    'correct' => $isCorrect,
                ];
            }
        }

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'total_points' => $total,
            'percent' => $total > 0 ? round($score / $total * 100, 2) : 0,
            'submitted_at' => now(),
        ]);

        // Analytics run after the grade is committed so a failure here can
        // never void a finished attempt. Items with no source_question_id
        // (a manually typed question, not picked from the Test Bank) can't
        // be attributed to a topic and are simply skipped for this part.
        try {
            app(PerformanceRecorder::class)->record(Auth::id(), $topicTally);
            app(SpacedRepetitionScheduler::class)->recordAnswers(Auth::id(), $answerResults);
            app(WeaknessDetector::class)->syncMany(Auth::id(), array_keys($topicTally));
        } catch (\Throwable $e) {
            report($e);
        }

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
