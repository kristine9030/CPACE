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
 * Regression coverage for the mobile/API quiz-submission path
 * (App\Http\Controllers\Api\QuizApiController::submit). Its
 * updatePerformanceRecords() used to do `(object) [...] + [...]` when a
 * performance_records row already existed - adding an array to a stdClass is
 * a PHP TypeError, so the API endpoint crashed on every second attempt at a
 * topic. This suite locks in the fix and the accuracy_rate persistence that
 * was fixed alongside it (see QuizSubmissionTest for the equivalent web
 * coverage - this file intentionally does not repeat every case there).
 */
class QuizApiSubmissionTest extends TestCase
{
    private const TABLES = [
        'points_log', 'weakness_reports', 'spaced_repetition_items', 'performance_records',
        'quiz_answers', 'quiz_sessions', 'question_choices', 'questions', 'topics', 'subjects',
        'student_profiles', 'api_tokens', 'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
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
            $table->boolean('email_verified')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
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
            $table->integer('total_points')->default(0);
            $table->integer('streak_days')->default(0);
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
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
            $table->string('difficulty')->default('easy');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
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
            $table->decimal('accuracy_rate', 5, 2)->default(0);
            $table->integer('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
            $table->timestamp('last_attempted')->nullable();
        });
        Schema::create('spaced_repetition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('question_id');
            $table->integer('repetition_num')->default(0);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->integer('interval_days')->default(0);
            $table->integer('quality_score')->nullable();
            $table->date('last_reviewed')->nullable();
            $table->date('next_review_at')->nullable();
        });
        Schema::create('weakness_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->timestamp('flagged_at')->nullable();
            $table->string('trigger_reason')->nullable();
            $table->decimal('accuracy_at_flag', 5, 2)->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
        Schema::create('points_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->integer('points');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_a_second_submission_on_the_same_topic_updates_the_existing_record_instead_of_crashing(): void
    {
        $student = $this->student();
        $headers = $this->tokenHeaders($student->id);
        [$topicId, $questions] = $this->seedTwoQuestionTopic();

        // First sitting establishes the performance_records row.
        $session1 = $this->beginQuizSession($student->id, $questions);
        $this->postJson("/api/quizzes/{$session1}/submit", [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['wrong_choice_id'],
            ],
        ], $headers)->assertOk();

        // Second sitting hits the update branch, which used to throw a
        // TypeError ("Unsupported operand types: stdClass + array").
        $session2 = $this->beginQuizSession($student->id, $questions);
        $response = $this->postJson("/api/quizzes/{$session2}/submit", [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['correct_choice_id'],
            ],
        ], $headers);

        $response->assertOk();
        $response->assertJson(['session_id' => $session2]);

        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertSame(4, $record->total_attempts);
        $this->assertSame(3, $record->correct_count);
        $this->assertEquals(75.00, $record->accuracy_rate);
    }

    public function test_a_first_time_submission_persists_accuracy_rate(): void
    {
        $student = $this->student();
        $headers = $this->tokenHeaders($student->id);
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $questions);

        $this->postJson("/api/quizzes/{$session}/submit", [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['wrong_choice_id'],
            ],
        ], $headers)->assertOk();

        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertEquals(50.00, $record->accuracy_rate);
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

    private function seedTwoQuestionTopic(): array
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        $questions = [];
        for ($i = 0; $i < 2; $i++) {
            $questionId = DB::table('questions')->insertGetId([
                'topic_id' => $topicId, 'question_text' => "Question {$i}", 'difficulty' => 'easy',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $correctId = DB::table('question_choices')->insertGetId(['question_id' => $questionId, 'choice_text' => 'Right', 'is_correct' => true]);
            $wrongId = DB::table('question_choices')->insertGetId(['question_id' => $questionId, 'choice_text' => 'Wrong', 'is_correct' => false]);

            $questions[] = ['id' => $questionId, 'correct_choice_id' => $correctId, 'wrong_choice_id' => $wrongId];
        }

        return [$topicId, $questions];
    }

    private function beginQuizSession(int $studentId, array $questions): int
    {
        $sessionId = DB::table('quiz_sessions')->insertGetId([
            'student_id' => $studentId, 'session_type' => 'testing', 'mode' => 'adaptive',
            'total_items' => count($questions), 'started_at' => now(),
        ]);

        foreach ($questions as $q) {
            DB::table('quiz_answers')->insert(['session_id' => $sessionId, 'question_id' => $q['id'], 'answered_at' => now()]);
        }

        return $sessionId;
    }
}
