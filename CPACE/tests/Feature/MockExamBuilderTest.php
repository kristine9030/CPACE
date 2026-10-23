<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Faculty side of the mock exam workflow: who may build, the one-subject and
 * 100-item rules, the auto-picker, and the hand-off to the Program Chair.
 */
class MockExamBuilderTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $otherSubjectId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->otherSubjectId = $this->makeSubject('AUD', 'Auditing');
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_a_faculty_member_can_only_build_for_subjects_they_are_assigned_to(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.store'), ['subject_id' => $this->subjectId])
            ->assertRedirect();

        // AUD belongs to someone else.
        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.store'), ['subject_id' => $this->otherSubjectId])
            ->assertForbidden();

        $this->assertSame(1, MockExam::count());
        $this->assertSame($this->subjectId, MockExam::first()->subject_id);
    }

    public function test_creating_a_draft_records_who_started_it_in_the_audit_trail(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)->post(route('faculty.mock-exams.store'), ['subject_id' => $this->subjectId]);

        $exam = MockExam::first();
        $audit = DB::table('mock_exam_audits')->where('exam_id', $exam->id)->first();

        $this->assertSame(MockExamAudit::ACTION_CREATED, $audit->action);
        $this->assertSame($faculty->id, (int) $audit->user_id);
    }

    public function test_an_exam_cannot_hold_more_than_one_hundred_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);

        // 101 real bank questions, all posted at once.
        $ids = collect(range(1, 101))->map(fn () => $this->makeQuestion($topicId))->all();

        $this->actingAs($faculty)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, $ids))
            ->assertSessionHasErrors('items_json');

        $this->assertSame(0, DB::table('mock_exam_items')->count());
    }

    public function test_fewer_than_one_hundred_questions_is_accepted(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);
        $ids = collect(range(1, 70))->map(fn () => $this->makeQuestion($topicId))->all();

        $this->actingAs($faculty)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, $ids))
            ->assertSessionHasNoErrors();

        $this->assertSame(70, DB::table('mock_exam_items')->count());
        $this->assertSame(70, MockExam::find($exam->id)->total_items);
    }

    public function test_items_are_snapshotted_from_the_bank_not_from_the_posted_payload(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $questionId = $this->makeQuestion($topicId, 'easy', 'The real question');
        $exam = $this->draft($faculty->id);

        // A tampered payload tries to smuggle in its own text and answer key.
        $this->actingAs($faculty)->put(route('faculty.mock-exams.update', $exam), array_merge(
            $this->payload($exam, $topicId, []),
            ['items_json' => json_encode([[
                'source_question_id' => $questionId,
                'question_text' => 'Injected question',
                'choices' => [['label' => 'A', 'text' => 'Injected', 'is_correct' => true]],
            ]])]
        ));

        $item = DB::table('mock_exam_items')->first();
        $this->assertSame('The real question', $item->question_text);
        $this->assertStringNotContainsString('Injected', $item->choices);
    }

    public function test_auto_pick_spreads_evenly_across_the_selected_topics(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicA = $this->makeTopic($this->subjectId, 'Inventory');
        $topicB = $this->makeTopic($this->subjectId, 'Leases');

        foreach ([$topicA, $topicB] as $topic) {
            foreach (['easy', 'moderate', 'difficult'] as $difficulty) {
                for ($i = 0; $i < 10; $i++) {
                    $this->makeQuestion($topic, $difficulty);
                }
            }
        }

        $response = $this->actingAs($faculty)->postJson(route('faculty.mock-exams.auto-pick'), [
            'subject_id' => $this->subjectId,
            'topic_ids' => [$topicA, $topicB],
            'count' => 20,
        ])->assertOk();

        $questions = collect($response->json('questions'));
        $this->assertCount(20, $questions);
        $this->assertFalse($response->json('short'));
        // 20 across 2 topics is 10 each.
        $this->assertSame(10, $questions->where('topic_id', $topicA)->count());
        $this->assertSame(10, $questions->where('topic_id', $topicB)->count());
    }

    public function test_auto_pick_reports_a_shortfall_instead_of_silently_returning_fewer(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        for ($i = 0; $i < 5; $i++) {
            $this->makeQuestion($topicId);
        }

        $response = $this->actingAs($faculty)->postJson(route('faculty.mock-exams.auto-pick'), [
            'subject_id' => $this->subjectId,
            'topic_ids' => [$topicId],
            'count' => 40,
        ])->assertOk();

        $this->assertTrue($response->json('short'));
        $this->assertSame(5, $response->json('returned'));
        $this->assertSame(40, $response->json('requested'));
    }

    public function test_an_exam_cannot_be_submitted_for_review_without_a_schedule_or_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $exam = $this->draft($faculty->id);

        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.submit', $exam))
            ->assertSessionHasErrors('scheduled_at');

        $exam->update(['scheduled_at' => now()->addWeek()]);

        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.submit', $exam))
            ->assertSessionHasErrors('items_json');

        $this->assertTrue(MockExam::find($exam->id)->isDraft());
    }

    public function test_submitting_for_review_moves_the_exam_and_notifies_the_chair(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id, ['scheduled_at' => now()->addWeek()]);
        $this->seedItems($exam->id, $topicId, 3);
        $exam->update(['total_items' => 3]);

        $this->actingAs($faculty)->post(route('faculty.mock-exams.submit', $exam))->assertRedirect();

        $this->assertSame(MockExam::STATUS_FOR_REVIEW, MockExam::find($exam->id)->status);
        $this->assertNotNull(MockExam::find($exam->id)->submitted_for_review_at);
        $this->assertSame(1, DB::table('notifications')->where('recipient_id', $chair->id)->count());
    }

    public function test_a_faculty_member_cannot_delete_an_exam_once_it_left_draft(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $exam = $this->draft($faculty->id, ['status' => MockExam::STATUS_FOR_REVIEW]);

        $this->actingAs($faculty)->delete(route('faculty.mock-exams.destroy', $exam))
            ->assertSessionHasErrors('exam');

        $this->assertSame(1, MockExam::count());
    }

    // ── hand-written questions and scenarios ─────────────────────────────

    private function custom(string $text, string $correct = 'A', array $extra = []): array
    {
        return $extra + [
            'question_text' => $text, 'question_type' => 'mcq', 'difficulty' => 'moderate', 'points' => 2, 'explanation' => 'Because.',
            'choices' => array_map(fn ($l) => ['text' => "Option {$l}", 'is_correct' => $l === $correct], ['A', 'B', 'C', 'D']),
        ];
    }

    private function save(MockExam $exam, int $topicId, array $rows): \Illuminate\Testing\TestResponse
    {
        $faculty = \App\Models\User::where('email', 'far@example.com')->first();

        return $this->actingAs($faculty)->put(route('faculty.mock-exams.update', $exam), array_merge(
            $this->payload($exam, $topicId, []),
            ['items_json' => json_encode($rows)]
        ));
    }

    public function test_a_faculty_member_can_write_a_question_straight_into_the_exam(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);

        $this->save($exam, $topicId, [$this->custom('<div>What is <b>depreciation</b>?</div>', 'C', ['topic_id' => $topicId])])->assertSessionHasNoErrors();

        $item = DB::table('mock_exam_items')->first();
        $this->assertNull($item->source_question_id);
        $this->assertStringContainsString('<b>depreciation</b>', $item->question_text);
        $this->assertSame(2, (int) $item->points);
        $this->assertSame($topicId, (int) $item->topic_id);
        $choices = json_decode($item->choices, true);
        $this->assertSame('C', collect($choices)->firstWhere('is_correct', true)['label']);
        $this->assertSame(1, MockExam::find($exam->id)->total_items);
        $this->assertSame(0, DB::table('questions')->count(), 'it lives on the exam only, not in the Test Bank');
    }

    public function test_a_written_question_must_be_complete(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);

        $noAnswer = $this->custom('Q?');
        $noAnswer['choices'] = array_map(fn ($c) => ['text' => $c['text'], 'is_correct' => false], $noAnswer['choices']);
        $this->save($exam, $topicId, [$noAnswer])->assertSessionHasErrors('items_json');

        $this->save($exam, $topicId, [$this->custom('Q?', 'A', ['choices' => [['text' => 'only one', 'is_correct' => true]]])])->assertSessionHasErrors('items_json');
        $this->assertSame(0, DB::table('mock_exam_items')->count());
    }

    public function test_a_topic_from_another_subject_is_not_kept_on_a_written_question(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $foreign = $this->makeTopic($this->otherSubjectId, 'Evidence');
        $exam = $this->draft($faculty->id);

        $this->save($exam, $topicId, [$this->custom('Q?', 'A', ['topic_id' => $foreign])])->assertSessionHasNoErrors();

        $this->assertNull(DB::table('mock_exam_items')->value('topic_id'));
    }

    public function test_bank_and_written_questions_keep_the_order_they_were_arranged_in(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $bank = $this->makeQuestion($topicId, 'easy', 'From the bank');
        $exam = $this->draft($faculty->id);

        $this->save($exam, $topicId, [$this->custom('Mine first'), ['source_question_id' => $bank], $this->custom('Mine last')])->assertSessionHasNoErrors();

        $this->assertSame(['Mine first', 'From the bank', 'Mine last'], DB::table('mock_exam_items')->orderBy('sort_order')->pluck('question_text')->all());
    }

    public function test_picking_one_bank_scenario_question_brings_the_whole_scenario(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        [$scenarioId, $ids] = $this->makeScenario($topicId, '<div>ABC Corp. bought equipment</div>', 4);
        DB::table('questions')->where('id', $ids[3])->update(['is_active' => false]); // retired: stays out
        $loose = $this->makeQuestion($topicId, 'easy', 'Loose');
        $exam = $this->draft($faculty->id);

        $this->save($exam, $topicId, [['source_question_id' => $loose], ['source_question_id' => $ids[2]]])->assertSessionHasNoErrors();

        $items = DB::table('mock_exam_items')->orderBy('sort_order')->get();
        $this->assertSame(['Loose', 'Scenario question 1', 'Scenario question 2', 'Scenario question 3'], $items->pluck('question_text')->all());
        $this->assertSame([null, 's' . $scenarioId, 's' . $scenarioId, 's' . $scenarioId], $items->pluck('scenario_group')->all());
        $this->assertSame('<div>ABC Corp. bought equipment</div>', $items[1]->scenario_text);
        $this->assertSame(4, MockExam::find($exam->id)->total_items);
    }

    public function test_changing_the_bank_scenario_later_does_not_change_an_exam_already_built(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        [$scenarioId, $ids] = $this->makeScenario($topicId, '<div>Original situation</div>', 2);
        $exam = $this->draft($faculty->id);
        $this->save($exam, $topicId, [['source_question_id' => $ids[0]]])->assertSessionHasNoErrors();

        DB::table('question_scenarios')->where('id', $scenarioId)->update(['body' => '<div>Rewritten later</div>']);

        $this->assertStringContainsString('Original situation', DB::table('mock_exam_items')->value('scenario_text'));
    }

    public function test_written_scenarios_stay_together_with_one_shared_text(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);
        $in = fn (string $t) => $this->custom($t, 'A', ['scenario_group' => 'c1', 'scenario_text' => '<div>My case</div>']);

        $this->save($exam, $topicId, [$in('Case 1'), $this->custom('Solo'), $in('Case 2') + ['scenario_text' => '']])->assertSessionHasNoErrors();

        $items = DB::table('mock_exam_items')->orderBy('sort_order')->get();
        $this->assertSame(['Case 1', 'Case 2', 'Solo'], $items->pluck('question_text')->all());
        $this->assertSame(['<div>My case</div>', '<div>My case</div>', null], $items->pluck('scenario_text')->all());

        $this->save($exam, $topicId, [$this->custom('X', 'A', ['scenario_group' => 'c2', 'scenario_text' => '<div><br></div>'])])->assertSessionHasErrors('items_json');
    }

    public function test_a_scenario_counts_all_of_its_questions_toward_the_one_hundred_cap(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        [, $ids] = $this->makeScenario($topicId, '<div>Case</div>', 5);
        $loose = collect(range(1, 97))->map(fn () => ['source_question_id' => $this->makeQuestion($topicId)])->all();
        $exam = $this->draft($faculty->id);

        // 97 + a 5-question scenario = 102, even though only 98 rows were posted.
        $this->save($exam, $topicId, array_merge($loose, [['source_question_id' => $ids[0]]]))->assertSessionHasErrors('items_json');
        $this->assertSame(0, DB::table('mock_exam_items')->count());
    }

    public function test_the_builders_question_list_groups_scenario_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        [$scenarioId] = $this->makeScenario($topicId, '<div>Situation <b>text</b></div>', 3);
        $this->makeQuestion($topicId, 'easy', 'Loose');

        $res = $this->actingAs($faculty)->getJson(route('faculty.mock-exams.bank-questions', ['subject_id' => $this->subjectId, 'topic_ids' => [$topicId]]))->assertOk();

        $q = collect($res->json('questions'));
        $this->assertSame(4, $q->count());
        $this->assertSame(3, $q->where('scenario_group', 's' . $scenarioId)->count());
        $this->assertSame('Situation text', $q->firstWhere('scenario_group', 's' . $scenarioId)['scenario_text']);
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function draft(int $facultyId, array $overrides = []): MockExam
    {
        return MockExam::create(array_merge([
            'subject_id' => $this->subjectId,
            'created_by' => $facultyId,
            'title' => 'FAR Mock Exam',
            'status' => MockExam::STATUS_DRAFT,
            'duration_minutes' => 180,
        ], $overrides));
    }

    private function payload(MockExam $exam, int $topicId, array $questionIds): array
    {
        return [
            'version' => $exam->version,
            'title' => $exam->title,
            'scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'duration_minutes' => 180,
            'topic_mode' => 'manual',
            'question_mode' => 'manual',
            'topic_ids' => [$topicId],
            'items_json' => json_encode(array_map(fn ($id) => ['source_question_id' => $id], $questionIds)),
        ];
    }

    private function seedItems(int $examId, int $topicId, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            DB::table('mock_exam_items')->insert([
                'exam_id' => $examId,
                'source_question_id' => $this->makeQuestion($topicId),
                'topic_id' => $topicId,
                'question_text' => "Q{$i}",
                'choices' => json_encode([
                    ['label' => 'A', 'text' => 'Right', 'is_correct' => true],
                    ['label' => 'B', 'text' => 'Wrong', 'is_correct' => false],
                ]),
                'points' => 1,
                'sort_order' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
