<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\ChairAnalyticsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChairAnalyticsTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order.
     *
     * The schema is built by hand rather than with RefreshDatabase because the
     * migration set cannot run from scratch — `alumni_profiles` is altered by a
     * migration but never created by one.
     */
    private const TABLES = [
        'messages', 'conversation_participants', 'conversations', 'student_profiles',
        'notifications', 'question_variants', 'quiz_answers', 'quiz_sessions',
        'performance_records', 'questions', 'faculty_subjects', 'topics', 'subjects', 'users',
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
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
        });
        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->timestamps();
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body')->nullable();
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->unsignedTinyInteger('passing_threshold')->default(75);
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
            $table->integer('correct_count');
            $table->integer('total_attempts');
            $table->boolean('is_weak_area')->default(false);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type');
            $table->integer('total_items');
            $table->integer('correct_answers');
            $table->integer('duration_secs')->default(0);
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('question_id');
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
        });

        $this->seedAnalyticsData();
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_chair_can_open_both_analytics_reports(): void
    {
        $chair = User::where('email', 'chair@example.com')->firstOrFail();

        $this->actingAs($chair)->get(route('chair.analytics.performance'))
            ->assertOk()
            ->assertSee('Class-Level Performance')
            ->assertSee('70%')
            ->assertSee('Predicted Pass Rate');

        $this->actingAs($chair)->get(route('chair.analytics.test-bank-coverage'))
            ->assertOk()
            ->assertSee('Test Bank Coverage')
            ->assertSee('Thin Areas')
            ->assertSee('Needs 20 more');
    }

    public function test_chair_dashboard_and_faculty_report_render(): void
    {
        $chair = User::where('email', 'chair@example.com')->firstOrFail();

        $this->actingAs($chair)->get(route('chair.dashboard'))->assertOk();
        $this->actingAs($chair)->get(route('chair.faculty.performance'))->assertOk();
    }

    public function test_service_calculates_weighted_accuracy_readiness_and_coverage(): void
    {
        $analytics = app(ChairAnalyticsService::class);
        $report = $analytics->performanceReport();
        $coverage = $analytics->coverageReport();

        $this->assertSame(70, $report['overall_accuracy']);
        $this->assertSame(100, $report['readiness']['readiness_rate']);
        $this->assertSame(100, $report['readiness']['pass_projection']);
        $this->assertSame('thin', $coverage->firstWhere('name', 'Topic 1')['status']);
        $this->assertSame('critical', $coverage->firstWhere('name', 'Topic 2')['status']);
    }

    public function test_coverage_rolls_subtopic_questions_up_into_their_curriculum_area(): void
    {
        $coverage = app(ChairAnalyticsService::class)->coverageReport();

        // Only top-level areas are reported — a nested subtopic is never its own row.
        $this->assertCount(3, $coverage);
        $this->assertNull($coverage->firstWhere('name', 'Topic 1 — Subtopic'));

        $area = $coverage->firstWhere('name', 'Topic 1');
        $this->assertSame(2, $area['subtopics']);
        // 2 questions on the area itself + 3 on its first subtopic.
        $this->assertSame(5, $area['active']);
        $this->assertSame(20, $area['gap']);
    }

    public function test_subject_performance_reports_distance_to_the_passing_threshold(): void
    {
        $subjects = app(ChairAnalyticsService::class)->subjectPerformance();

        $aud = $subjects->firstWhere('code', 'AUD');
        $this->assertSame(70, $aud['accuracy']);
        $this->assertSame(75, $aud['threshold']);
        $this->assertSame(-5, $aud['gap_to_threshold']);
        $this->assertFalse($aud['meets_threshold']);
    }

    public function test_engagement_distribution_and_difficulty_series_are_chart_ready(): void
    {
        $analytics = app(ChairAnalyticsService::class);

        $engagement = $analytics->engagementTrend(8);
        $this->assertCount(8, $engagement);
        // The seeded sessions all completed this week, so only the last bucket fills.
        $this->assertSame(1, $engagement->last()['active_students']);
        $this->assertSame(60, $engagement->last()['items']);

        $distribution = $analytics->scoreDistribution();
        $this->assertCount(6, $distribution);
        $this->assertSame(1, $distribution->firstWhere('label', '70–79%')['students']);

        $difficulty = $analytics->difficultyPerformance();
        $this->assertSame(['Easy', 'Moderate', 'Difficult'], $difficulty->pluck('label')->all());
        $this->assertSame(50, $difficulty->firstWhere('label', 'Easy')['accuracy']);
        $this->assertNull($difficulty->firstWhere('label', 'Difficult')['accuracy']);
    }

    public function test_weakest_topics_ignores_topics_below_the_attempt_floor(): void
    {
        $weak = app(ChairAnalyticsService::class)->weakestTopics(10);

        $this->assertNotEmpty($weak);
        $this->assertNull($weak->firstWhere('name', 'Topic 1 — Subtopic'), 'a 4-attempt topic is below the floor');
        foreach ($weak as $topic) {
            $this->assertGreaterThanOrEqual(ChairAnalyticsService::TOPIC_MIN_ATTEMPTS, $topic['attempts']);
        }
    }

    public function test_student_cannot_open_chair_analytics(): void
    {
        $student = User::where('email', 'student@example.com')->firstOrFail();

        $this->actingAs($student)->get(route('chair.analytics.performance'))->assertForbidden();
    }

    private function seedAnalyticsData(): void
    {
        // setup_completed_at is set so EnsureAccountSetup does not divert these
        // requests into first-login onboarding before role checks are reached.
        User::create(['role_id' => Role::ADMIN, 'first_name' => 'Program', 'last_name' => 'Chair', 'email' => 'chair@example.com', 'password' => Hash::make('password'), 'is_active' => true, 'setup_completed_at' => now()]);
        $student = User::create(['role_id' => Role::STUDENT, 'first_name' => 'Test', 'last_name' => 'Student', 'email' => 'student@example.com', 'password' => Hash::make('password'), 'is_active' => true, 'setup_completed_at' => now()]);
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'created_at' => now(), 'updated_at' => now()]);

        $firstTopicId = null;
        foreach ([['AUD', 'Auditing'], ['FAR', 'Financial Accounting'], ['TAX', 'Taxation']] as $index => [$code, $name]) {
            $subjectId = DB::table('subjects')->insertGetId(['code' => $code, 'name' => $name, 'passing_threshold' => 75, 'created_at' => now(), 'updated_at' => now()]);
            $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'parent_id' => null, 'name' => 'Topic '.($index + 1), 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $topicId, 'correct_count' => 14, 'total_attempts' => 20, 'is_weak_area' => false]);
            $sessionId = DB::table('quiz_sessions')->insertGetId(['student_id' => $student->id, 'subject_id' => $subjectId, 'session_type' => 'adaptive', 'total_items' => 20, 'correct_answers' => 14, 'duration_secs' => 600, 'completed_at' => now()]);

            if ($index === 0) {
                $firstTopicId = $topicId;
                $easyId = DB::table('questions')->insertGetId(['topic_id' => $topicId, 'difficulty' => 'easy', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('questions')->insert(['topic_id' => $topicId, 'difficulty' => 'moderate', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

                // Two answers on one easy question → 50% accuracy on the easy band.
                DB::table('quiz_answers')->insert([
                    ['session_id' => $sessionId, 'question_id' => $easyId, 'is_correct' => true, 'answered_at' => now()],
                    ['session_id' => $sessionId, 'question_id' => $easyId, 'is_correct' => false, 'answered_at' => now()],
                ]);
            }
        }

        // Two nested subtopics under Topic 1. Their questions must roll up into
        // Topic 1's coverage, and each sits under the weak-topic attempt floor.
        // Their 7/7 and 0/3 records cancel to 70%, so every headline accuracy
        // figure in this suite stays exactly where it was.
        $subtopics = [
            ['Topic 1 — Subtopic', 7, 7, false, ['easy', 'moderate', 'moderate']],
            ['Topic 1 — Second subtopic', 0, 3, true, []],
        ];

        foreach ($subtopics as [$name, $correct, $attempts, $weak, $difficulties]) {
            $subtopicId = DB::table('topics')->insertGetId(['subject_id' => 1, 'parent_id' => $firstTopicId, 'name' => $name, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $subtopicId, 'correct_count' => $correct, 'total_attempts' => $attempts, 'is_weak_area' => $weak]);

            foreach ($difficulties as $difficulty) {
                DB::table('questions')->insert(['topic_id' => $subtopicId, 'difficulty' => $difficulty, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
}
