<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Concerns\EditsMockExams;
use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\MockExam;
use App\Models\MockExamAudit;
use App\Models\Question;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use App\Support\MockExamAuditor;
use App\Support\MockExamQuestionPicker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Faculty side of the mock exam workflow: assemble a per-subject exam from the
 * Test Bank, then hand it to the Program Chair for review.
 *
 * A mock exam is always scoped to exactly one subject - subjects are never
 * combined - and a faculty member can only build for subjects they are
 * assigned to. Within a subject, every assigned faculty collaborates on the
 * same exam, so `created_by` records who started it but grants no exclusive
 * rights; MockExam::canBeEditedBy() is the real gate.
 */
class MockExamController extends Controller
{
    use EditsMockExams, SubjectTheme;

    /**
     * Index: one card per subject the faculty is assigned to, with a count of
     * the exams sitting in each state. Mirrors the Class Quizzes index so the
     * two features read as siblings.
     */
    public function index()
    {
        $faculty = Auth::user();
        $subjects = $this->subjectsFor($faculty);

        $exams = MockExam::whereIn('subject_id', $subjects->pluck('id'))
            ->withCount('items')
            ->get()
            ->groupBy('subject_id');

        $cards = $subjects->map(function (Subject $subject) use ($exams) {
            $group = $exams->get($subject->id, collect());

            return [
                'subject' => $subject,
                'icon' => self::subjectIcon($subject->code),
                'theme' => self::theme($subject->code),
                'total' => $group->count(),
                'draft' => $group->where('status', MockExam::STATUS_DRAFT)->count(),
                'for_review' => $group->where('status', MockExam::STATUS_FOR_REVIEW)->count(),
                'published' => $group->where('status', MockExam::STATUS_PUBLISHED)->count(),
                'next' => $group->where('status', MockExam::STATUS_PUBLISHED)
                    ->whereNotNull('scheduled_at')
                    ->sortBy('scheduled_at')
                    ->first(),
            ];
        })->values();

        return view('faculty.mock-exams', compact('cards'));
    }

    /** One subject's exams, newest first. */
    public function subject(Subject $subject)
    {
        $faculty = Auth::user();
        $this->assertAssigned($faculty, $subject->id);

        $exams = MockExam::with(['creator', 'publisher', 'event'])
            ->withCount(['items', 'attempts as submitted_count' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->where('subject_id', $subject->id)
            ->orderByRaw("CASE status WHEN 'draft' THEN 0 WHEN 'for_review' THEN 1 WHEN 'published' THEN 2 ELSE 3 END")
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        return view('faculty.mock-exam-subject', [
            'subject' => $subject,
            'icon' => self::subjectIcon($subject->code),
            'theme' => self::theme($subject->code),
            'exams' => $exams,
        ]);
    }

    /**
     * Step 1 of the wizard: create the draft as soon as a subject is chosen.
     *
     * The draft is persisted immediately (rather than holding the whole wizard
     * client-side) so topics, questions and the audit trail have somewhere to
     * live, and so a colleague can pick the work up mid-build.
     */
    public function store(Request $request)
    {
        $faculty = Auth::user();

        $data = $request->validate([
            'subject_id' => ['required', 'integer'],
            'title' => ['nullable', 'string', 'max:150'],
        ]);

        $subject = Subject::findOrFail((int) $data['subject_id']);
        $this->assertAssigned($faculty, $subject->id);

        $exam = MockExam::create([
            'subject_id' => $subject->id,
            'created_by' => $faculty->id,
            'title' => trim((string) ($data['title'] ?? '')) ?: $subject->code . ' Mock Exam',
            'status' => MockExam::STATUS_DRAFT,
            'duration_minutes' => MockExam::DEFAULT_DURATION,
            'topic_mode' => MockExam::MODE_MANUAL,
            'question_mode' => MockExam::MODE_MANUAL,
        ]);

        MockExamAuditor::record($exam, $faculty, MockExamAudit::ACTION_CREATED, $subject->code . ' mock exam started');

        return redirect()->route('faculty.mock-exams.build', $exam)
            ->with('status', 'Draft created — now choose the topics.');
    }

    /** Steps 2-4: the build screen for an existing draft. */
    public function build(MockExam $mockExam)
    {
        $faculty = Auth::user();
        abort_unless($mockExam->canBeViewedBy($faculty), 403, 'You are not assigned to that subject.');

        $mockExam->load(['subject', 'items', 'topics', 'creator', 'audits.user']);

        return view('faculty.mock-exam-build', [
            'exam' => $mockExam,
            'subject' => $mockExam->subject,
            'icon' => self::subjectIcon($mockExam->subject?->code),
            'theme' => self::theme($mockExam->subject?->code),
            'topicTree' => $this->topicsFor($mockExam->subject_id),
            'selectedTopics' => $mockExam->topics->pluck('id')->all(),
            'readOnly' => ! $mockExam->canBeEditedBy($faculty),
            'bankCounts' => $this->bankCounts($mockExam->subject_id),
        ]);
    }

    /**
     * Save the build. One endpoint for all three editable parts (settings,
     * topics, items) because the wizard posts them together, and because a
     * single write path means a single place that has to check the lock and
     * write the audit trail.
     */
    public function update(Request $request, MockExam $mockExam)
    {
        $faculty = Auth::user();
        $this->assertEditable($mockExam, $faculty);

        $this->saveMockExam($request, $mockExam, $faculty);

        // The builder's "Submit for review" saves and hands over in one step, so
        // the Chair always reviews exactly what was on the faculty's screen.
        if ($request->boolean('then_submit')) {
            return $this->submitForReview($mockExam->refresh());
        }

        return redirect()->route('faculty.mock-exams.build', $mockExam)
            ->with('status', 'Mock exam saved.');
    }

    /** Hand the exam to the Program Chair. */
    public function submitForReview(MockExam $mockExam)
    {
        $faculty = Auth::user();
        $this->assertEditable($mockExam, $faculty);

        $this->assertExamComplete($mockExam);

        $mockExam->update([
            'status' => MockExam::STATUS_FOR_REVIEW,
            'submitted_for_review_at' => now(),
            'review_note' => null,
        ]);

        MockExamAuditor::record(
            $mockExam,
            $faculty,
            MockExamAudit::ACTION_SUBMITTED,
            $mockExam->total_items . ' questions, ' . $mockExam->scheduled_at->format('M j, Y g:i A')
        );

        $this->notifyChairs($mockExam, $faculty);

        return redirect()->route('faculty.mock-exams.subject', $mockExam->subject_id)
            ->with('status', 'Submitted to the Program Chair for review.');
    }

    /** A draft can be discarded; anything further along belongs to the Chair. */
    public function destroy(MockExam $mockExam)
    {
        $faculty = Auth::user();
        abort_unless($mockExam->canBeViewedBy($faculty), 403, 'You are not assigned to that subject.');

        if (! $mockExam->isDraft()) {
            return back()->withErrors(['exam' => 'Only a draft can be deleted. Ask the Program Chair to return or close this exam first.']);
        }

        $subjectId = $mockExam->subject_id;
        $mockExam->delete();

        return redirect()->route('faculty.mock-exams.subject', $subjectId)
            ->with('status', 'Draft deleted.');
    }

    // ── JSON endpoints used by the builder ───────────────────────────────────

    /**
     * Test Bank questions for the chosen topics, for the manual picker.
     * Mirrors FacultyQuizController::bankQuestions()'s shape.
     */
    public function bankQuestions(Request $request)
    {
        $faculty = Auth::user();
        $subjectId = (int) $request->input('subject_id');
        $this->assertAssigned($faculty, $subjectId);

        $topicIds = $this->validatedTopicIds((array) $request->input('topic_ids', []), $subjectId);
        if ($topicIds === []) {
            return response()->json(['questions' => []]);
        }

        $questions = Question::with(['choices', 'topic'])
            ->whereIn('topic_id', $topicIds)
            ->where('is_active', true)
            ->orderBy('topic_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'questions' => $questions->map(fn (Question $q) => $this->questionPayload($q))->values(),
        ]);
    }

    /**
     * Run the auto-picker and hand the chosen questions back in the same
     * shape as the manual picker, so the builder treats them identically.
     */
    public function autoPick(Request $request)
    {
        $faculty = Auth::user();
        $subjectId = (int) $request->input('subject_id');
        $this->assertAssigned($faculty, $subjectId);

        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:' . MockExam::MAX_ITEMS],
            'topic_ids' => ['required', 'array', 'min:1'],
            'topic_ids.*' => ['integer'],
        ], [
            'count.max' => 'A mock exam can have at most ' . MockExam::MAX_ITEMS . ' questions.',
            'topic_ids.min' => 'Choose at least one topic first.',
        ]);

        $topicIds = $this->validatedTopicIds($data['topic_ids'], $subjectId);
        $picked = MockExamQuestionPicker::pick($topicIds, (int) $data['count']);

        return response()->json([
            'questions' => $picked->map(fn (Question $q) => $this->questionPayload($q))->values(),
            'requested' => (int) $data['count'],
            'returned' => $picked->count(),
            // The builder surfaces this so faculty know the bank ran dry
            // rather than silently shipping a shorter exam.
            'short' => $picked->count() < (int) $data['count'],
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function subjectsFor(User $faculty)
    {
        return $faculty->assignedSubjects()->where('subjects.is_active', true)->orderBy('subjects.id')->get();
    }

    private function assertAssigned(User $faculty, int $subjectId): void
    {
        abort_unless(
            $faculty->assignedSubjects()->where('subjects.id', $subjectId)->exists(),
            403,
            'You are not assigned to that subject.'
        );
    }

    private function assertEditable(MockExam $exam, User $faculty): void
    {
        abort_unless($exam->canBeViewedBy($faculty), 403, 'You are not assigned to that subject.');

        if (! $exam->isEditable()) {
            abort(403, 'This mock exam has been published and can no longer be edited.');
        }
    }

    /**
     * Active topics for the subject, nested into a tree. Topics are
     * hierarchical here (topics.parent_id), so the picker has to show the
     * same structure faculty see everywhere else rather than a flat list.
     */
    private function topicsFor(int $subjectId)
    {
        $flat = Topic::where('subject_id', $subjectId)
            ->where('is_active', true)
            ->withCount(['questions as bank_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Topic::buildTree($flat);
    }

    /** Totals shown in the builder so faculty can see what the bank can support. */
    private function bankCounts(int $subjectId): array
    {
        $rows = Question::join('topics', 'questions.topic_id', '=', 'topics.id')
            ->where('topics.subject_id', $subjectId)
            ->where('questions.is_active', true)
            ->selectRaw('questions.difficulty, COUNT(*) as total')
            ->groupBy('questions.difficulty')
            ->pluck('total', 'difficulty');

        return [
            'easy' => (int) $rows->get('easy', 0),
            'moderate' => (int) $rows->get('moderate', 0),
            'difficult' => (int) $rows->get('difficult', 0),
            'total' => (int) $rows->sum(),
        ];
    }

    /** @return array<string, mixed> */
    private function questionPayload(Question $question): array
    {
        return [
            'id' => $question->id,
            'topic_id' => $question->topic_id,
            'topic' => $question->topic?->name,
            'difficulty' => $question->difficulty,
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'explanation' => $question->explanation,
            'choices' => $question->choices->sortBy('choice_label')->values()->map(fn ($c) => [
                'label' => $c->choice_label,
                'text' => $c->choice_text,
                'is_correct' => (bool) $c->is_correct,
            ])->all(),
        ];
    }


    /** Let the Program Chair know something is waiting for review. */
    private function notifyChairs(MockExam $exam, User $faculty): void
    {
        $chairs = User::where('role_id', Role::ADMIN)->where('is_active', true)->pluck('id');
        if ($chairs->isEmpty()) {
            return;
        }

        $rows = $chairs->map(fn ($id) => [
            'recipient_id' => $id,
            'type' => 'mock_exam_review',
            'title' => 'Mock exam ready for review',
            'message' => $faculty->first_name . ' ' . $faculty->last_name . ' submitted the '
                . ($exam->subject?->code ?? 'subject') . ' mock exam for '
                . $exam->scheduled_at->format('M j, Y') . '.',
            'is_read' => 0,
            'reference_type' => 'mock_exam',
            'reference_id' => $exam->id,
            'created_at' => now(),
        ])->all();

        DB::table('notifications')->insert($rows);
    }
}
