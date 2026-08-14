<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Quiz-from-note: a student turns one of their own review notes into a short
 * practice quiz. Hand-built schema, same convention as the other Feature tests
 * in this suite.
 */
class NoteQuizTest extends TestCase
{
    private const TABLES = [
        'review_notes', 'topics', 'subjects',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('role_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('setup_completed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('group');
            $table->string('name')->nullable();
            $table->boolean('is_default_group')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
        });

        Schema::create('review_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('title', 180);
            $table->longText('content')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    private function student(string $email = 'student@example.com'): User
    {
        return User::create([
            'role_id'    => Role::STUDENT,
            'first_name' => 'Stu',
            'last_name'  => 'Dent',
            'email'      => $email,
            'password'   => bcrypt('secret'),
            'setup_completed_at' => now(),
        ]);
    }

    private function note(User $owner, ?string $content = null): int
    {
        return \DB::table('review_notes')->insertGetId([
            'student_id' => $owner->id,
            'title'      => 'Partnership dissolution',
            'content'    => $content ?? str_repeat('A partnership is dissolved when there is a change in the relation of the partners. ', 8),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Fakes the same reply from BOTH providers, so the test doesn't care
     * whether the service reaches Gemini or falls through to OpenRouter —
     * that depends on whether GEMINI_API_KEY is set in the environment (it
     * isn't in CI, which runs off .env.example, but it is in local dev).
     */
    private function fakeAiReply(string $text): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => $text]]]]],
            ], 200),
            'https://openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $text]]],
            ], 200),
        ]);
    }

    /**
     * A well-formed reply. Pass $badChoiceCountOn to make that question come
     * back with only 3 choices, so the drop-bad-questions path gets exercised.
     */
    private function quizJson(int $count = 5, ?int $badChoiceCountOn = null): string
    {
        $questions = [];

        for ($i = 1; $i <= $count; $i++) {
            $choices = [];
            $n = ($badChoiceCountOn === $i) ? 3 : 4;

            for ($c = 0; $c < $n; $c++) {
                $choices[] = ['text' => "Choice {$c} for Q{$i}", 'is_correct' => $c === 0];
            }

            $questions[] = [
                'question_text' => "What happens in scenario {$i}?",
                'choices'       => $choices,
                'explanation'   => "Because of rule {$i}.",
            ];
        }

        return json_encode(['questions' => $questions]);
    }

    public function test_a_student_can_generate_a_quiz_from_their_own_note(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->fakeAiReply($this->quizJson(5));

        $response = $this->actingAs($student)->postJson("/review-notes/{$noteId}/quiz", ['count' => 5]);

        $response->assertOk()
            ->assertJsonPath('note_id', $noteId)
            ->assertJsonCount(5, 'questions')
            ->assertJsonPath('questions.0.question_text', 'What happens in scenario 1?')
            ->assertJsonCount(4, 'questions.0.choices')
            ->assertJsonPath('questions.0.choices.0.is_correct', true)
            ->assertJsonPath('questions.0.explanation', 'Because of rule 1.');
    }

    public function test_a_student_cannot_quiz_another_students_note(): void
    {
        $owner = $this->student('owner@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($owner);

        $this->fakeAiReply($this->quizJson());

        $this->actingAs($other)
            ->postJson("/review-notes/{$noteId}/quiz")
            ->assertForbidden();
    }

    public function test_a_note_too_short_to_quiz_is_rejected_before_calling_the_ai(): void
    {
        $student = $this->student();
        $noteId = $this->note($student, 'Too short.');

        Http::fake();

        $this->actingAs($student)
            ->postJson("/review-notes/{$noteId}/quiz")
            ->assertStatus(422)
            ->assertJsonPath('message', 'This note is too short to build a quiz from. Add more detail to it first — around a paragraph or two.');

        Http::assertNothingSent();
    }

    public function test_malformed_questions_are_dropped_rather_than_failing_the_whole_quiz(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        // Question 2 comes back with only 3 choices — it should be skipped,
        // leaving the other four.
        $this->fakeAiReply($this->quizJson(5, badChoiceCountOn: 2));

        $response = $this->actingAs($student)->postJson("/review-notes/{$noteId}/quiz");

        $response->assertOk()->assertJsonCount(4, 'questions');

        foreach ($response->json('questions') as $q) {
            $this->assertCount(4, $q['choices']);
            $this->assertSame(1, collect($q['choices'])->where('is_correct', true)->count());
        }
    }

    public function test_an_ai_failure_returns_503_rather_than_a_server_error(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response('nope', 500),
            'https://openrouter.ai/*' => Http::response('nope', 500),
        ]);

        $this->actingAs($student)
            ->postJson("/review-notes/{$noteId}/quiz")
            ->assertStatus(503)
            ->assertJsonPath('message', 'The AI could not build a quiz right now. Please try again in a moment.');
    }

    public function test_a_reply_with_too_few_usable_questions_is_treated_as_a_failure(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->fakeAiReply(json_encode(['questions' => [
            ['question_text' => 'Only one, and it is broken', 'choices' => [], 'explanation' => ''],
        ]]));

        $this->actingAs($student)
            ->postJson("/review-notes/{$noteId}/quiz")
            ->assertStatus(503);
    }
}
