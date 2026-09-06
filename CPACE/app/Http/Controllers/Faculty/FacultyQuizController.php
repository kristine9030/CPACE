<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\FacultyQuiz;
use App\Models\FacultyQuizItem;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class quizzes: a faculty member assembles a fixed set of questions (typed
 * in, or copied from their Test Bank), sets a deadline / time limit, saves
 * it as a draft or publishes it, and shares the quiz link with students.
 */
class FacultyQuizController extends Controller
{
    private const MAX_ITEMS = 100;

    /** Quiz list with status filter and quick counts. */
    public function index(Request $request)
    {
        $faculty = Auth::user();
        $status = $request->input('status');

        $quizzes = FacultyQuiz::with('subject')
            ->withCount(['items', 'attempts as submitted_count' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->where('faculty_id', $faculty->id)
            ->when(in_array($status, ['draft', 'published', 'closed'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('updated_at')
            ->get();

        $counts = [
            'all' => FacultyQuiz::where('faculty_id', $faculty->id)->count(),
            'draft' => FacultyQuiz::where('faculty_id', $faculty->id)->where('status', 'draft')->count(),
            'published' => FacultyQuiz::where('faculty_id', $faculty->id)->where('status', 'published')->count(),
            'closed' => FacultyQuiz::where('faculty_id', $faculty->id)->where('status', 'closed')->count(),
        ];

        return view('faculty.quizzes', compact('quizzes', 'counts', 'status'));
    }

    public function create()
    {
        return view('faculty.quiz-form', [
            'quiz' => new FacultyQuiz(['show_results' => true, 'shuffle_questions' => false]),
            'items' => [],
            'subjects' => $this->subjectsFor(Auth::user()),
            'locked' => false,
        ]);
    }

    public function store(Request $request)
    {
        $faculty = Auth::user();
        $data = $this->validatedSettings($request, $faculty);
        $items = $this->parseItems($request);
        $publish = $request->input('action') === 'publish';

        if ($publish) {
            $this->assertPublishable($items, $data['due_at']);
        }

        $quiz = DB::transaction(function () use ($faculty, $data, $items, $publish) {
            $quiz = FacultyQuiz::create($data + [
                'faculty_id' => $faculty->id,
                'status' => $publish ? FacultyQuiz::STATUS_PUBLISHED : FacultyQuiz::STATUS_DRAFT,
                'published_at' => $publish ? now() : null,
            ]);
            $this->replaceItems($quiz, $items);

            return $quiz;
        });

        return redirect()
            ->route('faculty.quizzes.edit', $quiz->id)
            ->with('status', $publish ? 'Quiz published. Copy the link below to share it with your students.' : 'Draft saved.');
    }

    public function edit(FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->load('items');

        return view('faculty.quiz-form', [
            'quiz' => $quiz,
            'items' => $quiz->items->map(fn ($item) => [
                'question_text' => $item->question_text,
                'question_type' => $item->question_type,
                'choices' => $item->choices,
                'explanation' => $item->explanation,
                'points' => $item->points,
                'source_question_id' => $item->source_question_id,
            ])->values()->all(),
            'subjects' => $this->subjectsFor(Auth::user()),
            'locked' => $quiz->attempts()->exists(),
        ]);
    }

    public function update(Request $request, FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $faculty = Auth::user();
        $data = $this->validatedSettings($request, $faculty);
        $publish = $request->input('action') === 'publish';

        // Once a student has answered, the question set is frozen so existing
        // scores keep meaning something; settings (deadline etc.) stay editable.
        $locked = $quiz->attempts()->exists();
        $items = $locked
            ? $quiz->items->map(fn ($i) => $i->only(['question_text', 'question_type', 'choices', 'explanation', 'points', 'source_question_id']))->all()
            : $this->parseItems($request);

        if ($publish) {
            $this->assertPublishable($items, $data['due_at']);
        }

        DB::transaction(function () use ($quiz, $data, $items, $publish, $locked) {
            if ($publish && ! $quiz->isPublished()) {
                $data['status'] = FacultyQuiz::STATUS_PUBLISHED;
                $data['published_at'] = now();
            }
            $quiz->update($data);

            if (! $locked) {
                $this->replaceItems($quiz, $items);
            }
        });

        return back()->with('status', $publish
            ? ($quiz->wasChanged('status') ? 'Quiz published. Copy the link below to share it with your students.' : 'Changes published.')
            : 'Changes saved.');
    }

    /** Publish an existing draft (or re-open a closed quiz) from the list. */
    public function publish(FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->load('items');

        $this->assertPublishable($quiz->items->toArray(), $quiz->due_at);

        $quiz->update(['status' => FacultyQuiz::STATUS_PUBLISHED, 'published_at' => $quiz->published_at ?? now()]);

        return back()->with('status', 'Quiz published. Share the link with your students.');
    }

    /** Close a quiz early - students can no longer start or submit it. */
    public function close(FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->update(['status' => FacultyQuiz::STATUS_CLOSED]);

        return back()->with('status', 'Quiz closed. Students can no longer take it.');
    }

    /** Take a closed quiz back to draft so it can be edited before re-publishing. */
    public function reopen(FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->update(['status' => FacultyQuiz::STATUS_DRAFT]);

        return back()->with('status', 'Quiz moved back to drafts.');
    }

    public function destroy(FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->delete();

        return redirect()->route('faculty.quizzes')->with('status', 'Quiz deleted.');
    }

    /** Per-student results plus per-question difficulty for a quiz. */
    public function results(FacultyQuiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $quiz->load(['items', 'subject']);

        $attempts = $quiz->attempts()
            ->with('student:id,first_name,last_name,email')
            ->orderByRaw('submitted_at IS NULL')
            ->orderByDesc('percent')
            ->get();

        $submitted = $attempts->filter->isSubmitted();

        // How many submitted students got each question right.
        $itemStats = [];
        foreach ($quiz->items as $item) {
            $correct = $item->correctLabel();
            $right = $submitted->filter(fn ($a) => ($a->answers[$item->id] ?? null) === $correct)->count();
            $itemStats[$item->id] = [
                'right' => $right,
                'rate' => $submitted->count() > 0 ? (int) round($right / $submitted->count() * 100) : null,
            ];
        }

        $stats = [
            'started' => $attempts->count(),
            'submitted' => $submitted->count(),
            'average' => $submitted->count() > 0 ? round($submitted->avg('percent'), 1) : null,
            'highest' => $submitted->count() > 0 ? round($submitted->max('percent'), 1) : null,
            'lowest' => $submitted->count() > 0 ? round($submitted->min('percent'), 1) : null,
        ];

        return view('faculty.quiz-results', compact('quiz', 'attempts', 'stats', 'itemStats'));
    }

    /**
     * JSON search over the faculty's Test Bank for the "Add from Test Bank"
     * picker. Only active questions from the faculty's assigned subjects.
     */
    public function bankQuestions(Request $request)
    {
        $faculty = Auth::user();
        $subjectIds = $this->subjectsFor($faculty)->pluck('id');

        $query = Question::query()
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->whereIn('subjects.id', $subjectIds)
            ->where('questions.is_active', true)
            ->select('questions.*', 'topics.name as topic_name', 'subjects.code as subject_code', 'subjects.id as subject_id')
            ->with('choices');

        if ($search = trim((string) $request->input('search'))) {
            $query->where('questions.question_text', 'like', "%{$search}%");
        }
        if ($subjectId = (int) $request->input('subject')) {
            $query->where('subjects.id', $subjectId);
        }

        $questions = $query->orderByDesc('questions.id')->limit(50)->get();

        return response()->json($questions->map(fn ($q) => [
            'id' => $q->id,
            'question_text' => $q->question_text,
            'question_type' => $q->question_type,
            'difficulty' => $q->difficulty,
            'explanation' => $q->explanation,
            'topic' => $q->topic_name,
            'subject' => $q->subject_code,
            'choices' => $q->choices->sortBy('choice_label')->values()->map(fn ($c) => [
                'label' => $c->choice_label,
                'text' => $c->choice_text,
                'is_correct' => (bool) $c->is_correct,
            ])->all(),
        ]));
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function subjectsFor(User $faculty)
    {
        return $faculty->assignedSubjects()->where('subjects.is_active', true)->orderBy('subjects.id')->get();
    }

    private function authorizeQuiz(FacultyQuiz $quiz): void
    {
        abort_unless($quiz->faculty_id === Auth::id(), 403, 'This quiz belongs to another faculty member.');
    }

    /** Validate and normalise the quiz settings (everything except the questions). */
    private function validatedSettings(Request $request, User $faculty): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'subject_id' => ['nullable', 'integer'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'opens_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'show_results' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Please give the quiz a title.',
        ]);

        $subjectId = ! empty($data['subject_id']) ? (int) $data['subject_id'] : null;
        if ($subjectId !== null && ! $this->subjectsFor($faculty)->contains('id', $subjectId)) {
            throw ValidationException::withMessages(['subject_id' => 'You are not assigned to that subject.']);
        }

        $opensAt = ! empty($data['opens_at']) ? Carbon::parse($data['opens_at']) : null;
        $dueAt = ! empty($data['due_at']) ? Carbon::parse($data['due_at']) : null;
        if ($opensAt && $dueAt && $dueAt->lte($opensAt)) {
            throw ValidationException::withMessages(['due_at' => 'The deadline must be after the opening time.']);
        }

        return [
            'title' => trim($data['title']),
            'subject_id' => $subjectId,
            'instructions' => isset($data['instructions']) && trim($data['instructions']) !== '' ? trim($data['instructions']) : null,
            'opens_at' => $opensAt,
            'due_at' => $dueAt,
            'time_limit_minutes' => ! empty($data['time_limit_minutes']) ? (int) $data['time_limit_minutes'] : null,
            'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
            'show_results' => (bool) ($data['show_results'] ?? false),
        ];
    }

    /**
     * Decode and validate the question list posted by the builder (items_json).
     * A draft may have zero questions; every question that IS present must be
     * complete: text, 2-6 non-blank choices, exactly one correct answer.
     */
    private function parseItems(Request $request): array
    {
        $raw = $request->input('items_json');
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['items_json' => 'The question list could not be read. Please try again.']);
        }
        if (count($decoded) > self::MAX_ITEMS) {
            throw ValidationException::withMessages(['items_json' => 'A quiz can have at most ' . self::MAX_ITEMS . ' questions.']);
        }

        $items = [];
        foreach (array_values($decoded) as $i => $row) {
            $n = $i + 1;
            $text = trim((string) ($row['question_text'] ?? ''));
            if ($text === '') {
                throw ValidationException::withMessages(['items_json' => "Question {$n} has no text."]);
            }

            $type = ($row['question_type'] ?? 'mcq') === 'true_false' ? 'true_false' : 'mcq';
            $choices = [];
            $correct = 0;
            foreach (array_values((array) ($row['choices'] ?? [])) as $j => $choice) {
                $choiceText = trim((string) ($choice['text'] ?? ''));
                if ($choiceText === '') {
                    continue;
                }
                $isCorrect = ! empty($choice['is_correct']);
                $correct += $isCorrect ? 1 : 0;
                $choices[] = [
                    'label' => $type === 'true_false' ? ($j === 0 ? 'T' : 'F') : chr(65 + count($choices)),
                    'text' => $choiceText,
                    'is_correct' => $isCorrect,
                ];
            }

            if (count($choices) < 2 || count($choices) > 6) {
                throw ValidationException::withMessages(['items_json' => "Question {$n} needs between 2 and 6 answer choices."]);
            }
            if ($correct !== 1) {
                throw ValidationException::withMessages(['items_json' => "Question {$n} must have exactly one correct answer."]);
            }

            $points = (int) ($row['points'] ?? 1);
            $items[] = [
                'question_text' => $text,
                'question_type' => $type,
                'choices' => $choices,
                'explanation' => trim((string) ($row['explanation'] ?? '')) ?: null,
                'points' => max(1, min(100, $points)),
                'source_question_id' => ! empty($row['source_question_id']) ? (int) $row['source_question_id'] : null,
            ];
        }

        return $items;
    }

    private function assertPublishable(array $items, $dueAt): void
    {
        if (count($items) === 0) {
            throw ValidationException::withMessages(['items_json' => 'Add at least one question before publishing.']);
        }
        if ($dueAt && Carbon::parse($dueAt)->isPast()) {
            throw ValidationException::withMessages(['due_at' => 'The deadline has already passed. Move it to a future date before publishing.']);
        }
    }

    private function replaceItems(FacultyQuiz $quiz, array $items): void
    {
        $quiz->items()->delete();
        foreach ($items as $order => $item) {
            FacultyQuizItem::create($item + ['quiz_id' => $quiz->id, 'sort_order' => $order]);
        }
    }
}
