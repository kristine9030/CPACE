<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Practice quizzes (random questions from the Test Bank) and scenarios: a scenario is always served whole,
 * its questions stay together, and the student sees the scenario with them.
 */
class ScenarioPracticeQuizTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $topicId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->string('practice_difficulty', 20)->nullable();
            $table->string('mode')->default('adaptive');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->integer('duration_secs')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('variant_text');
            $table->string('source')->default('faculty');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('selected_choice')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();
        });
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->topicId = $this->makeTopic($this->subjectId);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('question_variants');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_sessions');
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    private function start($student, int $count): int
    {
        $this->actingAs($student)->post(route('quiz.start'), [
            'subject_id' => $this->subjectId, 'mode' => 'timed', 'count' => $count, 'session_type' => 'testing',
        ]);

        return (int) DB::table('quiz_sessions')->where('student_id', $student->id)->orderByDesc('id')->value('id');
    }

    public function test_picking_one_scenario_question_serves_the_whole_scenario(): void
    {
        $student = $this->makeStudent();
        [, $ids] = $this->makeScenario($this->topicId, '<div>Practice situation</div>', 4);
        DB::table('questions')->where('id', $ids[3])->update(['is_active' => false]); // retired: not served

        // Asking for 1 question still brings the scenario's 3 active questions.
        $sessionId = $this->start($student, 1);

        $served = DB::table('quiz_answers')->where('session_id', $sessionId)->pluck('question_id')->sort()->values()->all();
        $this->assertSame([$ids[0], $ids[1], $ids[2]], $served);
        $this->assertSame(3, (int) DB::table('quiz_sessions')->where('id', $sessionId)->value('total_items'));
    }

    public function test_the_quiz_shows_the_scenario_and_keeps_its_questions_together(): void
    {
        $student = $this->makeStudent();
        $this->makeScenario($this->topicId, '<div>Practice situation</div>', 3);
        foreach (range(1, 4) as $n) {
            $this->makeQuestion($this->topicId, 'moderate', "Loose {$n}");
        }

        $orders = [];
        foreach (range(1, 6) as $round) {
            $sessionId = $this->start($student, 7);
            $html = $this->actingAs($student)->get(route('quiz.take', $sessionId))->assertOk()->getContent();

            $this->assertStringContainsString('Practice situation', $html);
            $this->assertSame(3, substr_count($html, 'Practice situation'), 'one scenario box on each of its three slides');
            preg_match_all('/(Scenario question \d|Loose \d)/', $html, $m);
            $seq = array_values(array_unique($m[1]));
            $caseAt = array_keys(array_filter($seq, fn ($t) => str_starts_with($t, 'Scenario')));
            $this->assertSame(range($caseAt[0], $caseAt[0] + 2), $caseAt, 'its questions are one block: ' . implode(',', $seq));
            $this->assertSame(['Scenario question 1', 'Scenario question 2', 'Scenario question 3'], array_values(array_filter($seq, fn ($t) => str_starts_with($t, 'Scenario'))));
            $orders[] = implode(',', $seq);
        }
        $this->assertGreaterThan(1, count(array_unique($orders)), 'different sessions are shuffled differently');
    }

    public function test_the_results_page_shows_the_scenario_once(): void
    {
        $student = $this->makeStudent();
        $this->makeScenario($this->topicId, '<div>Results situation</div>', 3);
        $sessionId = $this->start($student, 1);
        DB::table('quiz_sessions')->where('id', $sessionId)->update(['completed_at' => now(), 'correct_answers' => 0, 'score_percent' => 0]);

        $html = $this->actingAs($student)->get(route('quiz.results', $sessionId))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Results situation'));
        $this->assertStringContainsString('Questions 1 to 3', $html);
    }
}
