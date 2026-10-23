<?php

namespace Tests\Feature;

use App\Support\ScenarioSupport;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Scenarios in the Test Bank: one situation, any number of questions, written
 * and edited together.
 */
class ScenarioBankTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $topicId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->topicId = $this->makeTopic($this->subjectId);
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    private function mcq(string $text, string $correct = 'A', string $difficulty = 'Medium', ?int $id = null): array
    {
        return [
            'id' => $id,
            'question_text' => $text,
            'question_type' => 'mcq',
            'difficulty' => $difficulty,
            'explanation' => 'Because.',
            'choices' => collect(['A', 'B', 'C', 'D'])->map(fn ($l) => ['text' => "Choice {$l}", 'is_correct' => $l === $correct])->all(),
        ];
    }

    private function form(array $questions, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $this->topicId,
            'title' => 'Impairment',
            'body' => '<div>ABC Corp. bought <b>equipment</b> for PHP 2,400,000.</div>',
            'is_active' => '1',
            'questions_json' => json_encode($questions),
        ], $overrides);
    }

    public function test_the_scenario_pages_open_for_faculty_and_the_chair(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $this->actingAs($faculty)->get(route('faculty.scenario.create'))->assertOk()->assertSee('New Scenario');

        $chair = $this->makeChair();
        $this->actingAs($chair)->get(route('chair.scenario.create'))->assertOk();
    }

    public function test_a_scenario_is_saved_with_all_of_its_questions_in_order(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)
            ->post(route('faculty.scenario.store'), $this->form([$this->mcq('First?'), $this->mcq('Second?', 'C', 'Hard'), $this->mcq('Third?', 'B', 'Easy')]))
            ->assertRedirect(route('faculty.test-bank'))
            ->assertSessionHasNoErrors();

        $scenario = DB::table('question_scenarios')->first();
        $this->assertSame($this->topicId, (int) $scenario->topic_id);
        $this->assertStringContainsString('ABC Corp.', $scenario->body);

        $questions = DB::table('questions')->where('scenario_id', $scenario->id)->orderBy('scenario_position')->get();
        $this->assertSame(['First?', 'Second?', 'Third?'], $questions->pluck('question_text')->all());
        $this->assertSame(['moderate', 'difficult', 'easy'], $questions->pluck('difficulty')->all());
        $this->assertSame(3, DB::table('question_choices')->whereIn('question_id', $questions->pluck('id'))->where('is_correct', true)->count());
        $this->assertSame('C', DB::table('question_choices')->where('question_id', $questions[1]->id)->where('is_correct', true)->value('choice_label'));
        $this->assertSame(1, (int) $questions[0]->is_active);
    }

    public function test_a_scenario_can_hold_far_more_than_a_handful_of_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $many = array_map(fn ($i) => $this->mcq("Q{$i}"), range(1, 60));

        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form($many))->assertSessionHasNoErrors();

        $this->assertSame(60, DB::table('questions')->whereNotNull('scenario_id')->count());
    }

    public function test_the_scenario_text_is_required_and_cleaned(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([$this->mcq('Q?')], ['body' => '<div><br></div>']))
            ->assertSessionHasErrors('body');
        $this->assertSame(0, DB::table('question_scenarios')->count());

        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([$this->mcq('Q?')], ['body' => '<div>Case <script>alert(1)</script><b onclick="x()">text</b></div>']))
            ->assertSessionHasNoErrors();
        $body = DB::table('question_scenarios')->value('body');
        $this->assertStringNotContainsString('script', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringContainsString('<b>text</b>', $body);
    }

    public function test_a_scenario_needs_at_least_one_valid_question(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([]))->assertSessionHasErrors('questions_json');

        $noAnswer = $this->mcq('Q?');
        $noAnswer['choices'] = array_map(fn ($c) => ['text' => $c['text'], 'is_correct' => false], $noAnswer['choices']);
        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([$noAnswer]))->assertSessionHasErrors('questions_json');

        $this->assertSame(0, DB::table('question_scenarios')->count());
        $this->assertSame(0, DB::table('questions')->count());
    }

    public function test_a_faculty_member_cannot_save_into_a_subject_they_are_not_assigned_to(): void
    {
        $other = $this->makeSubject('AUD', 'Auditing');
        $otherTopic = $this->makeTopic($other, 'Evidence');
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([$this->mcq('Q?')], ['topic_id' => $otherTopic]))
            ->assertSessionHasErrors('topic_id');

        $this->assertSame(0, DB::table('question_scenarios')->count());
    }

    public function test_editing_keeps_existing_questions_adds_new_ones_and_removes_deleted_ones(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([$this->mcq('One'), $this->mcq('Two'), $this->mcq('Three')]));
        $scenario = DB::table('question_scenarios')->first();
        $rows = DB::table('questions')->where('scenario_id', $scenario->id)->orderBy('scenario_position')->get();

        $this->actingAs($faculty)->put(route('faculty.scenario.update', $scenario->id), $this->form([
            $this->mcq('Three (edited)', 'D', 'Medium', $rows[2]->id),
            $this->mcq('Brand new'),
            $this->mcq('One', 'A', 'Medium', $rows[0]->id),
        ], ['body' => '<div>New situation</div>']))->assertSessionHasNoErrors();

        $after = DB::table('questions')->where('scenario_id', $scenario->id)->orderBy('scenario_position')->get();
        $this->assertSame(['Three (edited)', 'Brand new', 'One'], $after->pluck('question_text')->all());
        $this->assertSame($rows[2]->id, $after[0]->id, 'an edited question keeps its id (and so its history)');
        $this->assertSame($rows[0]->id, $after[2]->id);
        $this->assertNull(DB::table('questions')->where('id', $rows[1]->id)->first(), 'the question left out is deleted');
        $this->assertSame('D', DB::table('question_choices')->where('question_id', $rows[2]->id)->where('is_correct', true)->value('choice_label'));
        $this->assertStringContainsString('New situation', DB::table('question_scenarios')->value('body'));
    }

    public function test_the_edit_page_loads_the_scenario_with_its_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        [$scenarioId] = $this->makeScenario($this->topicId, 'Hello scenario body', 2);

        $this->actingAs($faculty)->get(route('faculty.scenario.edit', $scenarioId))
            ->assertOk()
            ->assertSee('Hello scenario body', false)
            ->assertSee('Scenario question 2', false);
    }

    public function test_deleting_a_scenario_removes_all_of_its_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        [$scenarioId, $ids] = $this->makeScenario($this->topicId, 'Body', 3);
        $standalone = $this->makeQuestion($this->topicId);

        $this->actingAs($faculty)->delete(route('faculty.scenario.destroy', $scenarioId))->assertRedirect(route('faculty.test-bank'));

        $this->assertSame(0, DB::table('question_scenarios')->count());
        $this->assertSame(0, DB::table('questions')->whereIn('id', $ids)->count());
        $this->assertNotNull(DB::table('questions')->where('id', $standalone)->first(), 'a standalone question is untouched');
    }

    public function test_deleting_the_last_question_of_a_scenario_removes_the_scenario(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        [$scenarioId, $ids] = $this->makeScenario($this->topicId, 'Body', 2);

        $this->actingAs($faculty)->delete(route('faculty.question.destroy', $ids[0]));
        $this->assertNotNull(DB::table('question_scenarios')->where('id', $scenarioId)->first(), 'still has a question');

        $this->actingAs($faculty)->delete(route('faculty.question.destroy', $ids[1]));
        $this->assertNull(DB::table('question_scenarios')->where('id', $scenarioId)->first());
    }

    public function test_the_question_form_sends_scenario_questions_to_the_scenario_page(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        [$scenarioId, $ids] = $this->makeScenario($this->topicId, 'Body', 2);

        $this->actingAs($faculty)->get(route('faculty.question.edit', $ids[0]))
            ->assertRedirect(route('faculty.scenario.edit', $scenarioId));
    }

    public function test_true_false_questions_are_stored_as_two_fixed_choices(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $tf = ['id' => null, 'question_text' => 'It was impaired.', 'question_type' => 'true_false', 'difficulty' => 'Easy', 'explanation' => '',
            'choices' => [['text' => 'whatever', 'is_correct' => false], ['text' => 'ignored', 'is_correct' => true]]];

        $this->actingAs($faculty)->post(route('faculty.scenario.store'), $this->form([$tf]))->assertSessionHasNoErrors();

        $q = DB::table('questions')->first();
        $choices = DB::table('question_choices')->where('question_id', $q->id)->orderBy('choice_label')->get();
        $this->assertSame(['True', 'False'], $choices->pluck('choice_text')->all());
        $this->assertSame([0, 1], $choices->pluck('is_correct')->map('intval')->all());
    }

    public function test_expanding_a_pick_always_brings_the_whole_scenario(): void
    {
        [$scenarioId, $ids] = $this->makeScenario($this->topicId, 'Body', 4);
        DB::table('questions')->where('id', $ids[3])->update(['is_active' => false]); // retired: stays out
        $alone = $this->makeQuestion($this->topicId);

        $picked = \App\Models\Question::with('choices')->whereIn('id', [$alone, $ids[2]])->get()->sortBy(fn ($q) => $q->id === $alone ? 0 : 1)->values();
        $expanded = ScenarioSupport::expand($picked);

        $this->assertSame([$alone, $ids[0], $ids[1], $ids[2]], $expanded->pluck('id')->all(), 'standalone first, then the scenario complete and in order, without the retired question');
    }

    public function test_scenario_groups_from_a_builder_are_made_contiguous_and_share_one_text(): void
    {
        $rows = [
            ['q' => 1, 'scenario_group' => 'g1', 'scenario_text' => '<div>Case A</div>'],
            ['q' => 2, 'scenario_group' => null],
            ['q' => 3, 'scenario_group' => 'g1', 'scenario_text' => ''],
            ['q' => 4, 'scenario_group' => 'g2', 'scenario_text' => '<div>Case B</div>'],
        ];

        $out = ScenarioSupport::normalise($rows, 'items_json');

        $this->assertSame([1, 3, 2, 4], array_column($out, 'q'), 'g1 stays together at its first position');
        $this->assertSame('<div>Case A</div>', $out[1]['scenario_text'], 'every member of a group carries the group text');
        $this->assertNull($out[2]['scenario_text']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        ScenarioSupport::normalise([['scenario_group' => 'g1', 'scenario_text' => '<div><br></div>']], 'items_json');
    }
}
