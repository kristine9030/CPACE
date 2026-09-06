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
 * Faculty-authored class quizzes: build (draft / publish), share by link,
 * student takes it before the deadline, results are graded and frozen.
 * Hand-built schema, same rationale as the other Feature tests in this suite.
 */
class FacultyQuizTest extends TestCase
{
    private const TABLES = [
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
            $table->timestamps();
            $table->unique(['quiz_id', 'student_id']);
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

        $this->actingAs($student)->get(route('class-quizzes'))
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
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $quizId;
    }
}
