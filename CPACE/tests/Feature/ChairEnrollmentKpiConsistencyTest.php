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
 * The chair's "enrolled students" KPI cards on Students, Analytics, and
 * Sections all previously used different definitions of "enrolled" (raw
 * user count vs excluding alumni/shifted vs section-name-matched active
 * students), so the same cohort showed different totals on each page.
 * This locks the Students and Sections pages to the "not alumni, not
 * shifted" definition the Analytics cohort strip already used.
 */
class ChairEnrollmentKpiConsistencyTest extends TestCase
{
    private const TABLES = [
        'quiz_sessions', 'student_profiles', 'faculty_subject_sections', 'faculty_subjects', 'sections',
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
            $table->timestamp('setup_completed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('temp_password')->nullable();
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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('student_number', 30)->nullable();
            $table->string('section', 30)->nullable();
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->unsignedInteger('streak_days')->default(0);
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type', 20)->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
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

        DB::table('sections')->insert(['name' => 'BSA-4A', 'year_level' => 4, 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_students_page_kpis_exclude_alumni_and_shifted_accounts(): void
    {
        $this->makeStudent('active@example.com', 'BSA-4A');
        $alumnus = $this->makeStudent('alumnus@example.com', 'BSA-4A');
        DB::table('student_profiles')->where('user_id', $alumnus->id)->update(['is_alumni' => true]);
        $shifted = $this->makeStudent('shifted@example.com', 'BSA-4A', false);
        DB::table('student_profiles')->where('user_id', $shifted->id)->update(['is_shifted' => true]);

        $response = $this->actingAs($this->chair())->get(route('chair.students'));

        $response->assertOk();
        // 3 accounts exist, but only 1 is a currently-enrolled student.
        $this->assertSame(1, $response->viewData('stats')['total']);
        $this->assertSame(1, $response->viewData('stats')['active']);
    }

    public function test_students_page_reports_students_not_yet_assigned_to_a_curated_section(): void
    {
        $this->makeStudent('sectioned@example.com', 'BSA-4A');
        $this->makeStudent('typo@example.com', 'BSA-4A-TYPO');
        $alumnus = $this->makeStudent('alumnus@example.com', 'BSA-4A');
        DB::table('student_profiles')->where('user_id', $alumnus->id)->update(['is_alumni' => true]);

        $response = $this->actingAs($this->chair())->get(route('chair.students'));

        $response->assertOk();
        $sections = collect($response->viewData('sectionCatalog'));
        $this->assertSame(1, $sections->firstWhere('name', 'BSA-4A')->student_count);
        $this->assertSame(1, $response->viewData('noSectionCount'));
    }

    public function test_students_page_shows_section_tiles_and_a_section_roster(): void
    {
        $this->makeStudent('sectioned@example.com', 'BSA-4A');
        DB::table('sections')->insert(['name' => 'BSA 3101', 'year_level' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        foreach (range(1, 37) as $i) {
            $this->makeStudent("s{$i}@example.com", 'BSA 3101');
        }
        $chair = $this->chair();

        // New-format card: year, semester read from the name, count.
        // Old-format card (BSA-4A): no semester is made up.
        $this->actingAs($chair)->get(route('chair.students'))
            ->assertSeeInOrder(['BSA 3101', '3rd Year · 1st Sem · 37 Students'])
            ->assertSeeInOrder(['BSA-4A', '4th Year · 1 Student<'], false)
            ->assertDontSee('4th Year · 1st Sem')->assertDontSee('4th Year · 2nd Sem');

        // Default view: section cards only, the student list stays closed.
        $this->actingAs($chair)->get(route('chair.students'))
            ->assertOk()->assertSee('View Students')->assertSee('New Section')->assertSee('Board Readiness')
            ->assertDontSee('id="studentList"', false);

        $this->actingAs($chair)->get(route('chair.students', ['section' => 'BSA-4A']))
            ->assertOk()->assertSee('Add students to BSA-4A')->assertSee('Back to sections')
            ->assertSee('id="studentList"', false);
    }

    public function test_a_section_with_students_or_faculty_cannot_be_deleted(): void
    {
        $this->makeStudent('sectioned@example.com', 'BSA-4A');
        $section = DB::table('sections')->where('name', 'BSA-4A')->first();

        $response = $this->actingAs($this->chair())
            ->delete(route('chair.sections.destroy', $section->id));

        $response->assertRedirect();
        $this->assertNotNull(DB::table('sections')->find($section->id));
    }

    public function test_an_empty_section_can_be_deleted(): void
    {
        $section = DB::table('sections')->where('name', 'BSA-4A')->first();

        $response = $this->actingAs($this->chair())
            ->delete(route('chair.sections.destroy', $section->id));

        $response->assertRedirect();
        $this->assertNull(DB::table('sections')->find($section->id));
    }

    private function makeStudent(string $email, string $section, bool $active = true): User
    {
        $user = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => $active, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $user->id, 'section' => $section]);

        return $user;
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN,
            'first_name' => 'Program', 'last_name' => 'Chair',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }
}
