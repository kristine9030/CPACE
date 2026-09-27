<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\QuizProctorCapture;
use App\Support\QuizProctorRetention;
use Tests\TestCase;

/**
 * Faculty-authored class quizzes: build (draft / publish), share by link,
 * student takes it before the deadline, results are graded and frozen.
 * Hand-built schema, same rationale as the other Feature tests in this suite.
 */
class FacultyQuizTest extends TestCase
{
    private const TABLES = [
        'weakness_reports', 'spaced_repetition_items', 'performance_records',
        'quiz_proctor_captures', 'quiz_proctor_events',
        'faculty_quiz_attempts', 'faculty_quiz_items', 'faculty_quizzes',
        'question_choices', 'questions', 'topics', 'subjects', 'faculty_subjects',
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
        // The student sidebar reads the profile for the alumni/shifted flags.
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('section', 30)->nullable();
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->string('difficulty')->default('moderate');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1);
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });
        Schema::create('faculty_quizzes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('title', 150);
            $table->text('instructions')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('share_token', 40)->unique();
            $table->dateTime('opens_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_results')->default(true);
            $table->boolean('monitor_enabled')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('faculty_quiz_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->unsignedBigInteger('source_question_id')->nullable();
            $table->text('question_text');
            $table->string('question_type', 20)->default('mcq');
            $table->json('choices');
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('faculty_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->unsignedBigInteger('student_id');
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->json('answers')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total_points')->default(0);
            $table->decimal('percent', 5, 2)->nullable();
            $table->unsignedInteger('flag_count')->default(0);
            $table->timestamps();
            $table->unique(['quiz_id', 'student_id']);
        });
        Schema::create('quiz_proctor_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->string('type', 30);
            $table->dateTime('occurred_at');
            $table->string('meta', 255)->nullable();
        });
        Schema::create('quiz_proctor_captures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->string('kind', 10);
            $table->string('path', 255);
            $table->dateTime('captured_at');
            $table->string('reason', 30)->default('interval');
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
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_faculty_can_save_a_quiz_as_a_draft_with_its_questions(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subjectFor($faculty);

        $this->actingAs($faculty)->post(route('faculty.quizzes.store'), [
            'action' => 'draft',
            'title' => 'FAR Quiz 1',
            'subject_id' => $subjectId,
            'due_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'time_limit_minutes' => 20,
            'items_json' => json_encode([$this->mcqItem('What is an asset?', 'B'), $this->tfItem('Cash is a liability.', false)]),
        ])->assertRedirect();

        $quiz = DB::table('faculty_quizzes')->where('faculty_id', $faculty->id)->first();
        $this->assertNotNull($quiz);
        $this->assertSame('draft', $quiz->status);
        $this->assertNotEmpty($quiz->share_token);
        $this->assertSame(20, (int) $quiz->time_limit_minutes);
        $this->assertSame(2, DB::table('faculty_quiz_items')->where('quiz_id', $quiz->id)->count());

        $tf = DB::table('faculty_quiz_items')->where('quiz_id', $quiz->id)->where('question_type', 'true_false')->first();
        $choices = json_decode($tf->choices, true);
        $this->assertSame(['T', 'F'], array_column($choices, 'label'));
        $this->assertTrue($choices[1]['is_correct']);
    }

    public function test_monitoring_is_a_per_quiz_toggle_that_defaults_to_off(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subjectFor($faculty);
        $payload = ['action' => 'draft', 'title' => 'Q', 'subject_id' => $subjectId, 'items_json' => json_encode([$this->mcqItem('One?', 'A')])];

        $this->actingAs($faculty)->post(route('faculty.quizzes.store'), $payload)->assertRedirect();
        $this->assertFalse((bool) DB::table('faculty_quizzes')->latest('id')->value('monitor_enabled'));

        $this->actingAs($faculty)->post(route('faculty.quizzes.store'), $payload + ['monitor_enabled' => 1])->assertRedirect();
        $this->assertTrue((bool) DB::table('faculty_quizzes')->latest('id')->value('monitor_enabled'));
    }

    public function test_a_monitored_quiz_asks_the_student_for_camera_and_screen_before_starting(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $plain = $this->quiz($faculty, 'published', [$this->mcqItem('One?', 'A')]);
        $watched = $this->quiz($faculty, 'published', [$this->mcqItem('One?', 'A')], ['monitor_enabled' => true]);

        $token = fn ($id) => DB::table('faculty_quizzes')->where('id', $id)->value('share_token');

        $this->actingAs($student)->get(route('class-quiz.show', $token($watched)))
            ->assertOk()->assertSee('This quiz is monitored')->assertSee('Screen sharing');
        $this->actingAs($student)->get(route('class-quiz.show', $token($plain)))
            ->assertOk()->assertDontSee('This quiz is monitored');
    }

    public function test_monitor_flags_and_captures_are_recorded_only_for_the_students_own_live_monitored_attempt(): void
    {
        Storage::fake('local');
        [$faculty, $student, $quizId, $attempt] = $this->monitoredSitting();

        $this->actingAs($student)->postJson(route('class-quiz.proctor.event', $attempt), ['type' => 'blur'])
            ->assertOk()->assertJson(['flags' => 1]);
        $this->actingAs($student)->postJson(route('class-quiz.proctor.event', $attempt), ['type' => 'not_a_real_flag'])
            ->assertStatus(422);

        $this->actingAs($student)->post(route('class-quiz.proctor.capture', $attempt), [
            'kind' => 'camera', 'reason' => 'blur', 'frame' => UploadedFile::fake()->image('f.jpg'),
        ])->assertOk();
        $capture = QuizProctorCapture::first();
        Storage::disk('local')->assertExists($capture->path);
        $this->assertStringStartsWith('proctor-quiz/', $capture->path);

        // Another student cannot post onto this attempt.
        $other = $this->student('other@example.com');
        $this->actingAs($other)->postJson(route('class-quiz.proctor.event', $attempt), ['type' => 'blur'])->assertForbidden();

        // A quiz with monitoring off records nothing.
        DB::table('faculty_quizzes')->where('id', $quizId)->update(['monitor_enabled' => false]);
        $this->actingAs($student)->postJson(route('class-quiz.proctor.event', $attempt), ['type' => 'blur'])->assertStatus(409);
    }

    public function test_only_the_owning_faculty_can_view_a_capture_or_the_monitor(): void
    {
        Storage::fake('local');
        [$faculty, $student, $quizId, $attempt] = $this->monitoredSitting();
        $this->actingAs($student)->post(route('class-quiz.proctor.capture', $attempt), [
            'kind' => 'camera', 'reason' => 'start', 'frame' => UploadedFile::fake()->image('f.jpg'),
        ])->assertOk();
        $capture = QuizProctorCapture::first();
        $stranger = $this->faculty('stranger@example.com');

        $this->actingAs($faculty)->get(route('class-quiz.capture', $capture))->assertOk();
        $this->actingAs($stranger)->get(route('class-quiz.capture', $capture))->assertForbidden();
        $this->actingAs($student)->get(route('class-quiz.capture', $capture))->assertForbidden();

        $this->actingAs($faculty)->get(route('faculty.quizzes.monitor', $quizId))->assertOk()->assertSee('Live monitor');
        $feed = $this->actingAs($faculty)->getJson(route('faculty.quizzes.monitor.feed', $quizId))->assertOk();
        $this->assertSame(1, $feed->json('kpis.started'));
        $this->assertNotNull($feed->json('students.0.camera'));
        $this->actingAs($stranger)->get(route('faculty.quizzes.monitor', $quizId))->assertForbidden();

        // No monitor page for a quiz that isn't monitored.
        DB::table('faculty_quizzes')->where('id', $quizId)->update(['monitor_enabled' => false]);
        $this->actingAs($faculty)->get(route('faculty.quizzes.monitor', $quizId))->assertNotFound();
    }

    public function test_flags_feed_the_same_risk_score_as_the_mock_exam_and_show_on_the_results_page(): void
    {
        Storage::fake('local');
        [$faculty, $student, $quizId, $attempt] = $this->monitoredSitting();

        // 3 x screen_lost (5 pts each) = 15 => High risk, same weights as a mock exam.
        foreach (range(1, 3) as $i) {
            $this->actingAs($student)->postJson(route('class-quiz.proctor.event', $attempt), ['type' => 'screen_lost'])->assertOk();
        }
        $attempt->update(['submitted_at' => now(), 'score' => 1, 'total_points' => 1, 'percent' => 100]);

        $feed = $this->actingAs($faculty)->getJson(route('faculty.quizzes.monitor.feed', $quizId));
        $this->assertSame('high', $feed->json('students.0.risk_level'));

        $this->actingAs($faculty)->get(route('faculty.quizzes.results', $quizId))->assertOk()->assertSee('High risk');
        $this->actingAs($faculty)->get(route('faculty.quizzes.attempt', [$quizId, $attempt->id]))
            ->assertOk()->assertSee('Screen sharing stopped')->assertSee('Flag timeline');
    }

    public function test_a_clean_monitored_sitting_keeps_only_its_opening_photo_once_submitted(): void
    {
        Storage::fake('local');
        [$faculty, $student, $quizId, $attempt] = $this->monitoredSitting();

        foreach ([['camera', 'start'], ['camera', 'interval'], ['screen', 'interval']] as [$kind, $reason]) {
            $this->actingAs($student)->post(route('class-quiz.proctor.capture', $attempt), [
                'kind' => $kind, 'reason' => $reason, 'frame' => UploadedFile::fake()->image('f.jpg'),
            ])->assertOk();
        }
        $this->assertSame(3, QuizProctorCapture::count());

        $attempt->update(['submitted_at' => now()]);
        app(QuizProctorRetention::class)->afterSubmit($attempt);

        $this->assertSame(1, QuizProctorCapture::count());
        $this->assertSame('start', QuizProctorCapture::first()->reason);
    }

    public function test_only_the_owner_can_delete_recordings_and_only_after_submit(): void
    {
        Storage::fake('local');
        [$faculty, $student, $quizId, $attempt] = $this->monitoredSitting();
        $this->actingAs($student)->post(route('class-quiz.proctor.capture', $attempt), [
            'kind' => 'camera', 'reason' => 'blur', 'frame' => UploadedFile::fake()->image('f.jpg'),
        ])->assertOk();
        $id = QuizProctorCapture::value('id');

        $this->actingAs($faculty)->delete(route('class-quiz.captures.destroy', $attempt), ['capture_ids' => [$id]])->assertStatus(409);

        $attempt->update(['submitted_at' => now()]);
        $this->actingAs($this->faculty('stranger@example.com'))->delete(route('class-quiz.captures.destroy', $attempt), ['capture_ids' => [$id]])->assertForbidden();
        $this->assertSame(1, QuizProctorCapture::count());

        $this->actingAs($faculty)->delete(route('class-quiz.captures.destroy', $attempt), ['capture_ids' => [$id]])->assertRedirect();
        $this->assertSame(0, QuizProctorCapture::count());
    }

    public function test_publishing_requires_at_least_one_question(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)->post(route('faculty.quizzes.store'), [
            'action' => 'publish',
            'title' => 'Empty quiz',
            'items_json' => json_encode([]),
        ])->assertSessionHasErrors('items_json');

        $this->assertSame(0, DB::table('faculty_quizzes')->count());
    }

    public function test_a_question_without_exactly_one_correct_answer_is_rejected(): void
    {
        $faculty = $this->faculty();
        $item = $this->mcqItem('Broken', 'A');
        $item['choices'][1]['is_correct'] = true;

        $this->actingAs($faculty)->post(route('faculty.quizzes.store'), [
            'action' => 'draft',
            'title' => 'Bad quiz',
            'items_json' => json_encode([$item]),
        ])->assertSessionHasErrors('items_json');
    }

    public function test_publishing_with_a_deadline_in_the_past_is_rejected(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)->post(route('faculty.quizzes.store'), [
            'action' => 'publish',
            'title' => 'Late quiz',
            'due_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'items_json' => json_encode([$this->mcqItem('Q', 'A')]),
        ])->assertSessionHasErrors('due_at');
    }

    public function test_a_student_can_take_a_published_quiz_through_the_shared_link_and_is_graded(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $quizId = $this->quiz($faculty, 'published', [
            $this->mcqItem('Q1', 'B', 2),
            $this->mcqItem('Q2', 'C', 1),
            $this->tfItem('Q3', true, 1),
        ]);
        $token = DB::table('faculty_quizzes')->where('id', $quizId)->value('share_token');
        $items = DB::table('faculty_quiz_items')->where('quiz_id', $quizId)->orderBy('sort_order')->pluck('id');

        $this->actingAs($student)->get(route('class-quiz.show', $token))->assertOk()->assertSee('Start quiz');
        $this->actingAs($student)->post(route('class-quiz.start', $token))->assertRedirect(route('class-quiz.take', $token));
        $this->actingAs($student)->get(route('class-quiz.take', $token))->assertOk()->assertSee('Q1');

        // Q1 right (2 pts), Q2 wrong, Q3 right (1 pt) => 3 / 4
        $this->actingAs($student)->post(route('class-quiz.submit', $token), [
            'answers' => [$items[0] => 'B', $items[1] => 'A', $items[2] => 'T'],
        ])->assertRedirect(route('class-quiz.result', $token));

        $attempt = DB::table('faculty_quiz_attempts')->where('quiz_id', $quizId)->where('student_id', $student->id)->first();
        $this->assertNotNull($attempt->submitted_at);
        $this->assertSame(3, (int) $attempt->score);
        $this->assertSame(4, (int) $attempt->total_points);
        $this->assertSame(75.0, (float) $attempt->percent);

        $this->actingAs($student)->get(route('class-quiz.result', $token))->assertOk()->assertSee('75%');

        // A second submission cannot overwrite the frozen score.
        $this->actingAs($student)->post(route('class-quiz.submit', $token), [
            'answers' => [$items[0] => 'B', $items[1] => 'C', $items[2] => 'T'],
        ])->assertRedirect(route('class-quiz.result', $token));
        $this->assertSame(3, (int) DB::table('faculty_quiz_attempts')->where('id', $attempt->id)->value('score'));

        // The faculty sees the submission on the results page.
        $this->actingAs($faculty)->get(route('faculty.quizzes.results', $quizId))->assertOk()->assertSee($student->email);

        // ...and can open that student's own result, question by question.
        $this->actingAs($faculty)->get(route('faculty.quizzes.attempt', [$quizId, $attempt->id]))
            ->assertOk()->assertSee('Correct')->assertSee('Wrong')->assertSee('75%');

        // Another faculty member cannot.
        $other = $this->faculty('other-fac@example.com');
        $this->actingAs($other)->get(route('faculty.quizzes.attempt', [$quizId, $attempt->id]))->assertForbidden();
    }

    public function test_submitting_a_class_quiz_updates_the_students_performance_records(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $subjectId = $this->subjectFor($faculty);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Topic']);
        $rightQuestionId = $this->questionInTopic($topicId, 'Right one');
        $wrongQuestionId = $this->questionInTopic($topicId, 'Wrong one');

        $item = fn (int $sourceId, string $correct) => array_merge(
            $this->mcqItem('Q', $correct),
            ['source_question_id' => $sourceId],
        );
        $quizId = $this->quiz($faculty, 'published', [
            $item($rightQuestionId, 'A'),
            $item($wrongQuestionId, 'A'),
        ]);
        $token = DB::table('faculty_quizzes')->where('id', $quizId)->value('share_token');
        $items = DB::table('faculty_quiz_items')->where('quiz_id', $quizId)->orderBy('sort_order')->pluck('id');

        $this->actingAs($student)->post(route('class-quiz.start', $token));
        $this->actingAs($student)->post(route('class-quiz.submit', $token), [
            // Item 1 correct (A), item 2 answered B -> wrong.
            'answers' => [$items[0] => 'A', $items[1] => 'B'],
        ])->assertRedirect(route('class-quiz.result', $token));

        $record = DB::table('performance_records')->where('student_id', $student->id)->where('topic_id', $topicId)->first();
        $this->assertNotNull($record, 'A class quiz submission should roll into performance_records, same as regular quizzes and mock exams.');
        $this->assertSame(2, (int) $record->total_attempts);
        $this->assertSame(1, (int) $record->correct_count);

        $this->assertSame(
            2,
            DB::table('spaced_repetition_items')->where('student_id', $student->id)->count(),
            'Both bank-sourced answers should also feed the spaced repetition scheduler.'
        );
    }

    public function test_students_cannot_see_or_start_a_draft_quiz(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $quizId = $this->quiz($faculty, 'draft', [$this->mcqItem('Q', 'A')]);
        $token = DB::table('faculty_quizzes')->where('id', $quizId)->value('share_token');

        $this->actingAs($student)->get(route('class-quiz.show', $token))->assertNotFound();
        $this->actingAs($student)->post(route('class-quiz.start', $token))->assertRedirect(route('class-quiz.show', $token));
        $this->assertSame(0, DB::table('faculty_quiz_attempts')->count());

        // The owner can still preview their own draft.
        $this->actingAs($faculty)->get(route('class-quiz.show', $token))->assertOk();
    }

    public function test_a_student_cannot_start_a_quiz_after_its_deadline(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $quizId = $this->quiz($faculty, 'published', [$this->mcqItem('Q', 'A')], ['due_at' => now()->subHour()]);
        $token = DB::table('faculty_quizzes')->where('id', $quizId)->value('share_token');

        $this->actingAs($student)->post(route('class-quiz.start', $token))
            ->assertRedirect(route('class-quiz.show', $token))
            ->assertSessionHas('error');
        $this->assertSame(0, DB::table('faculty_quiz_attempts')->count());
    }

    public function test_closing_a_quiz_blocks_new_attempts(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $quizId = $this->quiz($faculty, 'published', [$this->mcqItem('Q', 'A')]);
        $token = DB::table('faculty_quizzes')->where('id', $quizId)->value('share_token');

        $this->actingAs($faculty)->post(route('faculty.quizzes.close', $quizId))->assertRedirect();
        $this->assertSame('closed', DB::table('faculty_quizzes')->where('id', $quizId)->value('status'));

        $this->actingAs($student)->post(route('class-quiz.start', $token))->assertSessionHas('error');
    }

    public function test_faculty_cannot_edit_or_delete_another_facultys_quiz(): void
    {
        $owner = $this->faculty('owner@example.com');
        $other = $this->faculty('other@example.com');
        $quizId = $this->quiz($owner, 'draft', [$this->mcqItem('Q', 'A')]);

        $this->actingAs($other)->get(route('faculty.quizzes.edit', $quizId))->assertForbidden();
        $this->actingAs($other)->delete(route('faculty.quizzes.destroy', $quizId))->assertForbidden();
        $this->assertSame(1, DB::table('faculty_quizzes')->count());
    }

    public function test_questions_are_frozen_once_a_student_has_answered(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $quizId = $this->quiz($faculty, 'published', [$this->mcqItem('Original', 'A')]);
        $token = DB::table('faculty_quizzes')->where('id', $quizId)->value('share_token');

        $this->actingAs($student)->post(route('class-quiz.start', $token));

        $this->actingAs($faculty)->put(route('faculty.quizzes.update', $quizId), [
            'action' => 'publish',
            'title' => 'Renamed quiz',
            'items_json' => json_encode([$this->mcqItem('Replacement', 'B'), $this->mcqItem('Extra', 'C')]),
        ])->assertRedirect();

        $this->assertSame('Renamed quiz', DB::table('faculty_quizzes')->where('id', $quizId)->value('title'));
        $items = DB::table('faculty_quiz_items')->where('quiz_id', $quizId)->get();
        $this->assertCount(1, $items);
        $this->assertSame('Original', $items->first()->question_text);
    }

    public function test_bank_picker_only_returns_active_questions_from_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $mine = $this->subjectFor($faculty, 'FAR');
        $notMine = $this->subjectFor(null, 'AUD');

        $this->bankQuestion($mine, 'Mine and active');
        $this->bankQuestion($mine, 'Mine but inactive', false);
        $this->bankQuestion($notMine, 'Not mine');

        $response = $this->actingAs($faculty)->getJson(route('faculty.quizzes.bank-questions'))->assertOk();
        $texts = collect($response->json())->pluck('question_text')->all();

        $this->assertSame(['Mine and active'], $texts);
        $this->assertCount(4, $response->json()[0]['choices']);
    }

    public function test_student_class_quiz_list_shows_published_but_not_draft_quizzes(): void
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $this->quiz($faculty, 'published', [$this->mcqItem('Q', 'A')], ['title' => 'Visible quiz']);
        $this->quiz($faculty, 'draft', [$this->mcqItem('Q', 'A')], ['title' => 'Hidden draft']);

        // Quiz titles are listed on the per-subject page (Classroom-style);
        // the index page only shows a summary card per subject/class.
        $this->actingAs($student)->get(route('class-quizzes.subject', 'general'))
            ->assertOk()
            ->assertSee('Visible quiz')
            ->assertDontSee('Hidden draft');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function faculty(string $email = 'faculty@example.com'): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function student(string $email = 'student@example.com'): User
    {
        return User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function subjectFor(?User $faculty, string $code = 'FAR'): int
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => $code, 'name' => $code . ' subject', 'is_active' => true]);
        if ($faculty) {
            DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        }

        return $subjectId;
    }

    private function bankQuestion(int $subjectId, string $text, bool $active = true): int
    {
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Topic']);
        $questionId = DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'question_text' => $text, 'question_type' => 'mcq',
            'difficulty' => 'easy', 'is_active' => $active, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (['A', 'B', 'C', 'D'] as $label) {
            DB::table('question_choices')->insert([
                'question_id' => $questionId, 'choice_label' => $label, 'choice_text' => "Choice {$label}", 'is_correct' => $label === 'A',
            ]);
        }

        return $questionId;
    }

    private function questionInTopic(int $topicId, string $text): int
    {
        return DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'question_text' => $text, 'question_type' => 'mcq',
            'difficulty' => 'easy', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function mcqItem(string $text, string $correct, int $points = 1): array
    {
        return [
            'question_text' => $text, 'question_type' => 'mcq', 'points' => $points,
            'choices' => array_map(fn ($l) => ['label' => $l, 'text' => "Option {$l}", 'is_correct' => $l === $correct], ['A', 'B', 'C', 'D']),
        ];
    }

    private function tfItem(string $text, bool $answer, int $points = 1): array
    {
        return [
            'question_text' => $text, 'question_type' => 'true_false', 'points' => $points,
            'choices' => [['label' => 'T', 'text' => 'True', 'is_correct' => $answer], ['label' => 'F', 'text' => 'False', 'is_correct' => ! $answer]],
        ];
    }

    /** @return array{0: User, 1: User, 2: int, 3: \App\Models\FacultyQuizAttempt} */
    private function monitoredSitting(): array
    {
        $faculty = $this->faculty();
        $student = $this->student();
        $quizId = $this->quiz($faculty, 'published', [$this->mcqItem('One?', 'A')], ['monitor_enabled' => true]);
        $attempt = \App\Models\FacultyQuizAttempt::create([
            'quiz_id' => $quizId, 'student_id' => $student->id, 'started_at' => now(),
        ]);

        return [$faculty, $student, $quizId, $attempt];
    }

    /** Insert a quiz directly (bypassing the form) in the given status. */
    private function quiz(User $faculty, string $status, array $items, array $overrides = []): int
    {
        $quizId = DB::table('faculty_quizzes')->insertGetId(array_merge([
            'faculty_id' => $faculty->id, 'title' => 'Quiz', 'status' => $status,
            'share_token' => bin2hex(random_bytes(12)), 'show_results' => true,
            'published_at' => $status === 'published' ? now() : null,
            'created_at' => now(), 'updated_at' => now(),
        ], $overrides));

        foreach ($items as $order => $item) {
            DB::table('faculty_quiz_items')->insert([
                'quiz_id' => $quizId, 'question_text' => $item['question_text'], 'question_type' => $item['question_type'],
                'choices' => json_encode($item['choices']), 'points' => $item['points'], 'sort_order' => $order,
                'source_question_id' => $item['source_question_id'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $quizId;
    }
}
