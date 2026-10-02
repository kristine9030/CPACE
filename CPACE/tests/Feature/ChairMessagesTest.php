<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Program Chair's Messages: chat with faculty and students the way a
 * student does, with every person classified by role (Faculty, Student,
 * Alumni, Staff). Hand-built schema, same convention as the other Feature tests.
 */
class ChairMessagesTest extends TestCase
{
    private const TABLES = [
        'messages', 'conversation_participants', 'conversations', 'faculty_subjects', 'subjects',
        'student_profiles', 'notifications', 'communication_attachments', 'communications', 'roles', 'users',
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
            $table->string('avatar_color')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        foreach ([1 => 'admin', 2 => 'student', 3 => 'faculty', 4 => 'alumni', 5 => 'super_admin'] as $id => $name) {
            DB::table('roles')->insert(['id' => $id, 'name' => $name]);
        }
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('audience');
            $table->string('target_type');
            $table->json('target_filters')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('type');
            $table->string('priority');
            $table->string('link')->nullable();
            $table->unsignedInteger('recipient_count');
            $table->timestamps();
        });
        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id')->index();
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('category', 20)->default('other');
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->string('section')->nullable();
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id')->nullable();
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
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
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_category')->nullable();
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

    public function test_the_new_message_list_sorts_people_by_who_they_are(): void
    {
        $chair = $this->user(Role::ADMIN, 'Pia', 'Chair');
        $faculty = $this->user(Role::FACULTY, 'Felix', 'Faculty');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        $student = $this->user(Role::STUDENT, 'Sara', 'Student');
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'year_level' => 3, 'section' => 'BSA 3101']);
        $this->user(Role::ALUMNI, 'Alma', 'Alumna');
        $this->user(Role::STUDENT, 'Inactive', 'Student', false);

        $html = $this->actingAs($chair)->get(route('messages.index'))->assertOk()->getContent();

        // tabs with counts, then each person under their role heading
        $this->assertStringContainsString('data-tab="faculty"', $html);
        $this->assertStringContainsString('data-tab="student"', $html);
        $this->assertStringContainsString('data-tab="alumni"', $html);
        $this->assertStringNotContainsString('data-tab="staff"', $html, 'no other staff exist, so no empty tab');
        $this->assertMatchesRegularExpression('/class="pk-item"[^>]*data-role="faculty"[^>]*data-name="felix faculty/', $html);
        $this->assertMatchesRegularExpression('/class="pk-item"[^>]*data-role="student"[^>]*data-name="sara student/', $html);
        $this->assertStringContainsString('FAR', $html);                 // faculty show their subjects
        $this->assertStringContainsString('Year 3 · BSA 3101', $html);   // students show year and section
        $this->assertStringNotContainsString('inactive student', strtolower($html));
        $this->assertStringNotContainsString('data-name="pia chair', $html, 'you are not listed to yourself');

        // faculty come before students, students before alumni
        $this->assertLessThan(strpos($html, 'data-name="sara student'), strpos($html, 'data-name="felix faculty'));
        $this->assertLessThan(strpos($html, 'data-name="alma alumna'), strpos($html, 'data-name="sara student'));
    }

    public function test_the_chair_can_message_faculty_and_they_receive_it(): void
    {
        $chair = $this->user(Role::ADMIN, 'Pia', 'Chair');
        $faculty = $this->user(Role::FACULTY, 'Felix', 'Faculty');

        // start the chat
        $response = $this->actingAs($chair)->post(route('messages.start'), ['user_id' => $faculty->id]);
        $conversation = Conversation::where('type', 'direct')->firstOrFail();
        $response->assertRedirect(route('messages.show', $conversation));
        $this->assertEqualsCanonicalizing([$chair->id, $faculty->id], $conversation->participants()->pluck('users.id')->all());

        // starting it again resumes the same chat
        $this->actingAs($chair)->post(route('messages.start'), ['user_id' => $faculty->id]);
        $this->assertSame(1, Conversation::where('type', 'direct')->count());

        // send
        $this->actingAs($chair)->postJson(route('messages.send', $conversation), ['body' => 'Please send your syllabus.'])
            ->assertOk()->assertJsonPath('message.body', 'Please send your syllabus.')->assertJsonPath('message.is_mine', true);

        // the faculty member sees it: chat list row tagged with who it is, and the thread
        $this->actingAs($faculty)->get(route('messages.index'))
            ->assertOk()->assertSee('Please send your syllabus.')
            ->assertSee('data-kind="staff"', false)->assertSee('Program Chair');
        $this->actingAs($faculty)->getJson(route('messages.poll', $conversation) . '?after_id=0')
            ->assertOk()->assertJsonPath('messages.0.sender_name', 'Pia Chair')->assertJsonPath('messages.0.is_mine', false);

        // and replies
        $this->actingAs($faculty)->postJson(route('messages.send', $conversation), ['body' => 'On it, Chair.'])->assertOk();
        $this->actingAs($chair)->get(route('messages.show', $conversation))->assertOk()->assertSee('On it, Chair.');
    }

    public function test_the_chairs_chat_list_marks_faculty_students_and_groups(): void
    {
        $chair = $this->user(Role::ADMIN, 'Pia', 'Chair');
        $faculty = $this->user(Role::FACULTY, 'Felix', 'Faculty');
        $student = $this->user(Role::STUDENT, 'Sara', 'Student');
        foreach ([$faculty, $student] as $person) {
            $this->actingAs($chair)->post(route('messages.start'), ['user_id' => $person->id]);
            $conversation = Conversation::findDirectBetween($chair->id, $person->id);
            $this->actingAs($chair)->postJson(route('messages.send', $conversation), ['body' => 'Hello ' . $person->first_name]);
        }
        $this->actingAs($chair)->post(route('messages.group.create'), ['name' => 'Review Team', 'member_ids' => [$faculty->id, $student->id]]);

        $html = $this->actingAs($chair)->get(route('messages.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-kind="faculty"', $html);
        $this->assertStringContainsString('data-kind="student"', $html);
        $this->assertStringContainsString('data-kind="group"', $html);
        // the filter pills for these exist for the chair
        foreach (['all', 'faculty', 'student', 'group'] as $kind) {
            $this->assertStringContainsString('class="cl-pill' . ($kind === 'all' ? ' on' : '') . '" data-kind="' . $kind . '"', $html);
        }
    }

    public function test_the_chair_can_start_a_group_with_faculty_and_students_together(): void
    {
        $chair = $this->user(Role::ADMIN, 'Pia', 'Chair');
        $faculty = $this->user(Role::FACULTY, 'Felix', 'Faculty');
        $student = $this->user(Role::STUDENT, 'Sara', 'Student');

        // faculty are offered in the group picker (they weren't before)
        $this->actingAs($chair)->get(route('messages.index'))->assertOk()
            ->assertSee('id="groupPicker"', false)
            ->assertSeeInOrder(['id="groupPicker"', 'data-role="faculty"', 'felix faculty'], false);

        $this->actingAs($chair)->post(route('messages.group.create'), ['name' => 'CPALE Prep', 'member_ids' => [$faculty->id, $student->id]])
            ->assertRedirect()->assertSessionHas('status', 'Group chat created.');

        $group = Conversation::where('name', 'CPALE Prep')->firstOrFail();
        $this->assertEqualsCanonicalizing([$chair->id, $faculty->id, $student->id], $group->participants()->pluck('users.id')->all());

        // a member can chat in it; an outsider cannot read it
        $this->actingAs($faculty)->postJson(route('messages.send', $group), ['body' => 'Hi all'])->assertOk();
        $outsider = $this->user(Role::FACULTY, 'Olive', 'Outsider');
        $this->actingAs($outsider)->get(route('messages.show', $group))->assertForbidden();
    }

    public function test_students_do_not_get_the_chairs_filters_or_announcements(): void
    {
        $this->user(Role::ADMIN, 'Pia', 'Chair');
        $this->user(Role::FACULTY, 'Felix', 'Faculty');
        $student = $this->user(Role::STUDENT, 'Sara', 'Student');

        $this->actingAs($student)->get(route('messages.index'))
            ->assertOk()
            // the shared picker still tells a student who is faculty and who isn't
            ->assertSee('data-tab="faculty"', false)
            ->assertDontSee('id="clPills"', false)
            ->assertDontSee('class="msg-switch"', false);
    }

    private function user(int $roleId, string $first, string $last, bool $active = true): User
    {
        return User::create([
            'role_id' => $roleId, 'first_name' => $first, 'last_name' => $last,
            'email' => strtolower("{$first}.{$last}@example.com"), 'password' => Hash::make('password'),
            'is_active' => $active, 'setup_completed_at' => now(),
        ]);
    }
}
