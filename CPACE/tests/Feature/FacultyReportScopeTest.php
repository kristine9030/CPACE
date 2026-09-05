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
class FacultyReportScopeTest extends TestCase
{
    private const TABLES = [
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
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type')->default('testing');
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
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_report_only_includes_students_from_the_facultys_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);

        $farStudent = $this->student('far-student@example.com');
        $audStudent = $this->student('aud-student@example.com');
        $this->completedSession($farStudent->id, $farId, 10, 7);
        $this->completedSession($audStudent->id, $audId, 10, 3);

        $response = $this->actingAs($faculty)->get(route('faculty.reports'));

        $response->assertOk();
        $response->assertViewHas('students', function ($students) use ($farStudent) {
            return $students->count() === 1 && $students->first()['id'] === $farStudent->id;
        });
    }

    public function test_an_out_of_scope_subject_filter_falls_back_to_all_assigned_subjects_not_everything(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);

        $audStudent = $this->student('aud-student@example.com');
        $this->completedSession($audStudent->id, $audId, 10, 3);

        $response = $this->actingAs($faculty)->get(route('faculty.reports', ['scope' => $audId]));

        $response->assertOk();
        $response->assertViewHas('students', fn ($students) => $students->count() === 0);
    }

    public function test_a_student_below_the_accuracy_threshold_with_enough_attempts_is_flagged_at_risk(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $student = $this->student('weak@example.com');
        // 5 attempts, 40% accuracy: meets MIN_ATTEMPTS and is below ACCURACY_THRESHOLD.
        $this->completedSession($student->id, $farId, 5, 2);

        $response = $this->actingAs($faculty)->get(route('faculty.reports'));

        $response->assertViewHas('stats', fn ($stats) => $stats['at_risk'] === 1);
        $response->assertViewHas('atRisk', fn ($atRisk) => $atRisk->count() === 1 && $atRisk->first()['id'] === $student->id);
    }

    public function test_exporting_the_class_summary_report_streams_a_csv_of_the_scoped_roster(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $farStudent = $this->student('far-student@example.com');
        $audStudent = $this->student('aud-student@example.com');
        $this->completedSession($farStudent->id, $farId, 10, 8);
        $this->completedSession($audStudent->id, $audId, 10, 8);

        $response = $this->actingAs($faculty)->get(route('faculty.reports.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString($farStudent->email, $csv);
        $this->assertStringNotContainsString($audStudent->email, $csv);
    }

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function student(string $email): User
    {
        return User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Student', 'last_name' => $email,
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function completedSession(int $studentId, int $subjectId, int $totalItems, int $correct): void
    {
        DB::table('quiz_sessions')->insert([
            'student_id' => $studentId, 'session_type' => 'testing', 'subject_id' => $subjectId,
            'started_at' => now(), 'completed_at' => now(),
            'total_items' => $totalItems, 'correct_answers' => $correct,
        ]);
    }
}
