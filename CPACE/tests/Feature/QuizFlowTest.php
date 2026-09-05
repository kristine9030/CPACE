<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers QuizController::start/take/cancel/history - the session lifecycle
 * around the grading logic already covered by QuizSubmissionTest.
 *
 * Hand-built schema, same rationale as the other Feature tests in this
 * suite (migration set doesn't run cleanly from scratch).
 */
class QuizFlowTest extends TestCase
{
    private const TABLES = [
        'performance_records', 'quiz_answers', 'quiz_sessions', 'question_choices', 'questions',
        'topics', 'subjects', 'notifications', 'messages', 'conversation_participants',
        'conversations', 'student_profiles', 'users',
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->integer('total_points')->default(0);
            $table->integer('streak_days')->default(0);
            $table->timestamps();
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
            $table->string('question_type')->default('multiple_choice');
            $table->string('difficulty')->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
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
            $table->integer('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
            $table->timestamp('last_attempted')->nullable();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_starting_a_quiz_creates_a_session_with_placeholder_answers_and_redirects_to_take(): void
    {
        $student = $this->student();
        $subjectId = $this->subjectWithQuestions(3);

        $response = $this->actingAs($student)->post(route('quiz.start'), [
            'subject_id' => $subjectId,
            'mode' => 'timed',
            'count' => 3,
            'session_type' => 'testing',
        ]);

        $session = DB::table('quiz_sessions')->where('student_id', $student->id)->first();
        $this->assertNotNull($session);
        $response->assertRedirect(route('quiz.take', $session->id));
        $this->assertSame(3, DB::table('quiz_answers')->where('session_id', $session->id)->count());
    }

    public function test_starting_a_quiz_without_choosing_a_subject_shows_an_error(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('quiz.start'), [
            'count' => 5,
        ])->assertSessionHas('error');

        $this->assertSame(0, DB::table('quiz_sessions')->count());
    }

    public function test_starting_a_quiz_for_a_subject_with_no_questions_shows_an_error(): void
    {
        $student = $this->student();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'EMPTY', 'name' => 'Empty Subject', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('topics')->insert(['subject_id' => $subjectId, 'name' => 'Empty Topic', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->post(route('quiz.start'), [
            'subject_id' => $subjectId,
            'count' => 5,
        ])->assertSessionHas('error');

        $this->assertSame(0, DB::table('quiz_sessions')->count());
    }

    public function test_taking_an_already_completed_quiz_redirects_to_its_results(): void
    {
        $student = $this->student();
        $sessionId = $this->completedSession($student->id);

        $this->actingAs($student)->get(route('quiz.take', $sessionId))
            ->assertRedirect(route('quiz.results', $sessionId));
    }

    public function test_a_student_cannot_open_another_students_quiz_session(): void
    {
        $student = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $sessionId = $this->completedSession($other->id);

        $this->actingAs($student)->get(route('quiz.take', $sessionId))->assertNotFound();
    }

    public function test_cancelling_an_in_progress_quiz_deletes_it(): void
    {
        $student = $this->student();
        $subjectId = $this->subjectWithQuestions(2);
        $this->actingAs($student)->post(route('quiz.start'), ['subject_id' => $subjectId, 'count' => 2]);
        $sessionId = DB::table('quiz_sessions')->where('student_id', $student->id)->value('id');

        $this->actingAs($student)->post(route('quiz.cancel', $sessionId))->assertRedirect(route('adaptive-quizzes'));

        $this->assertNull(DB::table('quiz_sessions')->find($sessionId));
        $this->assertSame(0, DB::table('quiz_answers')->where('session_id', $sessionId)->count());
    }

    public function test_cancelling_an_already_completed_quiz_leaves_it_untouched(): void
    {
        $student = $this->student();
        $sessionId = $this->completedSession($student->id);

        $this->actingAs($student)->post(route('quiz.cancel', $sessionId));

        $this->assertNotNull(DB::table('quiz_sessions')->find($sessionId), 'a completed quiz must not be deleted by cancel');
    }

    public function test_quiz_history_only_lists_the_current_students_completed_sessions(): void
    {
        $student = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $mine = $this->completedSession($student->id);
        $this->completedSession($other->id);
        // An in-progress session of my own should not show up in history either.
        $subjectId = $this->subjectWithQuestions(1);
        $this->actingAs($student)->post(route('quiz.start'), ['subject_id' => $subjectId, 'count' => 1]);

        $response = $this->actingAs($student)->get(route('quiz.history'));

        $response->assertOk();
        $sessions = $response->viewData('sessions');
        $this->assertSame([$mine], $sessions->pluck('id')->all());
    }

    private function student(string $email = 'student@example.com'): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'created_at' => now(), 'updated_at' => now()]);

        return $student;
    }

    private function subjectWithQuestions(int $count): int
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        for ($i = 0; $i < $count; $i++) {
            $questionId = DB::table('questions')->insertGetId([
                'topic_id' => $topicId,
                'question_text' => "Question $i",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('question_choices')->insert(['question_id' => $questionId, 'choice_text' => 'Right', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('question_choices')->insert(['question_id' => $questionId, 'choice_text' => 'Wrong', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()]);
        }

        return $subjectId;
    }

    private function completedSession(int $studentId): int
    {
        return DB::table('quiz_sessions')->insertGetId([
            'student_id' => $studentId,
            'session_type' => 'testing',
            'mode' => 'adaptive',
            'total_items' => 1,
            'correct_answers' => 1,
            'score_percent' => 100,
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
