<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Faculty "Student Performance" must mirror the Test Bank's subject scoping:
 * the Subject filter, and the students/stats/weak-topics it computes, are
 * restricted to the subjects the Program Chair assigned to this faculty
 * member. Hand-built schema, same convention as the other Feature tests.
 */
class FacultyPerformanceScopeTest extends TestCase
{
    private const TABLES = [
        'faculty_subject_sections', 'sections', 'student_profiles',
        'performance_records', 'quiz_sessions', 'topics', 'subjects', 'faculty_subjects',
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
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type', 50)->default('normal');
            $table->string('title', 150)->default('');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
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
            $table->string('mode')->default('adaptive');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('total_items')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->unsignedInteger('duration_secs')->nullable();
        });

        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->unsignedInteger('total_attempts')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('consecutive_wrong')->default(0);
        });

        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('section', 30)->nullable();
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

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function student(string $email, ?string $section = null): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Student', 'last_name' => $email,
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);

        DB::table('student_profiles')->insert(['user_id' => $student->id, 'section' => $section]);

        return $student;
    }

    public function test_a_faculty_restricted_to_a_section_only_sees_that_sections_students(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subject_sections')->insert([
            'faculty_id' => $faculty->id, 'subject_id' => $farId, 'section_id' => $sectionA,
            'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $inSection = $this->student('in-section@example.com', 'BSA-3A');
        $outOfSection = $this->student('out-of-section@example.com', 'BSA-3B');
        DB::table('quiz_sessions')->insert([
            'student_id' => $inSection->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 7,
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $outOfSection->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 7,
        ]);

        $response = $this->actingAs($faculty)->get(route('faculty.performance'));

        $response->assertOk();
        $response->assertViewHas('students', function ($students) use ($inSection) {
            return $students->count() === 1 && $students->first()['id'] === $inSection->id;
        });
    }

    public function test_faculty_only_sees_the_subject_filter_and_students_from_their_assigned_subjects(): void
    {
        $faculty = $this->faculty();

        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);

        $farStudent = $this->student('far-student@example.com');
        $audStudent = $this->student('aud-student@example.com');

        DB::table('quiz_sessions')->insert([
            'student_id' => $farStudent->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 7,
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $audStudent->id, 'session_type' => 'testing', 'subject_id' => $audId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 3,
        ]);

        $response = $this->actingAs($faculty)->get(route('faculty.performance'));

        $response->assertOk();
        $response->assertViewHas('subjects', fn ($subjects) => $subjects->pluck('code')->all() === ['FAR']);
        $response->assertViewHas('students', function ($students) use ($farStudent) {
            return $students->count() === 1 && $students->first()['id'] === $farStudent->id;
        });
        $response->assertViewHas('stats', fn ($stats) => $stats['active'] === 1);
    }

    public function test_exporting_streams_a_csv_scoped_to_the_facultys_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $farStudent = $this->student('far-student@example.com');
        $audStudent = $this->student('aud-student@example.com');
        DB::table('quiz_sessions')->insert([
            'student_id' => $farStudent->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 7,
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $audStudent->id, 'session_type' => 'testing', 'subject_id' => $audId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 7,
        ]);

        $response = $this->actingAs($faculty)->get(route('faculty.performance.export'));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString($farStudent->email, $csv);
        $this->assertStringNotContainsString($audStudent->email, $csv);
    }

    public function test_sending_a_reminder_notifies_only_at_risk_students_by_default(): void
    {
        Mail::fake();
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $weakStudent = $this->student('weak@example.com');
        $strongStudent = $this->student('strong@example.com');
        // 5 attempts, 40% accuracy: at-risk (meets MIN_ATTEMPTS, below threshold).
        DB::table('quiz_sessions')->insert([
            'student_id' => $weakStudent->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 5, 'correct_answers' => 2,
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $strongStudent->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 5, 'correct_answers' => 5,
        ]);

        $this->actingAs($faculty)->post(route('faculty.performance.remind'))->assertRedirect();

        $this->assertTrue(DB::table('notifications')->where('recipient_id', $weakStudent->id)->exists());
        $this->assertFalse(DB::table('notifications')->where('recipient_id', $strongStudent->id)->exists());
    }

    public function test_sending_a_reminder_with_scope_all_notifies_every_scoped_student(): void
    {
        Mail::fake();
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $strongStudent = $this->student('strong@example.com');
        DB::table('quiz_sessions')->insert([
            'student_id' => $strongStudent->id, 'session_type' => 'testing', 'subject_id' => $farId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 5, 'correct_answers' => 5,
        ]);

        $this->actingAs($faculty)->post(route('faculty.performance.remind'), ['scope' => 'all'])->assertRedirect();

        $this->assertTrue(DB::table('notifications')->where('recipient_id', $strongStudent->id)->exists());
    }

    public function test_requesting_a_subject_id_outside_the_faculty_assignment_is_ignored(): void
    {
        $faculty = $this->faculty();

        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing']);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);

        $audStudent = $this->student('aud-student@example.com');
        DB::table('quiz_sessions')->insert([
            'student_id' => $audStudent->id, 'session_type' => 'testing', 'subject_id' => $audId,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => 10, 'correct_answers' => 3,
        ]);

        $response = $this->actingAs($faculty)->get(route('faculty.performance', ['subject' => $audId]));

        $response->assertOk();
        // The invalid subject filter is dropped, so no AUD student leaks through.
        $response->assertViewHas('students', fn ($students) => $students->count() === 0);
        $response->assertViewHas('filters', fn ($filters) => $filters['subject'] === null);
    }
}
