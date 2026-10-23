<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionScenario;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use App\Support\Auditor;
use App\Support\CurriculumScope;
use App\Support\CustomQuestionParser;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Scenarios (case problems) in the Test Bank: one situation, any number of
 * questions under it, written and edited together on one page.
 *
 * Shared by faculty and the Program Chair like the question form is: the same
 * routes exist under both prefixes and a chair may manage every subject.
 */
class ScenarioController extends Controller
{
    private const DIFFICULTY_TO_DB = ['Easy' => 'easy', 'Medium' => 'moderate', 'Hard' => 'difficult'];
    private const DIFFICULTY_TO_LABEL = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];

    private const NOT_ASSIGNED = "You're not assigned to this subject, so you can't manage its questions. Ask your Program Chair for access if you think this is a mistake.";
    private const ARCHIVED = 'That topic belongs to an archived curriculum, which is kept as read-only history. Use the current (or draft) curriculum instead.';

    public function create()
    {
        return view('faculty.scenario-form', [
            'editMode' => false,
            'isChair' => Auth::user()->isChair(),
            'subjects' => $this->subjectsFor(Auth::user())
                ->with(['topics' => fn ($q) => $q->inCurriculum(CurriculumScope::testBankVersionId())->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('id')->get(),
            'scenario' => $this->blank(),
            'currentSubject' => null,
        ]);
    }

    public function edit(int $id)
    {
        $scenario = QuestionScenario::with(['topic', 'questions.choices'])->findOrFail($id);

        if (! $this->canManage(Auth::user(), $scenario->topic->subject_id)) {
            return redirect()->route($this->backRoute())->with('warning', self::NOT_ASSIGNED);
        }

        return view('faculty.scenario-form', [
            'editMode' => true,
            'isChair' => Auth::user()->isChair(),
            'subjects' => $this->subjectsFor(Auth::user())
                ->with(['topics' => fn ($q) => $q->inCurriculum($scenario->topic->curriculum_version_id)])
                ->orderBy('id')->get(),
            'scenario' => [
                'id' => $scenario->id,
                'title' => $scenario->title,
                'body' => $scenario->body,
                'is_active' => (bool) $scenario->is_active,
                'topic_id' => $scenario->topic_id,
                'questions' => $scenario->questions->map(fn (Question $q) => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type ?: 'mcq',
                    'difficulty' => self::DIFFICULTY_TO_LABEL[$q->difficulty] ?? 'Medium',
                    'explanation' => $q->explanation,
                    'choices' => $q->choices->sortBy('choice_label')->values()->map(fn ($c) => [
                        'text' => $c->choice_text,
                        'is_correct' => (bool) $c->is_correct,
                    ])->all(),
                ])->values()->all(),
            ],
            'currentSubject' => $scenario->topic->subject_id,
        ]);
    }

    public function store(Request $request)
    {
        [$data, $topic, $rows] = $this->validated($request);

        $scenario = DB::transaction(function () use ($data, $rows) {
            $scenario = QuestionScenario::create([
                'topic_id' => $data['topic_id'],
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'body' => $data['body'],
                'is_active' => $data['is_active'],
            ]);
            $this->syncQuestions($scenario, $rows);

            return $scenario;
        });

        Auditor::log(Auth::user(), 'scenario_added', 'Added a scenario with ' . count($rows) . ' question(s) to "' . $topic->name . '".', 'QuestionScenario', $scenario->id);

        return redirect()->route($this->backRoute())->with('status', 'Scenario saved with ' . count($rows) . ' question' . (count($rows) === 1 ? '' : 's') . '.');
    }

    public function update(Request $request, int $id)
    {
        $scenario = QuestionScenario::with('topic')->findOrFail($id);
        if (! $this->canManage(Auth::user(), $scenario->topic->subject_id)) {
            return redirect()->route($this->backRoute())->with('warning', self::NOT_ASSIGNED);
        }
        [$data, $topic, $rows] = $this->validated($request, $scenario);

        DB::transaction(function () use ($scenario, $data, $rows) {
            $scenario->update([
                'topic_id' => $data['topic_id'],
                'title' => $data['title'],
                'body' => $data['body'],
                'is_active' => $data['is_active'],
            ]);
            $this->syncQuestions($scenario, $rows);
        });

        Auditor::log(Auth::user(), 'scenario_updated', 'Updated a scenario in "' . $topic->name . '".', 'QuestionScenario', $scenario->id);

        return redirect()->route($this->backRoute())->with('status', 'Scenario updated.');
    }

    public function destroy(int $id)
    {
        $scenario = QuestionScenario::with('topic')->findOrFail($id);
        if (! $this->canManage(Auth::user(), $scenario->topic->subject_id)) {
            return redirect()->route($this->backRoute())->with('warning', self::NOT_ASSIGNED);
        }

        DB::transaction(function () use ($scenario) {
            $scenario->questions()->delete();
            $scenario->delete();
        });

        return redirect()->route($this->backRoute())->with('status', 'Scenario and its questions deleted.');
    }

    /** @return array{0: array<string, mixed>, 1: Topic, 2: array<int, array<string, mixed>>} */
    private function validated(Request $request, ?QuestionScenario $existing = null): array
    {
        $request->merge([
            'body' => RichText::sanitize($request->input('body')),
            'title' => trim((string) $request->input('title')) ?: null,
        ]);

        $data = $request->validate([
            'topic_id' => ['required', 'exists:topics,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:60000'],
            'questions_json' => ['required', 'string'],
        ], [
            'body.required' => 'Write the scenario first: the situation the questions are about.',
            'topic_id.required' => 'Pick a subject and topic for this scenario.',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $topic = Topic::findOrFail($data['topic_id']);
        if (! $this->canManage(Auth::user(), $topic->subject_id)) {
            throw ValidationException::withMessages(['topic_id' => self::NOT_ASSIGNED]);
        }
        $movedTopic = ! $existing || (int) $existing->topic_id !== (int) $topic->id;
        if ($movedTopic && $this->isArchivedTopic($topic)) {
            throw ValidationException::withMessages(['topic_id' => self::ARCHIVED]);
        }

        $decoded = json_decode($data['questions_json'], true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['questions_json' => 'The questions could not be read. Please try again.']);
        }
        if (count($decoded) < 1) {
            throw ValidationException::withMessages(['questions_json' => 'Add at least one question to this scenario.']);
        }

        $rows = [];
        foreach (array_values($decoded) as $i => $row) {
            $row = (array) $row;
            $parsed = CustomQuestionParser::parse($row, $i + 1, 'questions_json');
            $type = $parsed['question_type'];
            $choices = $parsed['choices'];
            if ($type === 'true_false') {
                // The bank stores True / False as two fixed choices.
                $choices[0]['text'] = 'True';
                $choices[1]['text'] = 'False';
                $choices = array_slice($choices, 0, 2);
            }
            $rows[] = [
                'id' => ! empty($row['id']) ? (int) $row['id'] : null,
                'question_text' => $parsed['question_text'],
                'question_type' => $type,
                'difficulty' => self::DIFFICULTY_TO_DB[$row['difficulty'] ?? 'Medium'] ?? 'moderate',
                'explanation' => $parsed['explanation'],
                'choices' => $choices,
            ];
        }

        return [$data, $topic, $rows];
    }

    /** Create, update and remove the scenario's questions so they match $rows, in that order. */
    private function syncQuestions(QuestionScenario $scenario, array $rows): void
    {
        $existing = $scenario->questions()->with('choices')->get()->keyBy('id');
        $kept = [];

        foreach ($rows as $position => $row) {
            $attrs = [
                'topic_id' => $scenario->topic_id,
                'scenario_id' => $scenario->id,
                'scenario_position' => $position,
                'question_text' => $row['question_text'],
                'question_type' => $row['question_type'],
                'difficulty' => $row['difficulty'],
                'explanation' => $row['explanation'],
                'is_active' => $scenario->is_active,
            ];

            if ($row['id'] && $existing->has($row['id'])) {
                $question = $existing[$row['id']];
                $question->update($attrs);
            } else {
                $question = Question::create($attrs + ['created_by' => Auth::id()]);
            }
            $kept[] = $question->id;
            $this->replaceChoices($question, $row['choices']);
        }

        $existing->keys()->diff($kept)->each(fn ($id) => $existing[$id]->delete());
    }

    /** Same label-keyed update the question form uses, so recorded answers keep pointing at their choice. */
    private function replaceChoices(Question $question, array $choices): void
    {
        $existing = $question->choices()->get()->keyBy('choice_label');
        $labels = [];
        foreach (array_values($choices) as $i => $choice) {
            $label = chr(65 + $i);
            $labels[] = $label;
            $row = ['choice_text' => $choice['text'], 'is_correct' => (bool) $choice['is_correct']];
            if ($existing->has($label)) {
                $existing[$label]->update($row);
            } else {
                $question->choices()->create(['choice_label' => $label] + $row);
            }
        }
        $obsolete = $existing->keys()->diff($labels);
        if ($obsolete->isNotEmpty()) {
            $question->choices()->whereIn('choice_label', $obsolete->all())->delete();
        }
    }

    private function blank(): array
    {
        return ['id' => null, 'title' => '', 'body' => '', 'is_active' => true, 'topic_id' => null, 'questions' => []];
    }

    private function subjectsFor(User $user)
    {
        return $user->isChair() ? Subject::where('is_active', true) : $user->assignedSubjects()->where('is_active', true);
    }

    private function canManage(User $user, int $subjectId): bool
    {
        return $user->isChair() || $user->assignedSubjects()->where('subjects.id', $subjectId)->exists();
    }

    private function isArchivedTopic(Topic $topic): bool
    {
        if (! CurriculumScope::enabled() || $topic->curriculum_version_id === null) {
            return false;
        }

        return ! in_array((int) $topic->curriculum_version_id, array_filter([CurriculumScope::activeId(), CurriculumScope::draftId()]), true);
    }

    private function backRoute(): string
    {
        return Auth::user()->isChair() ? 'chair.ai-review' : 'faculty.test-bank';
    }
}
