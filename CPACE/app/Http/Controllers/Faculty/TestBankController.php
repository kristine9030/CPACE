<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;

use App\Models\Question;
use App\Models\QuestionVariant;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\AiQuestionAssistantService;
use App\Services\QuestionParaphraser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestBankController extends Controller
{
    /** Difficulty labels shown in the UI mapped to the DB enum. */
    private const DIFFICULTY_MAP = [
        'Easy' => 'easy', 'Medium' => 'moderate', 'Hard' => 'difficult',
    ];

    /**
     * Test Bank listing with live stats and filters.
     */
    public function index(Request $request)
    {
        $questions = $this->filteredQuery($request)->paginate(15)->withQueryString();

        // Live search / filter / pagination requests only need the table markup.
        if ($request->ajax()) {
            return view('faculty.partials.test-bank-table', ['questions' => $questions]);
        }

        $statsBase = $this->scopedQuestionQuery(Auth::user());

        $stats = [
            'total'     => (clone $statsBase)->count(),
            'active'    => (clone $statsBase)->where('questions.is_active', true)->count(),
            'draft'     => (clone $statsBase)->where('questions.is_active', false)->count(),
            'this_week' => (clone $statsBase)->where('questions.created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('faculty.test-bank', [
            'questions' => $questions,
            'stats'     => $stats,
            'subjects'  => $this->subjectsFor(Auth::user())->orderBy('id')->get(),
            'filters'   => $request->only(['search', 'subject', 'type', 'difficulty', 'status']),
        ]);
    }

    /**
     * Question query joined to topic/subject and restricted to the subjects
     * the current faculty member is assigned to (chair/admin see everything).
     * "All Subjects" in the UI therefore still only ever means "all of my
     * assigned subjects" for a faculty member, never the whole test bank.
     */
    private function scopedQuestionQuery(\App\Models\User $user)
    {
        $query = Question::query()
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id');

        if (! $user->isChair()) {
            $query->whereIn('subjects.id', $user->assignedSubjects()->pluck('subjects.id'));
        }

        return $query;
    }

    /**
     * Build the Test Bank question query with all the active filters applied.
     * Shared by the listing and the export so both honour the same filters.
     */
    private function filteredQuery(Request $request)
    {
        $query = $this->scopedQuestionQuery(Auth::user())
            ->select('questions.*', 'topics.name as topic_name', 'subjects.code as subject_code', 'subjects.id as subject_id')
            ->withCount('variants');

        if ($search = $request->input('search')) {
            $query->where('questions.question_text', 'like', "%{$search}%");
        }
        if ($subjectId = $request->input('subject')) {
            $query->where('subjects.id', $subjectId);
        }
        if ($type = $request->input('type')) {
            $query->where('questions.question_type', $type);
        }
        if ($difficulty = $request->input('difficulty')) {
            $query->where('questions.difficulty', $difficulty);
        }
        if (($status = $request->input('status')) !== null && $status !== '') {
            $query->where('questions.is_active', $status === 'active');
        }

        return $query->orderByDesc('questions.id');
    }

    /**
     * Export the currently filtered questions as CSV, JSON, or a printable PDF.
     * The same filters the faculty has applied on the listing are re-applied here
     * (passed through as query-string params), so the export matches what they see.
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        $questions = $this->filteredQuery($request)->with('choices')->get();
        $filename  = 'test-bank-' . now()->format('Y-m-d_His');

        return match ($format) {
            'json'  => $this->exportJson($questions, $filename),
            'pdf'   => view('faculty.test-bank-export', ['questions' => $questions, 'filters' => $request->only(['search', 'subject', 'type', 'difficulty', 'status'])]),
            default => $this->exportCsv($questions, $filename),
        };
    }

    /** Human-readable labels for the stored difficulty enum. */
    private const DIFFICULTY_LABELS = [
        'easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard',
    ];

    /** Flatten a question's choices into "A. text (correct)" style strings. */
    private function choiceLines(Question $question): array
    {
        return $question->choices
            ->sortBy('choice_label')
            ->map(fn ($c) => trim($c->choice_label . '. ' . $c->choice_text . ($c->is_correct ? '  [correct]' : '')))
            ->values()
            ->all();
    }

    /** The single correct answer for a question, as "A. text". */
    private function correctAnswer(Question $question): string
    {
        $correct = $question->choices->firstWhere('is_correct', true);

        return $correct ? trim($correct->choice_label . '. ' . $correct->choice_text) : '';
    }

    /** Stream the questions as a CSV file (opens directly in Excel / Sheets). */
    private function exportCsv($questions, string $filename)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->stream(function () use ($questions) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel renders accented characters correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Subject', 'Topic', 'Type', 'Difficulty', 'Status',
                'Question', 'Choices', 'Correct Answer', 'Explanation', 'Variants',
            ]);

            foreach ($questions as $q) {
                fputcsv($out, [
                    $q->id,
                    $q->subject_code,
                    $q->topic_name,
                    $q->question_type === 'mcq' ? 'Multiple Choice' : 'True / False',
                    self::DIFFICULTY_LABELS[$q->difficulty] ?? $q->difficulty,
                    $q->is_active ? 'Active' : 'Draft',
                    $q->question_text,
                    implode("\n", $this->choiceLines($q)),
                    $this->correctAnswer($q),
                    $q->explanation,
                    $q->variants_count,
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    /** Download the questions as a structured JSON file. */
    private function exportJson($questions, string $filename)
    {
        $payload = $questions->map(fn (Question $q) => [
            'id'             => $q->id,
            'subject'        => $q->subject_code,
            'topic'          => $q->topic_name,
            'type'           => $q->question_type,
            'difficulty'     => self::DIFFICULTY_LABELS[$q->difficulty] ?? $q->difficulty,
            'status'         => $q->is_active ? 'active' : 'draft',
            'question_text'  => $q->question_text,
            'choices'        => $q->choices->sortBy('choice_label')->map(fn ($c) => [
                'label'      => $c->choice_label,
                'text'       => $c->choice_text,
                'is_correct' => (bool) $c->is_correct,
            ])->values(),
            'explanation'    => $q->explanation,
            'variants_count' => $q->variants_count,
        ]);

        return response()->json($payload, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('faculty.question-form', [
            'subjects'    => $this->subjectsFor(Auth::user())
                ->with(['topics' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('id')->get(),
            'editMode'    => false,
        ]);
    }

    /**
     * Subjects a faculty member is allowed to write test-bank questions for:
     * only the subjects the Program Chair assigned to them. Chair/admin can
     * manage everything.
     */
    private function subjectsFor(\App\Models\User $user)
    {
        return $user->isChair()
            ? Subject::where('is_active', true)
            : $user->assignedSubjects()->where('is_active', true);
    }

    /** Message shown (as a friendly modal, not a hard error page) when a faculty member strays outside their assigned subjects. */
    private const NOT_ASSIGNED_MESSAGE = "You're not assigned to this subject, so you can't manage its questions. Ask your Program Chair for access if you think this is a mistake.";

    /** Whether this faculty member is allowed to manage questions in the given subject. Chair/admin can manage everything. */
    private function canManageSubject(\App\Models\User $user, int $subjectId): bool
    {
        return $user->isChair() || $user->assignedSubjects()->where('subjects.id', $subjectId)->exists();
    }

    /**
     * Draft a full question (text, choices, explanation) with AI, grounded in
     * the chosen subject/topic and the questions already in the bank for that
     * topic so it doesn't duplicate them. Returns JSON for the form to fill
     * in — nothing is saved here, the faculty still reviews and submits.
     */
    public function aiDraft(Request $request, AiQuestionAssistantService $ai)
    {
        $data = $request->validate([
            'topic_id'      => 'required|exists:topics,id',
            'difficulty'    => 'required|in:Easy,Medium,Hard',
            'question_type' => 'required|in:mcq,true_false',
            'seed_idea'     => 'nullable|string|max:500',
        ]);

        $topic = Topic::with('subject')->findOrFail($data['topic_id']);

        if (! $this->canManageSubject(Auth::user(), $topic->subject_id)) {
            return response()->json(['message' => self::NOT_ASSIGNED_MESSAGE, 'not_assigned' => true], 403);
        }

        $existingQuestions = Question::where('topic_id', $topic->id)
            ->orderByDesc('id')
            ->limit(15)
            ->pluck('question_text')
            ->all();

        try {
            $draft = $ai->draftQuestion(
                $topic->subject->name ?? $topic->subject->code ?? 'CPA Reviewer',
                $topic->name,
                $data['difficulty'],
                $data['question_type'],
                $existingQuestions,
                $data['seed_idea'] ?? null
            );
        } catch (\Throwable $e) {
            Log::error('AI question draft failed.', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'The AI could not draft a question right now. Please try again in a moment, or write it manually.',
            ], 503);
        }

        return response()->json($draft);
    }

    /**
     * Persist a new question and its choices.
     */
    public function store(Request $request)
    {
        $data = $this->validateQuestion($request);

        if (! $this->canManageSubject(Auth::user(), Topic::findOrFail($data['topic_id'])->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        DB::transaction(function () use ($data, $request) {
            $question = Question::create([
                'topic_id'      => $data['topic_id'],
                'created_by'    => Auth::id(),
                'question_text' => $data['question_text'],
                'question_type' => $data['question_type'],
                'difficulty'    => self::DIFFICULTY_MAP[$data['difficulty']],
                'explanation'   => $data['explanation'] ?? null,
                'is_active'     => $request->boolean('is_active'),
            ]);

            $this->saveChoices($question, $data);
        });

        return redirect()->route('faculty.test-bank')->with('status', 'Question added to the test bank.');
    }

    /**
     * Show the edit form.
     */
    public function edit(int $id)
    {
        $question = Question::with('choices')->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        return view('faculty.question-form', [
            'subjects'       => $this->subjectsFor(Auth::user())->with('topics')->orderBy('id')->get(),
            'editMode'       => true,
            'question'       => $question,
            'currentSubject' => $question->topic->subject_id,
            'stats'          => $this->questionStats($question),
        ]);
    }

    /**
     * Real usage stats for a question, derived from recorded quiz answers.
     * Avg. time spent has no per-question timer in the schema, so it's
     * estimated from the parent session's total duration divided evenly
     * across that session's items.
     */
    private function questionStats(Question $question): array
    {
        $timesAnswered = DB::table('quiz_answers')->where('question_id', $question->id)->count();

        $correctRate = null;
        if ($timesAnswered > 0) {
            $correct = DB::table('quiz_answers')->where('question_id', $question->id)->where('is_correct', true)->count();
            $correctRate = round($correct / $timesAnswered * 100);
        }

        $avgTimeSpent = DB::table('quiz_answers')
            ->join('quiz_sessions', 'quiz_sessions.id', '=', 'quiz_answers.session_id')
            ->where('quiz_answers.question_id', $question->id)
            ->where('quiz_sessions.total_items', '>', 0)
            ->whereNotNull('quiz_sessions.duration_secs')
            ->avg(DB::raw('quiz_sessions.duration_secs / quiz_sessions.total_items'));

        return [
            'times_answered' => $timesAnswered,
            'correct_rate'   => $correctRate,
            'avg_time_secs'  => $avgTimeSpent !== null ? round($avgTimeSpent) : null,
            'date_added'     => $question->created_at,
        ];
    }

    /**
     * Update an existing question and replace its choices.
     */
    public function update(Request $request, int $id)
    {
        $question = Question::with('topic')->findOrFail($id);
        $data = $this->validateQuestion($request);

        $canManage = $this->canManageSubject(Auth::user(), $question->topic->subject_id)
            && $this->canManageSubject(Auth::user(), Topic::findOrFail($data['topic_id'])->subject_id);

        if (! $canManage) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        DB::transaction(function () use ($question, $data, $request) {
            $question->update([
                'topic_id'      => $data['topic_id'],
                'question_text' => $data['question_text'],
                'question_type' => $data['question_type'],
                'difficulty'    => self::DIFFICULTY_MAP[$data['difficulty']],
                'explanation'   => $data['explanation'] ?? null,
                'is_active'     => $request->boolean('is_active'),
            ]);

            $question->choices()->delete();
            $this->saveChoices($question, $data);
        });

        return redirect()->route('faculty.test-bank')->with('status', 'Question updated.');
    }

    /**
     * Delete a question (choices cascade in the DB).
     */
    public function destroy(int $id)
    {
        $question = Question::with('topic')->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        $question->delete();

        return redirect()->route('faculty.test-bank')->with('status', 'Question deleted.');
    }

    /**
     * Manage the alternative wordings (variants) of a question. These are shown
     * to students in place of the original to discourage memorisation, without
     * ever changing the stored question or its correct answer.
     */
    public function variants(int $id)
    {
        $question = Question::with(['choices', 'topic.subject', 'variants' => fn ($q) => $q->orderByDesc('id')])
            ->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        return view('faculty.question-variants', [
            'question'   => $question,
            'vocabulary' => QuestionParaphraser::vocabulary(),
        ]);
    }

    /**
     * Save a new faculty-written variant for a question.
     */
    public function storeVariant(Request $request, int $id)
    {
        $question = Question::with('topic')->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        $data = $request->validate([
            'variant_text' => 'required|string|min:5|max:1000',
            'source'       => 'nullable|in:faculty,ai,rule',
        ]);

        $question->variants()->create([
            'variant_text' => trim($data['variant_text']),
            'source'       => $data['source'] ?? 'faculty',
            'is_active'    => true,
        ]);

        return redirect()
            ->route('faculty.question.variants', $id)
            ->with('status', 'Variant added. Students will now see this wording too.');
    }

    /**
     * Toggle a variant active/inactive without deleting it.
     */
    public function toggleVariant(int $id, int $variantId)
    {
        $question = Question::with('topic')->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        $variant = QuestionVariant::where('question_id', $id)->findOrFail($variantId);
        $variant->update(['is_active' => ! $variant->is_active]);

        return redirect()->route('faculty.question.variants', $id);
    }

    /**
     * Delete a variant.
     */
    public function destroyVariant(int $id, int $variantId)
    {
        $question = Question::with('topic')->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        QuestionVariant::where('question_id', $id)->where('id', $variantId)->delete();

        return redirect()
            ->route('faculty.question.variants', $id)
            ->with('status', 'Variant removed.');
    }

    /**
     * Return a draft variant the faculty can edit. Tries the AI rewrite first
     * (grounded in the question's subject/topic); if the AI is unavailable or
     * fails, falls back to the always-on rule-based paraphraser so the button
     * never dead-ends.
     */
    public function suggestVariant(int $id, AiQuestionAssistantService $ai)
    {
        $question = Question::with('topic.subject')->findOrFail($id);

        if (! $this->canManageSubject(Auth::user(), $question->topic->subject_id)) {
            return response()->json(['message' => self::NOT_ASSIGNED_MESSAGE, 'not_assigned' => true], 403);
        }

        try {
            $draft = $ai->rewriteVariant(
                $question->topic->subject->name ?? $question->topic->subject->code ?? 'CPA Reviewer',
                $question->topic->name ?? '',
                $question->question_text
            );

            return response()->json(['draft' => $draft, 'source' => 'ai']);
        } catch (\Throwable $e) {
            Log::warning('AI variant rewrite failed, falling back to rule-based paraphraser.', ['error' => $e->getMessage()]);
        }

        $draft = QuestionParaphraser::rephrase(
            $question->question_text,
            random_int(1, 2_000_000_000),
            $question->question_type
        );

        return response()->json(['draft' => $draft, 'source' => 'rule']);
    }

    /**
     * Validate the submitted question. MCQ needs 4 choices + a correct one;
     * True/False needs the boolean answer.
     */
    private function validateQuestion(Request $request): array
    {
        $rules = [
            'topic_id'      => 'required|exists:topics,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:mcq,true_false',
            'difficulty'    => 'required|in:Easy,Medium,Hard',
            'explanation'   => 'nullable|string',
        ];

        if ($request->input('question_type') === 'mcq') {
            $rules += [
                'choice_a'       => 'required|string',
                'choice_b'       => 'required|string',
                'choice_c'       => 'required|string',
                'choice_d'       => 'required|string',
                'correct_answer' => 'required|in:a,b,c,d',
            ];
        } else {
            $rules['tf_answer'] = 'required|in:true,false';
        }

        return $request->validate($rules);
    }

    /**
     * Insert the choice rows for a question based on its type.
     */
    private function saveChoices(Question $question, array $data): void
    {
        if ($data['question_type'] === 'mcq') {
            foreach (['a', 'b', 'c', 'd'] as $label) {
                $question->choices()->create([
                    'choice_label' => strtoupper($label),
                    'choice_text'  => $data["choice_{$label}"],
                    'is_correct'   => $data['correct_answer'] === $label,
                ]);
            }
            return;
        }

        // True / False stored as two choices.
        $question->choices()->create([
            'choice_label' => 'A', 'choice_text' => 'True',
            'is_correct'   => $data['tf_answer'] === 'true',
        ]);
        $question->choices()->create([
            'choice_label' => 'B', 'choice_text' => 'False',
            'is_correct'   => $data['tf_answer'] === 'false',
        ]);
    }
}
