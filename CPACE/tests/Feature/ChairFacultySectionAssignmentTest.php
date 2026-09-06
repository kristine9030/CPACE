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
 * The Program Chair can restrict a faculty's subject assignment to specific
 * sections (faculty_subject_sections), on top of the existing subject-level
 * assignment (faculty_subjects). A subject with no section rows stays
 * unrestricted - the original assign-by-subject behavior. Hand-built schema,
 * same convention as the other Feature tests in this suite.
 */
class ChairFacultySectionAssignmentTest extends TestCase
{
    private const TABLES = [
        'faculty_subject_sections', 'faculty_subjects', 'faculty_profiles', 'sections', 'subjects', 'users',
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
            $table->boolean('email_verified')->default(false);
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
        });
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('employee_number', 20)->nullable();
            $table->string('department', 100)->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_chair_can_restrict_a_faculty_subject_assignment_to_specific_sections(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $sectionB = DB::table('sections')->insertGetId(['name' => 'BSA-3B', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($chair)->post(route('chair.faculty.assign', $faculty->id), [
            'subjects' => [$subjectId],
            'sections' => [$subjectId => [$sectionA]],
        ]);

        $response->assertRedirect(route('chair.faculty'));
        $this->assertTrue(DB::table('faculty_subjects')->where(['faculty_id' => $faculty->id, 'subject_id' => $subjectId])->exists());
        $this->assertTrue(DB::table('faculty_subject_sections')
            ->where(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'section_id' => $sectionA])
            ->exists());
        $this->assertFalse(DB::table('faculty_subject_sections')
            ->where(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'section_id' => $sectionB])
            ->exists());
    }

    public function test_reassigning_without_any_sections_removes_the_restriction(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        DB::table('faculty_subject_sections')->insert([
            'faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'section_id' => $sectionA,
            'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($chair)->post(route('chair.faculty.assign', $faculty->id), [
            'subjects' => [$subjectId],
        ])->assertRedirect(route('chair.faculty'));

        $this->assertFalse(DB::table('faculty_subject_sections')->where('faculty_id', $faculty->id)->exists());
    }

    public function test_unassigning_a_subject_clears_its_leftover_section_restrictions(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        DB::table('faculty_subject_sections')->insert([
            'faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'section_id' => $sectionA,
            'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Saving the modal with no subjects ticked unassigns everything.
        $this->actingAs($chair)->post(route('chair.faculty.assign', $faculty->id), [])
            ->assertRedirect(route('chair.faculty'));

        $this->assertFalse(DB::table('faculty_subjects')->where('faculty_id', $faculty->id)->exists());
        $this->assertFalse(DB::table('faculty_subject_sections')->where('faculty_id', $faculty->id)->exists());
    }

    public function test_a_section_id_that_does_not_exist_is_rejected(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);

        $this->actingAs($chair)->post(route('chair.faculty.assign', $faculty->id), [
            'subjects' => [$subjectId],
            'sections' => [$subjectId => [999999]],
        ])->assertSessionHasErrors();
    }

    public function test_chair_can_add_a_new_section_from_the_sections_page(): void
    {
        $chair = $this->chair();

        $this->actingAs($chair)->post(route('chair.sections.store'), ['name' => 'BSA-4C'])
            ->assertRedirect();

        $this->assertTrue(DB::table('sections')->where('name', 'BSA-4C')->exists());
    }

    public function test_a_duplicate_section_name_is_rejected(): void
    {
        $chair = $this->chair();
        DB::table('sections')->insert(['name' => 'BSA-4C', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($chair)->post(route('chair.sections.store'), ['name' => 'BSA-4C'])
            ->assertSessionHasErrors('name');
    }

    public function test_chair_can_toggle_a_section_inactive(): void
    {
        $chair = $this->chair();
        $sectionId = DB::table('sections')->insertGetId(['name' => 'BSA-4C', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($chair)->post(route('chair.sections.toggle', $sectionId))->assertRedirect();

        $this->assertFalse((bool) DB::table('sections')->where('id', $sectionId)->value('is_active'));
    }

    public function test_creating_a_faculty_account_can_scope_a_subject_to_sections(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $chair = $this->chair();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($chair)->post(route('chair.faculty.store'), [
            'first_name' => 'New', 'last_name' => 'Faculty', 'email' => 'newfaculty@example.com',
            'subjects' => [$subjectId],
            'sections' => [$subjectId => [$sectionA]],
        ]);

        $response->assertRedirect(route('chair.faculty'));
        $newFacultyId = DB::table('users')->where('email', 'newfaculty@example.com')->value('id');
        $this->assertTrue(DB::table('faculty_subject_sections')
            ->where(['faculty_id' => $newFacultyId, 'subject_id' => $subjectId, 'section_id' => $sectionA])
            ->exists());
    }

    public function test_editing_a_faculty_account_can_change_its_section_restriction(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting']);
        $sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $sectionB = DB::table('sections')->insertGetId(['name' => 'BSA-3B', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        DB::table('faculty_subject_sections')->insert([
            'faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'section_id' => $sectionA,
            'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($chair)->put(route('chair.faculty.update', $faculty->id), [
            'first_name' => $faculty->first_name, 'last_name' => $faculty->last_name, 'email' => $faculty->email,
            'is_active' => '1',
            'subjects' => [$subjectId],
            'sections' => [$subjectId => [$sectionB]],
        ]);

        $response->assertRedirect(route('chair.faculty'));
        $this->assertFalse(DB::table('faculty_subject_sections')
            ->where(['faculty_id' => $faculty->id, 'section_id' => $sectionA])
            ->exists());
        $this->assertTrue(DB::table('faculty_subject_sections')
            ->where(['faculty_id' => $faculty->id, 'section_id' => $sectionB])
            ->exists());
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN, 'first_name' => 'Chair', 'last_name' => 'User',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }
}
