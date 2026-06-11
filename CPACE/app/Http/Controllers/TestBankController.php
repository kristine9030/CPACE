<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
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
        $query = Question::query()
            ->select('questions.*', 'topics.name as topic_name', 'subjects.code as subject_code', 'subjects.id as subject_id')
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

        $questions = $query->orderByDesc('questions.id')->paginate(15)->withQueryString();

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
