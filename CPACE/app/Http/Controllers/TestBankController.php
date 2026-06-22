<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionVariant;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\QuestionParaphraser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $stats = [
            'total'     => Question::count(),
            'active'    => Question::where('is_active', true)->count(),
            'draft'     => Question::where('is_active', false)->count(),
            'this_week' => Question::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('faculty.test-bank', [
            'questions' => $questions,
            'stats'     => $stats,
            'subjects'  => Subject::orderBy('id')->get(),
            'filters'   => $request->only(['search', 'subject', 'type', 'difficulty', 'status']),
        ]);
    }

    /**
     * Build the Test Bank question query with all the active filters applied.
     * Shared by the listing and the export so both honour the same filters.
     */
    private function filteredQuery(Request $request)
    {
        $query = Question::query()
            ->select('questions.*', 'topics.name as topic_name', 'subjects.code as subject_code', 'subjects.id as subject_id')
            ->withCount('variants')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id');

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
            'subjects'    => Subject::with('topics')->orderBy('id')->get(),
            'editMode'    => false,
        ]);
    }

    /**
     * Persist a new question and its choices.
     */
    public function store(Request $request)
    {
        $data = $this->validateQuestion($request);

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

        return view('faculty.question-form', [
            'subjects'       => Subject::with('topics')->orderBy('id')->get(),
            'editMode'       => true,
            'question'       => $question,
            'currentSubject' => $question->topic->subject_id,
        ]);
    }

    /**
     * Update an existing question and replace its choices.
     */
    public function update(Request $request, int $id)
    {
        $question = Question::findOrFail($id);
        $data = $this->validateQuestion($request);

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
        Question::findOrFail($id)->delete();

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
        $question = Question::findOrFail($id);

        $data = $request->validate([
            'variant_text' => 'required|string|min:5|max:1000',
        ]);

        $question->variants()->create([
            'variant_text' => trim($data['variant_text']),
            'source'       => 'faculty',
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
        $variant = QuestionVariant::where('question_id', $id)->findOrFail($variantId);
        $variant->update(['is_active' => ! $variant->is_active]);

        return redirect()->route('faculty.question.variants', $id);
    }

    /**
     * Delete a variant.
     */
    public function destroyVariant(int $id, int $variantId)
    {
        QuestionVariant::where('question_id', $id)->where('id', $variantId)->delete();

        return redirect()
            ->route('faculty.question.variants', $id)
            ->with('status', 'Variant removed.');
    }

    /**
     * Return a rule-based draft variant the faculty can edit (the "intelligent"
     * starting point). A fresh seed each call gives a different draft.
     */
    public function suggestVariant(int $id)
    {
        $question = Question::findOrFail($id);

        $draft = QuestionParaphraser::rephrase(
            $question->question_text,
            random_int(1, 2_000_000_000),
            $question->question_type
        );

        return response()->json(['draft' => $draft]);
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
