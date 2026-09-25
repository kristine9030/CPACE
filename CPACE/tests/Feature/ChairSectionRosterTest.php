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
 * Chair > Sections: clicking a section lists its students and lets the chair
 * add enrolled students to it. Hand-built schema, same convention as the other
 * Feature tests in this suite.
 */
class ChairSectionRosterTest extends TestCase
{
    private const TABLES = [
        'student_profiles', 'sections', 'notifications', 'messages',
        'conversation_participants', 'conversations', 'users',
    ];

    private int $sectionA;
    private int $sectionB;

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
            $table->string('student_number', 30)->nullable();
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->string('section', 30)->nullable();
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
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

        $this->sectionA = DB::table('sections')->insertGetId(['name' => 'BSA-3A', 'year_level' => 3, 'created_at' => now(), 'updated_at' => now()]);
        $this->sectionB = DB::table('sections')->insertGetId(['name' => 'BSA-4A', 'year_level' => 4, 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_roster_splits_members_from_students_who_can_be_added(): void
    {
        $member = $this->student('m@t.test', 'BSA-3A');
        $elsewhere = $this->student('e@t.test', 'BSA-4A');
        $none = $this->student('n@t.test', null);

        $json = $this->actingAs($this->chair())
            ->getJson(route('chair.sections.students', $this->sectionA))
            ->assertOk()->json();

        $this->assertSame([$member->id], array_column($json['members'], 'id'));
        $this->assertEqualsCanonicalizing([$elsewhere->id, $none->id], array_column($json['available'], 'id'));
    }

    public function test_alumni_shifted_and_inactive_students_are_not_listed(): void
    {
        $this->student('a@t.test', null, ['is_alumni' => true]);
        $this->student('s@t.test', null, ['is_shifted' => true]);
        $this->student('i@t.test', null, [], ['is_active' => false]);

        $json = $this->actingAs($this->chair())
            ->getJson(route('chair.sections.students', $this->sectionA))->json();

        $this->assertSame([], $json['available']);
    }

    public function test_the_chair_can_add_checked_students_and_they_take_the_section_and_year(): void
    {
        $one = $this->student('1@t.test', null);
        $two = $this->student('2@t.test', 'BSA-4A');
        $left = $this->student('3@t.test', null);

        $this->actingAs($this->chair())
            ->postJson(route('chair.sections.students.add', $this->sectionA), ['student_ids' => [$one->id, $two->id]])
            ->assertOk()->assertJson(['added' => 2]);

        $this->assertSame('BSA-3A', DB::table('student_profiles')->where('user_id', $one->id)->value('section'));
        $this->assertSame('BSA-3A', DB::table('student_profiles')->where('user_id', $two->id)->value('section'));
        $this->assertSame(3, (int) DB::table('student_profiles')->where('user_id', $two->id)->value('year_level'));
        $this->assertNull(DB::table('student_profiles')->where('user_id', $left->id)->value('section'));
    }

    public function test_non_students_and_alumni_cannot_be_sectioned_by_posting_their_ids(): void
    {
        $alumnus = $this->student('al@t.test', null, ['is_alumni' => true]);

        $this->actingAs($this->chair())
            ->postJson(route('chair.sections.students.add', $this->sectionA), ['student_ids' => [$alumnus->id]])
            ->assertOk()->assertJson(['added' => 0]);

        $this->assertNull(DB::table('student_profiles')->where('user_id', $alumnus->id)->value('section'));
    }

    public function test_adding_requires_at_least_one_student(): void
    {
        $this->actingAs($this->chair())
            ->postJson(route('chair.sections.students.add', $this->sectionA), ['student_ids' => []])
            ->assertStatus(422);
    }

    public function test_students_cannot_use_the_roster_endpoints(): void
    {
        $student = $this->student('s2@t.test', null);

        $this->actingAs($student)->getJson(route('chair.sections.students', $this->sectionA))->assertForbidden();
        $this->actingAs($student)
            ->postJson(route('chair.sections.students.add', $this->sectionA), ['student_ids' => [$student->id]])
            ->assertForbidden();
    }

    private function chair(): User
    {
        return $this->user('chair@t.test', Role::ADMIN);
    }

    private function student(string $email, ?string $section, array $profile = [], array $user = []): User
    {
        $u = $this->user($email, Role::STUDENT, $user);
        DB::table('student_profiles')->insert(array_merge(['user_id' => $u->id, 'section' => $section], $profile));

        return $u;
    }

    private function user(string $email, int $roleId, array $extra = []): User
    {
        return User::create(array_merge([
            'role_id' => $roleId, 'first_name' => 'Test', 'last_name' => ucfirst(strtok($email, '@')),
            'email' => $email, 'password' => Hash::make('password'), 'is_active' => true,
            'setup_completed_at' => now(),
        ], $extra));
    }
}
