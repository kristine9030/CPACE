<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\SpacedRepetitionScheduler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SpacedRepetitionCalendarTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order.
     *
     * The schema is built by hand rather than with RefreshDatabase because the
     * migration set cannot run from scratch — see ChairAnalyticsTest for the
     * same rationale. spaced_repetition_items and weakness_reports have no
     * migration file at all; their columns here are taken directly from how
     * SpacedRepetitionScheduler and WeaknessDetector read/write them.
     */
    private const TABLES = [
        'calendar_plans', 'weakness_reports', 'spaced_repetition_items', 'quiz_sessions',
        'performance_records', 'questions', 'topics', 'subjects', 'notifications',
        'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users',
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
        // default community group — so the chat tables have to exist here too.
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
            $table->integer('streak_days')->default(0);
            $table->date('exam_target_date')->nullable();
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
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('difficulty');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
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
        Schema::create('calendar_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->unsignedSmallInteger('topic_id')->nullable();
            $table->string('title', 120)->nullable();
            $table->date('plan_date');
            $table->unsignedSmallInteger('start_min');
            $table->unsignedSmallInteger('duration_min')->default(60);
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

    public function test_scheduler_advances_intervals_per_sm2_rule_and_lapses_on_a_miss(): void
    {
        $scheduler = app(SpacedRepetitionScheduler::class);
        $day1 = Carbon::parse('2026-01-01');

        // 1st correct review: I(1) = 1 day.
        $state = $scheduler->next(
            ['repetition_num' => 0, 'ease_factor' => SpacedRepetitionScheduler::EF_DEFAULT, 'interval_days' => 0],
            $scheduler->qualityFromAnswer(true, 'easy'),
            $day1
        );
        $this->assertSame(1, $state['interval_days']);
        $this->assertSame(2.6, $state['ease_factor']);
        $this->assertSame('2026-01-02', $state['next_review_at']);

        // 2nd correct review: I(2) = 6 days, regardless of ease factor.
        $state = $scheduler->next($state, $scheduler->qualityFromAnswer(true, 'easy'), Carbon::parse('2026-01-02'));
        $this->assertSame(6, $state['interval_days']);
        $this->assertSame('2026-01-08', $state['next_review_at']);

        // 3rd correct review: I(3) = round(I(2) x EF).
        $state = $scheduler->next($state, $scheduler->qualityFromAnswer(true, 'easy'), Carbon::parse('2026-01-08'));
        $this->assertSame(17, $state['interval_days']); // round(6 x 2.8)
        $this->assertSame('2026-01-25', $state['next_review_at']);

        // A blanked answer is a lapse: the chain resets and it resurfaces tomorrow.
        $state = $scheduler->next($state, $scheduler->qualityFromAnswer(false, 'easy'), Carbon::parse('2026-01-25'));
        $this->assertSame(0, $state['repetition_num']);
        $this->assertSame(1, $state['interval_days']);
        $this->assertSame('2026-01-26', $state['next_review_at']);
    }

    public function test_scheduler_never_drops_the_ease_factor_below_its_floor(): void
    {
        $scheduler = app(SpacedRepetitionScheduler::class);
        $state = ['repetition_num' => 0, 'ease_factor' => SpacedRepetitionScheduler::EF_MIN, 'interval_days' => 1];

        // Repeated blanks on an already-floored item must not push EF below 1.30.
        for ($i = 0; $i < 5; $i++) {
            $state = $scheduler->next($state, $scheduler->qualityFromAnswer(false, 'difficult'), Carbon::today());
        }

        $this->assertSame(SpacedRepetitionScheduler::EF_MIN, $state['ease_factor']);
    }

    public function test_calendar_seeds_a_schedule_from_existing_quiz_history_on_first_visit(): void
    {
        $student = $this->student();

        $subjectId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Internal Controls', 'created_at' => now(), 'updated_at' => now()]);

        $firstQuestionId = DB::table('questions')->insertGetId(['topic_id' => $topicId, 'difficulty' => 'easy', 'created_at' => now(), 'updated_at' => now()]);
        $secondQuestionId = DB::table('questions')->insertGetId(['topic_id' => $topicId, 'difficulty' => 'easy', 'created_at' => now(), 'updated_at' => now()]);

        // 50% accuracy over 2 questions: the scheduler is expected to treat the
        // first (by id) as remembered and the second as missed.
        DB::table('performance_records')->insert([
            'student_id' => $student->id,
            'topic_id' => $topicId,
            'correct_count' => 5,
            'total_attempts' => 10,
            'consecutive_wrong' => 0,
            'last_attempted' => '2026-01-10 00:00:00',
        ]);

        $this->assertSame(0, DB::table('spaced_repetition_items')->where('student_id', $student->id)->count());

        $this->actingAs($student)->get(route('calendar'))->assertOk();

        $items = DB::table('spaced_repetition_items')->where('student_id', $student->id)->get()->keyBy('question_id');
        $this->assertCount(2, $items);

        // Remembered: matured through one successful review.
        $this->assertSame(1, $items[$firstQuestionId]->repetition_num);
        $this->assertEquals(2.6, $items[$firstQuestionId]->ease_factor);

        // Missed: lapsed, so the chain is reset and it is due the next day.
        $this->assertSame(0, $items[$secondQuestionId]->repetition_num);
        $this->assertSame('2026-01-11', $items[$secondQuestionId]->next_review_at);

        // Revisiting the calendar must not duplicate the schedule.
        $this->actingAs($student)->get(route('calendar'))->assertOk();
        $this->assertCount(2, DB::table('spaced_repetition_items')->where('student_id', $student->id)->get());
    }

    public function test_weak_topic_surfaces_as_a_high_priority_review_today(): void
    {
        $student = $this->student();

        $subjectId = DB::table('subjects')->insertGetId(['code' => 'TAX', 'name' => 'Taxation', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Withholding Tax', 'created_at' => now(), 'updated_at' => now()]);
        $questionId = DB::table('questions')->insertGetId(['topic_id' => $topicId, 'difficulty' => 'moderate', 'created_at' => now(), 'updated_at' => now()]);

        // Three wrong answers in a row flags the topic as weak regardless of
        // overall attempt count (WeaknessDetector::CONSECUTIVE_WRONG).
        DB::table('performance_records')->insert([
            'student_id' => $student->id,
            'topic_id' => $topicId,
            'correct_count' => 1,
            'total_attempts' => 4,
            'consecutive_wrong' => 3,
            'last_attempted' => now(),
        ]);

        // Pre-seed the item so ensureSchedule() only syncs the weakness flag
        // instead of bootstrapping a fresh schedule for it.
        DB::table('spaced_repetition_items')->insert([
            'student_id' => $student->id,
            'question_id' => $questionId,
            'repetition_num' => 0,
            'ease_factor' => 2.5,
            'interval_days' => 1,
            'last_reviewed' => now()->subDay()->toDateString(),
            'next_review_at' => now()->toDateString(),
        ]);

        $this->actingAs($student)->get(route('calendar'))
            ->assertOk()
            ->assertSee('Withholding Tax')
            ->assertSee('High priority');

        $this->assertDatabaseHasWeaknessReport($student->id, $topicId);
    }

    public function test_student_can_add_and_remove_a_study_plan(): void
    {
        $student = $this->student();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Revenue Recognition', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->post(route('calendar.plan.store'), [
            'topic_id' => $topicId,
            'plan_date' => '2026-08-03',
            'start_time' => '09:30',
            'duration_min' => 60,
        ])->assertRedirect();

        $plan = DB::table('calendar_plans')->where('student_id', $student->id)->first();
        $this->assertNotNull($plan);
        $this->assertSame('2026-08-03', $plan->plan_date);
        $this->assertSame(9 * 60 + 30, $plan->start_min);

        $this->actingAs($student)->delete(route('calendar.plan.destroy', $plan->id))->assertRedirect();
        $this->assertSame(0, DB::table('calendar_plans')->where('student_id', $student->id)->count());
    }

    public function test_a_student_cannot_delete_another_students_study_plan(): void
    {
        $owner = $this->student('owner@example.com');
        $other = $this->student('other@example.com');

        $planId = DB::table('calendar_plans')->insertGetId([
            'student_id' => $owner->id,
            'title' => 'Owner block',
            'plan_date' => '2026-08-03',
            'start_min' => 540,
            'duration_min' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($other)->delete(route('calendar.plan.destroy', $planId))->assertRedirect();

        $this->assertNotNull(DB::table('calendar_plans')->find($planId), 'a study plan must survive a delete request from a non-owner');
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

    private function assertDatabaseHasWeaknessReport(int $studentId, int $topicId): void
    {
        $this->assertNotNull(
            DB::table('weakness_reports')
                ->where('student_id', $studentId)
                ->where('topic_id', $topicId)
                ->whereNull('resolved_at')
                ->first(),
            'expected an open weakness_reports row for this topic'
        );
    }
}
