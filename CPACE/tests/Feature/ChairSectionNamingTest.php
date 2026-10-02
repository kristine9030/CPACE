<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Section naming: "<PROGRAM> <year><sem><section no.>" (BSA 3101 = BSA,
 * 3rd Year, 1st Sem, Section 01). The server builds the name from its parts,
 * rejects duplicates, and a rename carries students and mock-exam audiences
 * (which point at a section by name) along in one transaction. Hand-built
 * schema, same convention as the other Feature tests in this suite.
 */
class ChairSectionNamingTest extends TestCase
{
    private const TABLES = [
        'mock_exams', 'student_profiles', 'sections', 'notifications', 'messages',
        'conversation_participants', 'conversations', 'users',
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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->string('section', 30)->nullable();
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
        });
        Schema::create('mock_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('audience_sections')->nullable();
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
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
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    // ── Format helpers ───────────────────────────────────────────────────────

    public function test_names_are_composed_and_parsed_in_the_program_year_sem_number_format(): void
    {
        $this->assertSame('BSA 3101', Section::composeName('bsa', 3, 1, 1));
        $this->assertSame('BSA 4210', Section::composeName('BSA', 4, 2, 10));
        $this->assertSame(
            ['program' => 'BSA', 'year_level' => 3, 'semester' => 2, 'section_number' => 1],
            Section::parseName('BSA 3201')
        );

        // Older / unsupported names are never re-interpreted.
        foreach (['BSA-3A', 'BSA-4A', 'BSA 5101', 'BSA 3301', 'BSA3101', 'bsa 3101'] as $old) {
            $this->assertNull(Section::parseName($old), $old);
        }
        $this->assertSame('1st Sem', (new Section(['name' => 'BSA 3101']))->semester_label);
        $this->assertNull((new Section(['name' => 'BSA-3A']))->semester_label);
    }

    // ── Scenarios 1–4: create ────────────────────────────────────────────────

    public function test_sections_are_created_from_program_year_semester_and_number(): void
    {
        $chair = $this->chair();

        foreach ([[3, 1, 1], [3, 1, 2], [3, 2, 1], [4, 1, 1]] as [$year, $sem, $no]) {
            $this->actingAs($chair)->post(route('chair.sections.store'), $this->parts('BSA', $year, $sem, $no))
                ->assertRedirect()->assertSessionHasNoErrors();
        }

        foreach (['BSA 3101' => 3, 'BSA 3102' => 3, 'BSA 3201' => 3, 'BSA 4101' => 4] as $name => $year) {
            $this->assertDatabaseHas('sections', ['name' => $name, 'year_level' => $year]);
        }
    }

    public function test_section_number_is_zero_padded_and_program_uppercased(): void
    {
        $chair = $this->chair();

        $this->actingAs($chair)->post(route('chair.sections.store'), $this->parts('bsa', 2, 2, 10))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sections', ['name' => 'BSA 2210']);
    }

    public function test_the_padded_section_number_the_form_sends_is_accepted(): void
    {
        // The form shows and submits "01", not 1.
        $chair = $this->chair();
        $id = $this->section('BSA-3A', 3);

        $this->actingAs($chair)->post(route('chair.sections.store'), ['program' => 'BSA', 'year_level' => '3', 'semester' => '1', 'section_number' => '02'])
            ->assertSessionHasNoErrors();
        $this->actingAs($chair)->put(route('chair.sections.update', $id), ['program' => 'BSA', 'year_level' => '3', 'semester' => '1', 'section_number' => '01'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sections', ['name' => 'BSA 3102']);
        $this->assertDatabaseHas('sections', ['id' => $id, 'name' => 'BSA 3101']);

        foreach (['0', '00', '100', '1a', ' '] as $bad) {
            $this->actingAs($chair)->post(route('chair.sections.store'), ['program' => 'BSA', 'year_level' => '3', 'semester' => '1', 'section_number' => $bad])
                ->assertSessionHasErrors('section_number');
        }
    }

    // ── Scenario 5: duplicate ────────────────────────────────────────────────

    public function test_a_duplicate_section_name_is_rejected(): void
    {
        $chair = $this->chair();
        $this->actingAs($chair)->post(route('chair.sections.store'), $this->parts('BSA', 3, 1, 1))->assertSessionHasNoErrors();

        $this->actingAs($chair)->post(route('chair.sections.store'), $this->parts('BSA', 3, 1, 1))
            ->assertSessionHasErrors(['name' => 'A section named "BSA 3101" already exists.']);

        $this->assertSame(1, DB::table('sections')->where('name', 'BSA 3101')->count());
    }

    public function test_each_part_is_required_and_validated(): void
    {
        $chair = $this->chair();

        $this->actingAs($chair)->post(route('chair.sections.store'), [])
            ->assertSessionHasErrors(['program', 'year_level', 'semester', 'section_number']);

        $this->actingAs($chair)->post(route('chair.sections.store'), [
            'program' => 'BS1', 'year_level' => 5, 'semester' => 3, 'section_number' => 'A1',
        ])->assertSessionHasErrors(['program', 'year_level', 'semester', 'section_number']);

        $this->actingAs($chair)->post(route('chair.sections.store'), $this->parts('BSA', 3, 1, 0))
            ->assertSessionHasErrors('section_number');

        // A full name typed by hand is no longer how sections are made.
        $this->actingAs($chair)->post(route('chair.sections.store'), ['name' => 'BSA-3C', 'year_level' => 3])
            ->assertSessionHasErrors(['program', 'semester', 'section_number']);

        $this->assertSame(0, DB::table('sections')->count());
    }

    // ── Scenario 6: edit ─────────────────────────────────────────────────────

    public function test_editing_a_new_format_section(): void
    {
        $chair = $this->chair();
        $id = $this->section('BSA 3101', 3);
        $student = $this->student('a@t.test', 'BSA 3101', 3);

        // Saving unchanged details is not a "duplicate of itself".
        $this->actingAs($chair)->put(route('chair.sections.update', $id), $this->parts('BSA', 3, 1, 1))
            ->assertSessionHasNoErrors();

        $this->actingAs($chair)->put(route('chair.sections.update', $id), $this->parts('BSA', 3, 1, 3))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sections', ['id' => $id, 'name' => 'BSA 3103']);
        $this->assertSame('BSA 3103', $this->sectionOf($student));
    }

    public function test_renaming_onto_an_existing_name_is_rejected_and_changes_nothing(): void
    {
        $chair = $this->chair();
        $this->section('BSA 3101', 3);
        $id = $this->section('BSA 3102', 3);
        $student = $this->student('a@t.test', 'BSA 3102', 3);

        $this->actingAs($chair)->put(route('chair.sections.update', $id), $this->parts('BSA', 3, 1, 1))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('sections', ['id' => $id, 'name' => 'BSA 3102']);
        $this->assertSame('BSA 3102', $this->sectionOf($student));
    }

    // ── Scenarios 7–8: rename an old-format section ──────────────────────────

    public function test_renaming_an_old_section_keeps_its_students_and_mock_exam_audiences(): void
    {
        $chair = $this->chair();
        $id = $this->section('BSA-3A', 3);
        $this->section('BSA-4A', 4);
        $students = [
            $this->student('a@t.test', 'BSA-3A', 3),
            $this->student('b@t.test', 'BSA-3A', 3),
            $this->student('c@t.test', ' bsa-3a', 3),                  // legacy spacing/case
            $this->student('d@t.test', 'BSA-3A', 3, ['is_alumni' => true]),
        ];
        $other = $this->student('e@t.test', 'BSA-4A', 4);
        $examId = DB::table('mock_exams')->insertGetId(['title' => 'Prelim', 'audience_sections' => json_encode(['BSA-3A', 'BSA-4A'])]);

        $this->actingAs($chair)->put(route('chair.sections.update', $id), $this->parts('BSA', 3, 1, 1))
            ->assertRedirect()->assertSessionHasNoErrors();

        // Same row renamed, not deleted and re-created.
        $this->assertDatabaseHas('sections', ['id' => $id, 'name' => 'BSA 3101', 'year_level' => 3]);
        $this->assertSame(2, DB::table('sections')->count());

        foreach ($students as $student) {
            $this->assertSame('BSA 3101', $this->sectionOf($student));
        }
        $this->assertSame('BSA-4A', $this->sectionOf($other));
        $this->assertSame(['BSA 3101', 'BSA-4A'], json_decode(DB::table('mock_exams')->where('id', $examId)->value('audience_sections'), true));
    }

    public function test_a_failed_rename_rolls_back_the_section_and_its_students(): void
    {
        $chair = $this->chair();
        $id = $this->section('BSA-3A', 3);
        $student = $this->student('a@t.test', 'BSA-3A', 3);
        Schema::drop('mock_exams'); // makes the last step of the rename fail

        $this->actingAs($chair)->put(route('chair.sections.update', $id), $this->parts('BSA', 3, 1, 1))
            ->assertStatus(500);

        $this->assertDatabaseHas('sections', ['id' => $id, 'name' => 'BSA-3A']);
        $this->assertSame('BSA-3A', $this->sectionOf($student));
    }

    public function test_moving_a_section_to_another_year_moves_its_current_students_year(): void
    {
        $chair = $this->chair();
        $id = $this->section('BSA 3101', 3);
        $current = $this->student('a@t.test', 'BSA 3101', 3);
        $alumnus = $this->student('b@t.test', 'BSA 3101', 3, ['is_alumni' => true]);

        $this->actingAs($chair)->put(route('chair.sections.update', $id), $this->parts('BSA', 4, 1, 1))
            ->assertSessionHasNoErrors();

        $this->assertSame('BSA 4101', $this->sectionOf($current));
        $this->assertSame(4, (int) DB::table('student_profiles')->where('user_id', $current->id)->value('year_level'));
        $this->assertSame(3, (int) DB::table('student_profiles')->where('user_id', $alumnus->id)->value('year_level'));
    }

    // ── Scenario 9: old names still work ─────────────────────────────────────

    public function test_old_format_sections_are_untouched_until_edited(): void
    {
        $chair = $this->chair();
        $old = $this->section('BSA-3A', 3);
        $this->section('BSA 3101', 3);

        // Creating a new-format section leaves old ones alone.
        $this->actingAs($chair)->post(route('chair.sections.store'), $this->parts('BSA', 3, 1, 2))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sections', ['id' => $old, 'name' => 'BSA-3A']);

        // Students can still be placed in an old-format section.
        $student = $this->student('a@t.test', null, null);
        $this->actingAs($chair)->post(route('chair.students.assign-section'), ['section_id' => $old, 'student_ids' => [$student->id]])
            ->assertSessionHas('status');
        $this->assertSame('BSA-3A', $this->sectionOf($student));
    }

    private function parts(string $program, int $year, int $sem, int $no): array
    {
        return ['program' => $program, 'year_level' => $year, 'semester' => $sem, 'section_number' => $no];
    }

    private function section(string $name, ?int $year): int
    {
        return DB::table('sections')->insertGetId(['name' => $name, 'year_level' => $year, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function sectionOf(User $student): ?string
    {
        return DB::table('student_profiles')->where('user_id', $student->id)->value('section');
    }

    private function chair(): User
    {
        return $this->user('chair@t.test', Role::ADMIN);
    }

    private function student(string $email, ?string $section, ?int $year, array $profile = []): User
    {
        $u = $this->user($email, Role::STUDENT);
        DB::table('student_profiles')->insert(array_merge(['user_id' => $u->id, 'section' => $section, 'year_level' => $year], $profile));

        return $u;
    }

    private function user(string $email, int $roleId): User
    {
        return User::create([
            'role_id' => $roleId, 'first_name' => 'Test', 'last_name' => ucfirst(strtok($email, '@')),
            'email' => $email, 'password' => Hash::make('password'), 'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
