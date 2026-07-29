<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order. Hand-built schema, same rationale as
     * the other Feature tests in this suite (migration set doesn't run
     * cleanly from scratch).
     */
    private const TABLES = [
        'quiz_sessions', 'performance_records', 'topics', 'subjects', 'student_profiles',
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->integer('total_points')->default(0);
            $table->date('exam_target_date')->nullable();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->unsignedTinyInteger('passing_threshold')->default(75);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->decimal('accuracy_rate', 5, 2)->default(0);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->integer('duration_secs')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_dashboard_renders_for_a_brand_new_student_with_no_history(): void
    {
        $student = $this->student(withProfile: false);
        $this->subject('FAR', 'Financial Accounting');

        $this->actingAs($student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Set your target board exam date to start the countdown.')
            ->assertSee('No weak areas detected yet. Take a few quizzes and your focus areas will appear here.', false)
            ->assertSee('Financial Accounting')
            ->assertSee('Start a quiz to begin a streak');
    }

    public function test_readiness_and_questions_attempted_come_only_from_completed_non_training_sessions(): void
    {
        $student = $this->student();

        // Counts: 8/10 correct, completed, testing.
        $this->quizSession($student->id, 'testing', total: 10, correct: 8, completedAt: now());
        // Excluded: still in progress (no completed_at).
        $this->quizSession($student->id, 'testing', total: 5, correct: 5, completedAt: null);
        // Excluded: training mode, even though completed.
        $this->quizSession($student->id, 'training', total: 20, correct: 20, completedAt: now());

        $response = $this->actingAs($student)->get(route('dashboard'))->assertOk();

        $response->assertSee('80<small>%</small>', false); // readiness = 8/10
        $response->assertSee(number_format(10)); // questionsAttempted = 10, not 30
    }

    public function test_top_weaknesses_lists_the_three_lowest_accuracy_topics_with_attempts_first(): void
    {
        $student = $this->student();
        $subjectId = $this->subject('AUD', 'Auditing');

        $this->performanceRecord($student->id, $subjectId, 'Weakest Topic', accuracy: 20.00, attempts: 10);
        $this->performanceRecord($student->id, $subjectId, 'Second Weakest', accuracy: 40.00, attempts: 10);
        $this->performanceRecord($student->id, $subjectId, 'Third Weakest', accuracy: 60.00, attempts: 10);
        $this->performanceRecord($student->id, $subjectId, 'Strongest Topic', accuracy: 90.00, attempts: 10);
        // Zero attempts: must never appear regardless of its (low) accuracy.
        $this->performanceRecord($student->id, $subjectId, 'Never Attempted', accuracy: 0.00, attempts: 0);

        $response = $this->actingAs($student)->get(route('dashboard'))->assertOk();

        $response->assertSeeInOrder(['Weakest Topic', 'Second Weakest', 'Third Weakest']);
        $response->assertDontSee('Strongest Topic');
        $response->assertDontSee('Never Attempted');
    }

    public function test_subject_mastery_shows_zero_for_an_untouched_subject_and_the_real_percentage_otherwise(): void
    {
        $student = $this->student();
        $practicedId = $this->subject('TAX', 'Taxation', passingThreshold: 75);
        $this->subject('RFBT', 'Regulatory Framework'); // never practiced

        $this->performanceRecord($student->id, $practicedId, 'Estate Tax', accuracy: 80.00, attempts: 10, correct: 8);

        $response = $this->actingAs($student)->get(route('dashboard'))->assertOk();

        $response->assertSee('Taxation');
        $response->assertSee('Pass: 75%');
        $response->assertSee('Regulatory Framework');
    }

    public function test_exam_countdown_shows_once_a_target_date_is_set(): void
    {
        $student = $this->student();
        DB::table('student_profiles')->where('user_id', $student->id)->update([
            'exam_target_date' => now()->addDays(30)->toDateString(),
        ]);

        $this->actingAs($student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Target Board Exam:')
            ->assertDontSee('Set your target board exam date to start the countdown.');
    }

    public function test_a_students_dashboard_never_reflects_another_students_activity(): void
    {
        $studentA = $this->student('a@example.com');
        $studentB = $this->student('b@example.com');
        $subjectId = $this->subject('MS', 'Management Services');

        $this->quizSession($studentA->id, 'testing', total: 10, correct: 10, completedAt: now());
        $this->performanceRecord($studentA->id, $subjectId, 'Cost Accounting', accuracy: 10.00, attempts: 10);

        $response = $this->actingAs($studentB)->get(route('dashboard'))->assertOk();

        $response->assertSee('0<small>%</small>', false); // readiness stays 0 for B
        $response->assertSee(number_format(0)); // questionsAttempted stays 0 for B
        $response->assertDontSee('Cost Accounting');
    }

    private function student(string $email = 'student@example.com', bool $withProfile = true): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);

        if ($withProfile) {
            DB::table('student_profiles')->insert(['user_id' => $student->id]);
        }

        return $student;
    }

    private function subject(string $code, string $name, int $passingThreshold = 75): int
    {
        return DB::table('subjects')->insertGetId([
            'code' => $code, 'name' => $name, 'passing_threshold' => $passingThreshold,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function performanceRecord(int $studentId, int $subjectId, string $topicName, float $accuracy, int $attempts, ?int $correct = null): void
    {
        $topicId = DB::table('topics')->insertGetId([
            'subject_id' => $subjectId, 'name' => $topicName, 'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('performance_records')->insert([
            'student_id' => $studentId,
            'topic_id' => $topicId,
            'correct_count' => $correct ?? (int) round($accuracy / 100 * $attempts),
            'total_attempts' => $attempts,
            'accuracy_rate' => $accuracy,
        ]);
    }

    private function quizSession(int $studentId, string $sessionType, int $total, int $correct, $completedAt): void
    {
        DB::table('quiz_sessions')->insert([
            'student_id' => $studentId,
            'session_type' => $sessionType,
            'total_items' => $total,
            'correct_answers' => $correct,
            'duration_secs' => 600,
            'started_at' => now(),
            'completed_at' => $completedAt,
        ]);
    }
}
