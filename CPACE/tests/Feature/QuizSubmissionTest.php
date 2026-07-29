<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuizSubmissionTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order.
     *
     * Hand-built schema, same rationale as ChairAnalyticsTest /
     * SpacedRepetitionCalendarTest: the migration set cannot run cleanly from
     * scratch, so RefreshDatabase is not used.
     */
    private const TABLES = [
        'points_log', 'weakness_reports', 'spaced_repetition_items', 'performance_records',
        'quiz_answers', 'quiz_sessions', 'question_choices', 'questions', 'topics', 'subjects',
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
            $table->boolean('email_verified')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        // Creating a student fires User::booted(), which drops them into the
        // default community group - so the chat tables have to exist here too.
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
            $table->unsignedBigInteger('created_by')->nullable();
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
            $table->string('session_type')->default('adaptive');
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

    public function test_submitting_a_quiz_grades_server_side_against_the_stored_answer_key(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $topicId, $questions);

        // Answer question 1 correctly, question 2 with the wrong choice id -
        // and try to smuggle a fake "is_correct" flag that the server must ignore.
        $answers = [
            $questions[0]['id'] => $questions[0]['correct_choice_id'],
            $questions[1]['id'] => $questions[1]['wrong_choice_id'],
        ];

        $this->actingAs($student)
            ->post(route('quiz.submit', $session), ['answers' => $answers])
            ->assertRedirect(route('quiz.results', $session));

        $updated = DB::table('quiz_sessions')->find($session);
        $this->assertNotNull($updated->completed_at);
        $this->assertSame(1, $updated->correct_answers);
        $this->assertEquals(50.00, $updated->score_percent);

        $graded = DB::table('quiz_answers')->where('session_id', $session)->get()->keyBy('question_id');
        $this->assertTrue((bool) $graded[$questions[0]['id']]->is_correct);
        $this->assertFalse((bool) $graded[$questions[1]['id']]->is_correct);
    }

    public function test_an_omitted_answer_is_graded_as_incorrect_not_skipped(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $topicId, $questions);

        // Only answer the first question - the second is left out entirely.
        $answers = [$questions[0]['id'] => $questions[0]['correct_choice_id']];

        $this->actingAs($student)->post(route('quiz.submit', $session), ['answers' => $answers]);

        $second = DB::table('quiz_answers')->where('session_id', $session)->where('question_id', $questions[1]['id'])->first();
        $this->assertNull($second->selected_choice);
        $this->assertFalse((bool) $second->is_correct);
    }

    public function test_submitting_updates_performance_records_and_extends_the_wrong_streak(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $topicId, $questions);

        // Both answers wrong.
        $answers = [
            $questions[0]['id'] => $questions[0]['wrong_choice_id'],
            $questions[1]['id'] => $questions[1]['wrong_choice_id'],
        ];

        $this->actingAs($student)->post(route('quiz.submit', $session), ['answers' => $answers]);

        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertSame(2, $record->total_attempts);
        $this->assertSame(0, $record->correct_count);
        $this->assertSame(2, $record->consecutive_wrong);
        // 2 wrong in a row is below both weak thresholds (needs 3-in-a-row or 5 attempts).
        $this->assertFalse((bool) $record->is_weak_area);
    }

    public function test_accuracy_rate_is_persisted_on_insert_and_kept_in_sync_on_update(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();

        // First sitting: 1/2 correct -> 50%.
        $session1 = $this->beginQuizSession($student->id, $topicId, $questions);
        $this->actingAs($student)->post(route('quiz.submit', $session1), [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['wrong_choice_id'],
            ],
        ]);
        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertEquals(50.00, $record->accuracy_rate);

        // Second sitting: both correct -> cumulative 3/4 = 75%.
        $session2 = $this->beginQuizSession($student->id, $topicId, $questions);
        $this->actingAs($student)->post(route('quiz.submit', $session2), [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['correct_choice_id'],
            ],
        ]);
        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertEquals(75.00, $record->accuracy_rate);
    }

    public function test_three_consecutive_wrong_sessions_flag_the_topic_as_weak_and_open_a_report(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();

        // Three separate sittings, each entirely wrong on one question, so the
        // consecutive_wrong streak crosses the WeaknessDetector::CONSECUTIVE_WRONG
        // threshold of 3 without needing 5 total attempts.
        for ($i = 0; $i < 3; $i++) {
            $session = $this->beginQuizSession($student->id, $topicId, [$questions[0]]);
            $this->actingAs($student)->post(route('quiz.submit', $session), [
                'answers' => [$questions[0]['id'] => $questions[0]['wrong_choice_id']],
            ]);
        }

        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertSame(3, $record->consecutive_wrong);
        $this->assertTrue((bool) $record->is_weak_area);

        $report = DB::table('weakness_reports')->where('student_id', $student->id)->where('topic_id', $topicId)->whereNull('resolved_at')->first();
        $this->assertNotNull($report, 'a clean-session wrong streak of 3 must open a weakness report');
    }

    public function test_submitting_schedules_the_next_sm2_review_for_each_answered_question(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $topicId, $questions);

        $this->assertSame(0, DB::table('spaced_repetition_items')->where('student_id', $student->id)->count());

        $this->actingAs($student)->post(route('quiz.submit', $session), [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['wrong_choice_id'],
            ],
        ]);

        $items = DB::table('spaced_repetition_items')->where('student_id', $student->id)->get()->keyBy('question_id');
        $this->assertCount(2, $items);

        // First review ever: correct -> repetition 1, interval 1 day.
        $this->assertSame(1, $items[$questions[0]['id']]->repetition_num);
        $this->assertSame(1, $items[$questions[0]['id']]->interval_days);

        // Wrong answer: lapse, repetition resets to 0, still due tomorrow.
        $this->assertSame(0, $items[$questions[1]['id']]->repetition_num);
        $this->assertSame(1, $items[$questions[1]['id']]->interval_days);
    }

    public function test_training_mode_grades_the_quiz_but_skips_all_progress_tracking(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $topicId, $questions, sessionType: 'training');

        $this->actingAs($student)->post(route('quiz.submit', $session), [
            'answers' => [
                $questions[0]['id'] => $questions[0]['correct_choice_id'],
                $questions[1]['id'] => $questions[1]['correct_choice_id'],
            ],
        ]);

        // The session itself is still graded and saved...
        $updated = DB::table('quiz_sessions')->find($session);
        $this->assertNotNull($updated->completed_at);
        $this->assertSame(2, $updated->correct_answers);

        // ...but none of it counts toward analytics, points, or the review schedule.
        $this->assertSame(0, DB::table('performance_records')->where('student_id', $student->id)->count());
        $this->assertSame(0, DB::table('spaced_repetition_items')->where('student_id', $student->id)->count());
        $this->assertSame(0, DB::table('points_log')->where('student_id', $student->id)->count());
    }

    public function test_a_completed_quiz_cannot_be_resubmitted_to_double_count_progress(): void
    {
        $student = $this->student();
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($student->id, $topicId, $questions);

        $answers = [
            $questions[0]['id'] => $questions[0]['correct_choice_id'],
            $questions[1]['id'] => $questions[1]['correct_choice_id'],
        ];

        $this->actingAs($student)->post(route('quiz.submit', $session), ['answers' => $answers]);
        $this->actingAs($student)->post(route('quiz.submit', $session), ['answers' => $answers])
            ->assertRedirect(route('quiz.results', $session));

        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertSame(2, $record->total_attempts, 'resubmitting an already-completed quiz must not tally its answers a second time');
    }

    public function test_a_student_cannot_submit_another_students_quiz_session(): void
    {
        $owner = $this->student('owner@example.com');
        $intruder = $this->student('intruder@example.com');
        [$topicId, $questions] = $this->seedTwoQuestionTopic();
        $session = $this->beginQuizSession($owner->id, $topicId, $questions);

        $this->actingAs($intruder)
            ->post(route('quiz.submit', $session), ['answers' => [$questions[0]['id'] => $questions[0]['correct_choice_id']]])
            ->assertNotFound();

        $this->assertNull(DB::table('quiz_sessions')->find($session)->completed_at);
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

    /**
     * Two "easy" questions under one topic, each with a correct and a wrong choice.
     * Returns [topicId, [['id'=>, 'correct_choice_id'=>, 'wrong_choice_id'=>], ...]].
     */
    private function seedTwoQuestionTopic(): array
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        $questions = [];
        for ($i = 0; $i < 2; $i++) {
            $questionId = DB::table('questions')->insertGetId([
                'topic_id' => $topicId,
                'question_text' => "Question $i",
                'difficulty' => 'easy',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $correctId = DB::table('question_choices')->insertGetId(['question_id' => $questionId, 'choice_text' => 'Right', 'is_correct' => true, 'created_at' => now(), 'updated_at' => now()]);
            $wrongId = DB::table('question_choices')->insertGetId(['question_id' => $questionId, 'choice_text' => 'Wrong', 'is_correct' => false, 'created_at' => now(), 'updated_at' => now()]);

            $questions[] = ['id' => $questionId, 'correct_choice_id' => $correctId, 'wrong_choice_id' => $wrongId];
        }

        return [$topicId, $questions];
    }

    /**
     * Create a quiz session with placeholder answer rows already served, exactly
     * as QuizController::start() does, so submit() has something to grade.
     */
    private function beginQuizSession(int $studentId, int $topicId, array $questions, string $sessionType = 'testing'): int
    {
        $sessionId = DB::table('quiz_sessions')->insertGetId([
            'student_id' => $studentId,
            'topic_id' => $topicId,
            'session_type' => $sessionType,
            'mode' => 'adaptive',
            'total_items' => count($questions),
            'started_at' => now(),
        ]);

        foreach ($questions as $q) {
            DB::table('quiz_answers')->insert([
                'session_id' => $sessionId,
                'question_id' => $q['id'],
                'answered_at' => now(),
            ]);
        }

        return $sessionId;
    }
}
