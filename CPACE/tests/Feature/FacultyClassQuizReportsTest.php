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
 * Faculty Reports must scope every roster to the subjects the Program Chair
 * assigned to the signed-in faculty member, same as Test Bank and Student
 * Performance already do. Hand-built schema, same rationale as the other
 * Feature tests in this suite.
 */
/**
 * The Quiz Results Report and the Individual Student Report: class quiz scores on paper.
 */
class FacultyClassQuizReportsTest extends TestCase
{
    private const TABLES = [
        'faculty_quiz_attempts', 'faculty_quizzes', 'faculty_subject_sections', 'sections',
        'weakness_reports', 'performance_records', 'quiz_sessions', 'topics', 'subjects', 'faculty_subjects',
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
            $table->timestamp('last_login_at')->nullable();
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('section', 30)->nullable();
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
            $table->boolean('is_active')->default(true);
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
        Schema::create('faculty_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->unsignedBigInteger('student_id');
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->json('answers')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total_points')->default(0);
            $table->decimal('percent', 5, 2)->nullable();
            $table->unsignedInteger('flag_count')->default(0);
            $table->timestamps();
            $table->unique(['quiz_id', 'student_id']);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type')->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('total_items')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->unsignedInteger('total_attempts')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('consecutive_wrong')->default(0);
        });
        Schema::create('weakness_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->timestamp('resolved_at')->nullable();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subject_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
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

    private function faculty(string $email = 'faculty@example.com'): User
    {
        return User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Fay', 'last_name' => 'Culty',
            'email' => $email, 'password' => Hash::make('password'), 'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function student(string $name, ?string $section): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT, 'first_name' => $name, 'last_name' => 'Reviewer',
            'email' => strtolower($name) . '@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'section' => $section]);

        return $student;
    }

    private function quiz(User $faculty, int $subjectId, string $title, string $status = 'published', array $extra = []): int
    {
        return DB::table('faculty_quizzes')->insertGetId($extra + [
            'faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'title' => $title, 'status' => $status,
            'share_token' => bin2hex(random_bytes(8)), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function attempt(int $quizId, User $student, int $score, int $total): void
    {
        DB::table('faculty_quiz_attempts')->insert([
            'quiz_id' => $quizId, 'student_id' => $student->id, 'started_at' => now()->subHour(), 'submitted_at' => now(),
            'score' => $score, 'total_points' => $total, 'percent' => round($score / $total * 100, 2),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** A faculty assigned to FAR for section BSA-3A only. */
    private function setUpFacultyAndSubject(): array
    {
        $faculty = $this->faculty();
        $far = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $far, 'assigned_at' => now()]);
        $a = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subject_sections')->insert([
            'faculty_id' => $faculty->id, 'subject_id' => $far, 'section_id' => $a,
            'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$faculty, $far];
    }

    public function test_the_quiz_report_lists_every_assigned_student_with_best_score_or_a_dash(): void
    {
        [$faculty, $far] = $this->setUpFacultyAndSubject();
        $alma = $this->student('Alma', 'BSA-3A');
        $ben = $this->student('Ben', 'BSA-3A');
        $this->student('Cara', 'BSA-3A');
        $this->student('Zed', 'BSA-3B');     // not this faculty's section
        $quiz = $this->quiz($faculty, $far, 'Cost Accounting Quiz 1');
        $this->attempt($quiz, $alma, 9, 10);          // 90
        $this->attempt($quiz, $ben, 7, 10);           // 70

        $response = $this->actingAs($faculty)->get(route('faculty.reports', ['report' => 'quiz_results']))->assertOk();

        $response->assertViewHas('quizReport', function ($r) {
            return $r['assigned'] === 3 && $r['submitted'] === 2 && $r['average'] === 80
                && $r['rows']->pluck('name')->all() === ['Alma Reviewer', 'Ben Reviewer', 'Cara Reviewer']
                && $r['rows']->firstWhere('name', 'Cara Reviewer')['best'] === null;
        });
        $response->assertSee('Quiz Results Report')->assertSee('Cost Accounting Quiz 1')->assertSee('Average best score')
            ->assertSee('90%')->assertDontSee('Zed Reviewer');
    }

    public function test_the_quiz_report_can_be_narrowed_to_a_section_and_only_offers_published_quizzes(): void
    {
        [$faculty, $far] = $this->setUpFacultyAndSubject();
        $this->student('Alma', 'BSA-3A');
        $this->quiz($faculty, $far, 'Published quiz');
        $this->quiz($faculty, $far, 'Still a draft', 'draft');
        $this->quiz($this->faculty('other@example.com'), $far, 'Someone elses quiz');

        $this->actingAs($faculty)->get(route('faculty.reports', ['report' => 'quiz_results']))
            ->assertViewHas('quizOptions', fn ($o) => collect($o)->pluck('title')->all() === ['Published quiz']);

        $this->actingAs($faculty)->get(route('faculty.reports', ['report' => 'quiz_results', 'group' => 'BSA-3Z']))
            ->assertViewHas('quizReport', fn ($r) => $r['assigned'] === 0);
    }

    public function test_the_student_report_shows_every_class_quiz_score_for_that_student(): void
    {
        [$faculty, $far] = $this->setUpFacultyAndSubject();
        $alma = $this->student('Alma', 'BSA-3A');
        $q1 = $this->quiz($faculty, $far, 'Quiz 1');
        $q2 = $this->quiz($faculty, $far, 'Quiz 2');
        $this->quiz($faculty, $far, 'Quiz 3 (closed)', 'closed');
        $this->quiz($faculty, $far, 'Quiz 4 (open)');
        $this->quiz($faculty, $far, 'Draft quiz', 'draft');
        $this->attempt($q1, $alma, 9, 10);   // 90 passed
        $this->attempt($q2, $alma, 5, 10);   // 50 below passing

        $response = $this->actingAs($faculty)->get(route('faculty.reports', ['report' => 'student_report', 'student' => $alma->id]))->assertOk();

        $response->assertViewHas('studentReport', function ($r) {
            $status = $r['rows']->pluck('status', 'title');

            return $r['total'] === 4 && $r['taken'] === 2 && $r['average'] === 70 && $r['best'] === 90
                && $status['Quiz 1'] === 'Passed' && $status['Quiz 2'] === 'Below passing'
                && $status['Quiz 3 (closed)'] === 'Missed' && $status['Quiz 4 (open)'] === 'Not taken yet'
                && ! $status->has('Draft quiz');
        });
        $response->assertSee('Individual Student Report')->assertSee('Alma Reviewer')->assertSee('Quiz 1')->assertSee('Below passing');
    }

    public function test_the_student_report_ignores_other_faculty_quizzes_and_asks_for_a_student_first(): void
    {
        [$faculty, $far] = $this->setUpFacultyAndSubject();
        $alma = $this->student('Alma', 'BSA-3A');
        $theirs = $this->quiz($this->faculty('other@example.com'), $far, 'Someone elses quiz');
        $this->attempt($theirs, $alma, 10, 10);

        $this->actingAs($faculty)->get(route('faculty.reports', ['report' => 'student_report', 'student' => $alma->id]))
            ->assertViewHas('studentReport', fn ($r) => $r['total'] === 0 && $r['taken'] === 0);

        $this->actingAs($faculty)->get(route('faculty.reports', ['report' => 'student_report']))
            ->assertOk()->assertSee('Pick a student on the left');
    }

    public function test_both_new_reports_download_as_excel(): void
    {
        [$faculty, $far] = $this->setUpFacultyAndSubject();
        $alma = $this->student('Alma', 'BSA-3A');
        $quiz = $this->quiz($faculty, $far, 'Quiz 1');
        $this->attempt($quiz, $alma, 8, 10);

        foreach ([['report' => 'quiz_results'], ['report' => 'student_report', 'student' => $alma->id]] as $query) {
            $response = $this->actingAs($faculty)->get(route('faculty.reports.export', $query));
            $response->assertOk();
            $this->assertStringContainsString('spreadsheetml', (string) $response->headers->get('content-type'));
        }
    }
}
