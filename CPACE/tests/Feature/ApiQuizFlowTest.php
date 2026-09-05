<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the mobile API's quiz session lifecycle
 * (Api\QuizApiController::index/history/start/take/cancel/results) - the
 * grading logic itself (submit()) is already covered by
 * QuizApiSubmissionTest; this fills in the surrounding session flow.
 * Hand-built schema, same rationale as the other Feature tests in this
 * suite (migration set doesn't run cleanly from scratch).
 */
class ApiQuizFlowTest extends TestCase
{
    private const TABLES = [
        'performance_records', 'question_choices', 'questions', 'topics', 'subjects',
        'api_tokens', 'quiz_answers', 'quiz_sessions',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users',
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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token', 128)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->string('difficulty')->default('easy');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1)->default('A');
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->string('mode')->default('adaptive');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->integer('duration_secs')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('selected_choice')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_starting_a_quiz_creates_a_session_with_placeholder_answers(): void
    {
        $student = $this->student();
        $subjectId = $this->subjectWithQuestions(3);

        $response = $this->postJson('/api/quizzes/start', [
            'subject_id' => $subjectId,
            'count' => 3,
        ], $this->tokenHeaders($student->id));

        $response->assertStatus(201)->assertJsonStructure(['session_id']);
        $sessionId = $response->json('session_id');
        $this->assertSame(3, DB::table('quiz_answers')->where('session_id', $sessionId)->count());
    }

    public function test_starting_a_timed_quiz_returns_a_time_limit(): void
    {
        $student = $this->student();
        $subjectId = $this->subjectWithQuestions(2);

        $response = $this->postJson('/api/quizzes/start', [
            'subject_id' => $subjectId, 'count' => 2, 'mode' => 'timed',
        ], $this->tokenHeaders($student->id));

        $response->assertStatus(201)->assertJsonPath('time_limit', 120);
    }

    public function test_a_student_cannot_take_another_students_session(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $sessionId = $this->completedSession($other->id);

        $this->getJson("/api/quizzes/{$sessionId}", $this->tokenHeaders($me->id))->assertStatus(404);
    }

    public function test_taking_a_completed_session_reports_it_as_completed(): void
    {
        $student = $this->student();
        $sessionId = $this->completedSession($student->id);

        $response = $this->getJson("/api/quizzes/{$sessionId}", $this->tokenHeaders($student->id));

        $response->assertOk()->assertJson(['completed' => true]);
    }

    public function test_cancelling_an_in_progress_session_deletes_it(): void
    {
        $student = $this->student();
        $subjectId = $this->subjectWithQuestions(2);
        $start = $this->postJson('/api/quizzes/start', ['subject_id' => $subjectId, 'count' => 2], $this->tokenHeaders($student->id));
        $sessionId = $start->json('session_id');

        $this->postJson("/api/quizzes/{$sessionId}/cancel", [], $this->tokenHeaders($student->id))->assertOk();

        $this->assertNull(DB::table('quiz_sessions')->find($sessionId));
    }

    public function test_results_requires_the_session_to_be_completed(): void
    {
        $student = $this->student();
        $subjectId = $this->subjectWithQuestions(1);
        $start = $this->postJson('/api/quizzes/start', ['subject_id' => $subjectId, 'count' => 1], $this->tokenHeaders($student->id));
        $sessionId = $start->json('session_id');

        $this->getJson("/api/quizzes/{$sessionId}/results", $this->tokenHeaders($student->id))
            ->assertStatus(422);
    }

    public function test_history_only_lists_the_current_students_completed_sessions(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $mine = $this->completedSession($me->id);
        $this->completedSession($other->id);

        $response = $this->getJson('/api/quizzes/history', $this->tokenHeaders($me->id));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertSame([$mine], $ids->all());
    }

    public function test_the_index_endpoint_reports_this_students_own_accuracy(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        DB::table('quiz_sessions')->insert([
            'student_id' => $other->id, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 1, 'started_at' => now(), 'completed_at' => now(),
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $me->id, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 9, 'started_at' => now(), 'completed_at' => now(),
        ]);

        $response = $this->getJson('/api/quizzes', $this->tokenHeaders($me->id));

        $response->assertOk()->assertJsonPath('total_attempted', 10);
    }

    private function student(string $email = 'student@example.com'): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id]);

        return $student;
    }

    private function tokenHeaders(int $userId): array
    {
        $token = ApiToken::create([
            'user_id' => $userId,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDay(),
        ]);

        return ['Authorization' => 'Bearer ' . $token->token];
    }

    private function subjectWithQuestions(int $count): int
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        for ($i = 0; $i < $count; $i++) {
            $questionId = DB::table('questions')->insertGetId([
                'topic_id' => $topicId, 'question_text' => "Question $i",
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('question_choices')->insert(['question_id' => $questionId, 'choice_label' => 'A', 'choice_text' => 'Right', 'is_correct' => true]);
            DB::table('question_choices')->insert(['question_id' => $questionId, 'choice_label' => 'B', 'choice_text' => 'Wrong', 'is_correct' => false]);
        }

        return $subjectId;
    }

    private function completedSession(int $studentId): int
    {
        return DB::table('quiz_sessions')->insertGetId([
            'student_id' => $studentId, 'session_type' => 'testing', 'mode' => 'adaptive',
            'total_items' => 1, 'correct_answers' => 1, 'score_percent' => 100,
            'started_at' => now(), 'completed_at' => now(),
        ]);
    }
}
