<?php

namespace App\Http\Controllers\Concerns;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use App\Models\MockExamItem;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Support\MockExamAuditor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The single write path for a mock exam's contents, shared by the faculty
 * builder and the Program Chair's review screen.
 *
 * Both roles edit the same exam with the same rules, so keeping one
 * implementation means the publish lock, the optimistic-lock check, the
 * "snapshot from the live question, never from the posted payload" rule and
 * the audit trail can't drift apart between them.
 */
trait EditsMockExams
{
    /**
     * Validate and persist a build, then write the audit entries describing
     * what actually changed. Returns nothing - callers redirect.
     */
    protected function saveMockExam(Request $request, MockExam $exam, User $actor): void
    {
        $this->assertFreshVersion($request, $exam);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'scheduled_at' => ['nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'topic_mode' => ['required', 'in:manual,auto,all'],
            'question_mode' => ['required', 'in:manual,auto,all'],
            'topic_ids' => ['array'],
            'topic_ids.*' => ['integer'],
            'items_json' => ['nullable', 'string'],
        ], [
            'title.required' => 'Please give the mock exam a title.',
            'duration_minutes.max' => 'An exam cannot run longer than 10 hours.',
        ]);

        $scheduledAt = ! empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
        $topicIds = $this->validatedTopicIds($data['topic_ids'] ?? [], $exam->subject_id);
        $items = $this->parseItems($request);

        if ($scheduledAt) {
            $this->assertSubjectFree($exam, $scheduledAt);
        }

        $before = [
            'title' => $exam->title,
            'scheduled_at' => $exam->scheduled_at?->toDateTimeString(),
            'duration_minutes' => $exam->duration_minutes,
        ];
        $topicsBefore = $exam->topics()->pluck('topics.id')->map('intval')->sort()->values()->all();
        $itemsBefore = $exam->items()->count();
        $sourcesBefore = $exam->items()->pluck('source_question_id')->filter()->map('intval')->all();

        DB::transaction(function () use ($exam, $data, $scheduledAt, $topicIds, $items) {
            $exam->update([
                'title' => trim($data['title']),
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => (int) $data['duration_minutes'],
                'topic_mode' => $data['topic_mode'],
                'question_mode' => $data['question_mode'],
                'total_items' => count($items),
                'version' => $exam->version + 1,
            ]);

            $exam->topics()->sync($topicIds);
            $this->replaceItems($exam, $items);
        });

        $this->writeUpdateAudits($exam, $actor, $before, $topicsBefore, $topicIds, $itemsBefore, $sourcesBefore, $items);
    }

    /**
     * Optimistic lock. Faculty assigned to the same subject collaborate on one
     * exam and the Chair edits it too, so a save built on a stale copy is
     * refused rather than silently overwriting someone else's work.
     */
    protected function assertFreshVersion(Request $request, MockExam $exam): void
    {
        $seen = $request->input('version');
        if ($seen === null || (int) $seen === $exam->version) {
            return;
        }

        $editor = $exam->audits()->with('user')->first()?->user?->first_name;

        throw ValidationException::withMessages([
            'version' => $editor
                ? "{$editor} changed this mock exam while you were editing. Reload the page to pick up their changes before saving."
                : 'This mock exam was changed while you were editing. Reload the page before saving.',
        ]);
    }

    /** Topics must belong to this exam's subject - never trust posted ids. */
    protected function validatedTopicIds(array $raw, int $subjectId): array
    {
        $ids = array_values(array_unique(array_map('intval', $raw)));
        if ($ids === []) {
            return [];
        }

        return Topic::whereIn('id', $ids)->where('subject_id', $subjectId)->pluck('id')->map('intval')->all();
    }

    /**
     * Decode the builder's item list. Items reference Test Bank questions by
     * id, and the snapshot is taken here from the live question rather than
     * from anything the client posted - so a tampered payload cannot smuggle
     * in altered question text or a different correct answer.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseItems(Request $request): array
    {
        $raw = $request->input('items_json');
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['items_json' => 'The question list could not be read. Please try again.']);
        }
        if (count($decoded) > MockExam::MAX_ITEMS) {
            throw ValidationException::withMessages([
                'items_json' => 'A mock exam can have at most ' . MockExam::MAX_ITEMS . ' questions.',
            ]);
        }

        $ids = array_values(array_unique(array_filter(array_map(
            fn ($row) => (int) ($row['source_question_id'] ?? $row['id'] ?? 0),
            $decoded
        ))));
        if ($ids === []) {
            return [];
        }

        $questions = Question::with('choices')->whereIn('id', $ids)->where('is_active', true)->get()->keyBy('id');

        $items = [];
        foreach ($ids as $id) {
            $question = $questions->get($id);
            // Silently drop rather than failing the whole save: a colleague
            // may have retired the question from the bank mid-build, and an
            // item with no correct answer would be unanswerable.
            if (! $question || $question->choices->count() < 2 || ! $question->choices->contains('is_correct', true)) {
                continue;
            }
            $items[] = MockExamItem::payloadFromQuestion($question, count($items));
        }

        return $items;
    }

    protected function replaceItems(MockExam $exam, array $items): void
    {
        $exam->items()->delete();
        foreach ($items as $order => $item) {
            MockExamItem::create($item + ['exam_id' => $exam->id, 'sort_order' => $order]);
        }
    }

    /**
     * A subject can only have one live exam per sitting date. Drafts are
     * exempt so two colleagues can each sketch an idea before one is scheduled.
     */
    protected function assertSubjectFree(MockExam $exam, Carbon $scheduledAt): void
    {
        $clash = MockExam::where('subject_id', $exam->subject_id)
            ->where('id', '!=', $exam->id)
            ->whereIn('status', [MockExam::STATUS_FOR_REVIEW, MockExam::STATUS_PUBLISHED])
            ->whereDate('scheduled_at', $scheduledAt->toDateString())
            ->exists();

        if ($clash) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'This subject already has a mock exam on that date. Pick another date, or edit the existing exam.',
            ]);
        }
    }

    /** Everything that must be true before an exam can go to review or be published. */
    protected function assertExamComplete(MockExam $exam): void
    {
        if ($exam->scheduled_at === null) {
            throw ValidationException::withMessages(['scheduled_at' => 'Set the exam date and time first.']);
        }
        if ($exam->scheduled_at->isPast()) {
            throw ValidationException::withMessages(['scheduled_at' => 'The exam date has already passed. Move it to a future date.']);
        }

        $count = $exam->items()->count();
        if ($count < 1) {
            throw ValidationException::withMessages(['items_json' => 'Add at least one question first.']);
        }
        if ($count > MockExam::MAX_ITEMS) {
            throw ValidationException::withMessages([
                'items_json' => 'A mock exam can have at most ' . MockExam::MAX_ITEMS . ' questions.',
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $items */
    protected function writeUpdateAudits(
        MockExam $exam,
        User $actor,
        array $before,
        array $topicsBefore,
        array $topicsAfter,
        int $itemsBefore,
        array $sourcesBefore,
        array $items
    ): void {
        $after = [
            'title' => $exam->title,
            'scheduled_at' => $exam->scheduled_at?->toDateTimeString(),
            'duration_minutes' => $exam->duration_minutes,
        ];

        if ($summary = MockExamAuditor::describeSettingsChange($before, $after)) {
            MockExamAuditor::record($exam, $actor, MockExamAudit::ACTION_SETTINGS, $summary);
        }

        sort($topicsAfter);
        if ($topicsBefore !== $topicsAfter) {
            MockExamAuditor::record(
                $exam,
                $actor,
                MockExamAudit::ACTION_TOPICS,
                'Topics: ' . count($topicsBefore) . ' → ' . count($topicsAfter)
            );
        }

        $sourcesAfter = array_map('intval', array_filter(array_column($items, 'source_question_id')));
        $added = count(array_diff($sourcesAfter, $sourcesBefore));
        $removed = count(array_diff($sourcesBefore, $sourcesAfter));
        if ($itemsBefore !== count($items) || $added > 0 || $removed > 0) {
            MockExamAuditor::record(
                $exam,
                $actor,
                MockExamAudit::ACTION_ITEMS,
                MockExamAuditor::describeItemChange($itemsBefore, count($items), $added, $removed)
            );
        }
    }
}
