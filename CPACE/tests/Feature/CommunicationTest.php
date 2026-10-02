<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
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

        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id')->index();
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('category', 20)->default('other');
            $table->timestamps();
        });
        // The Messages inbox reads each user's role.
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

        // Creating a student fires User::booted(), which drops them into the
        // default community group, and the global view composer counts unread
        // messages — so the chat tables have to exist here too.
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
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('faculty_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('communication_attachments');
        Schema::dropIfExists('communications');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_chair_can_send_an_announcement_only_to_active_students(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $activeStudent = $this->user(Role::STUDENT, 'student@example.com');
        $this->user(Role::STUDENT, 'inactive@example.com', false);
        $this->user(Role::FACULTY, 'faculty@example.com');

        $response = $this->actingAs($chair)->post(route('chair.communications.store'), [
            'audience' => 'students',
            'target_type' => 'all',
            'title' => 'Schedule Change',
            'message' => 'The mock exam has moved to Friday.',
            'type' => 'schedule_change',
            'priority' => 'high',
            'link' => '/calendar',
        ]);

        $response->assertRedirect(route('messages.index', ['view' => 'announcements']));
        $this->assertDatabaseHas('communications', ['sender_id' => $chair->id, 'recipient_count' => 1]);
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $activeStudent->id,
            'sender_id' => $chair->id,
            'title' => 'Schedule Change',
            'is_read' => false,
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_announcements_live_inside_messages_for_the_chair(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $this->user(Role::STUDENT, 'student@example.com');
        $this->user(Role::FACULTY, 'faculty@example.com');

        $this->actingAs($chair)->get(route('messages.index', ['view' => 'announcements']))
            ->assertOk()
            // Chats | Announcements switch, the main action, and the audience filter
            ->assertSee('Chats')->assertSee('Announcements')
            ->assertSee('Make an announcement')->assertSee('id="announceModal"', false)
            ->assertSee('Students')->assertSee('Faculty')
            ->assertSee('No announcements yet')
            // the form still posts to the same endpoint with the same fields
            ->assertSee(route('chair.communications.store'), false)
            ->assertSee('name="target_type"', false)
            ->assertSee('enctype="multipart/form-data"', false)->assertSee('name="attachments[]"', false)
            // priority is no longer asked for
            ->assertDontSee('name="priority"', false);

        // The plain Messages page for the chair is still the chat inbox.
        $this->actingAs($chair)->get(route('messages.index'))
            ->assertOk()->assertSee('Announcements')->assertDontSee('id="announceModal"', false);
    }

    public function test_announcement_cards_are_filtered_by_audience(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        \App\Models\Communication::create([
            'sender_id' => $chair->id, 'audience' => 'students', 'target_type' => 'all', 'target_filters' => [],
            'title' => 'Students: finals schedule', 'message' => 'Finals start Monday.', 'type' => 'announcement',
            'priority' => 'urgent', 'recipient_count' => 4,
        ]);
        \App\Models\Communication::create([
            'sender_id' => $chair->id, 'audience' => 'faculty', 'target_type' => 'group', 'target_filters' => ['subject_id' => 1],
            'title' => 'Faculty: submit grades', 'message' => 'Grades are due Friday.', 'type' => 'reminder',
            'priority' => 'normal', 'recipient_count' => 2,
        ]);

        $all = $this->actingAs($chair)->get(route('messages.index', ['view' => 'announcements']));
        $all->assertOk()->assertSee('Students: finals schedule')->assertSee('Faculty: submit grades')
            // a list of rows that open the announcement, not cards
            ->assertSee('class="an-row"', false)->assertDontSee('class="an-card"', false)
            ->assertSee('<b>0</b> of 4 seen', false)->assertSee('<b>0</b> of 2 seen', false);

        $this->actingAs($chair)->get(route('messages.index', ['view' => 'announcements', 'to' => 'students']))
            ->assertOk()->assertSee('Students: finals schedule')->assertDontSee('Faculty: submit grades');

        $this->actingAs($chair)->get(route('messages.index', ['view' => 'announcements', 'to' => 'faculty']))
            ->assertOk()->assertSee('Faculty: submit grades')->assertDontSee('Students: finals schedule');
    }

    public function test_the_old_communications_page_redirects_into_messages(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');

        $this->actingAs($chair)->get(route('chair.communications'))
            ->assertRedirect(route('messages.index', ['view' => 'announcements']));
        $this->actingAs($chair)->get(route('chair.communications', ['tab' => 'faculty']))
            ->assertRedirect(route('messages.index', ['view' => 'announcements', 'to' => 'faculty']));
        $this->actingAs($chair)->get(route('chair.communications', ['tab' => 'history']))
            ->assertRedirect(route('messages.index', ['view' => 'announcements']));
    }

    public function test_only_the_chair_gets_the_announcements_view(): void
    {
        $faculty = $this->user(Role::FACULTY, 'faculty@example.com');
        $student = $this->user(Role::STUDENT, 'student@example.com');

        foreach ([$faculty, $student] as $user) {
            $this->actingAs($user)->get(route('messages.index', ['view' => 'announcements']))
                ->assertOk()->assertDontSee('id="announceModal"', false)->assertDontSee('class="msg-switch"', false);
        }
        $this->actingAs($faculty)->get(route('chair.communications'))->assertForbidden();
    }

    public function test_chair_can_target_faculty_by_assigned_subject(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $assignedFaculty = $this->user(Role::FACULTY, 'assigned@example.com');
        $this->user(Role::FACULTY, 'other@example.com');
        $subjectId = DB::table('subjects')->insertGetId([
            'code' => 'AUD',
            'name' => 'Auditing',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('faculty_subjects')->insert([
            'faculty_id' => $assignedFaculty->id,
            'subject_id' => $subjectId,
            'assigned_by' => $chair->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($chair)->post(route('chair.communications.store'), [
            'audience' => 'faculty',
            'target_type' => 'group',
            'subject_id' => $subjectId,
            'title' => 'Auditing Faculty Meeting',
            'message' => 'Please attend the faculty meeting at 3 PM.',
            'type' => 'announcement',
            'priority' => 'normal',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', ['recipient_id' => $assignedFaculty->id]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_non_chair_cannot_send_communications(): void
    {
        $student = $this->user(Role::STUDENT, 'student@example.com');

        $this->actingAs($student)->post(route('chair.communications.store'), [
            'audience' => 'students',
            'target_type' => 'all',
            'title' => 'Unauthorized',
            'message' => 'This must not be sent.',
            'type' => 'announcement',
            'priority' => 'normal',
        ])->assertForbidden();

        $this->assertDatabaseCount('communications', 0);
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $first = $this->user(Role::STUDENT, 'first@example.com');
        $second = $this->user(Role::STUDENT, 'second@example.com');
        $id = DB::table('notifications')->insertGetId([
            'recipient_id' => $second->id,
            'type' => 'normal',
            'title' => 'Private update',
            'message' => 'For the second student only.',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($first)->post(route('notifications.read', $id))->assertNotFound();
        $this->assertDatabaseHas('notifications', ['id' => $id, 'is_read' => false]);
    }

    public function test_sending_without_a_priority_defaults_to_normal(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $student = $this->user(Role::STUDENT, 'student@example.com');

        $this->actingAs($chair)->post(route('chair.communications.store'), [
            'audience' => 'students', 'target_type' => 'all', 'title' => 'No priority', 'message' => 'Body', 'type' => 'announcement',
        ])->assertRedirect(route('messages.index', ['view' => 'announcements']))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('communications', ['title' => 'No priority', 'priority' => 'normal']);
        $this->assertDatabaseHas('notifications', ['recipient_id' => $student->id, 'type' => 'normal']);
    }

    public function test_an_announcement_can_carry_files_and_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $this->user(Role::STUDENT, 'student@example.com');

        $this->actingAs($chair)->post(route('chair.communications.store'), [
            'audience' => 'students', 'target_type' => 'all', 'title' => 'With files', 'message' => 'See attached.', 'type' => 'announcement',
            'attachments' => [
                \Illuminate\Http\UploadedFile::fake()->create('schedule.pdf', 120, 'application/pdf'),
                \Illuminate\Http\UploadedFile::fake()->image('poster.png'),
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $communication = \App\Models\Communication::where('title', 'With files')->firstOrFail();
        $files = $communication->attachments()->orderBy('id')->get();
        $this->assertSame(['schedule.pdf', 'poster.png'], $files->pluck('original_name')->all());
        $this->assertSame(['pdf', 'image'], $files->pluck('category')->all());
        foreach ($files as $file) {
            \Illuminate\Support\Facades\Storage::disk('public')->assertExists($file->path);
        }

        // The board marks the announcement as having files.
        $this->actingAs($chair)->get(route('messages.index', ['view' => 'announcements']))
            ->assertOk()->assertSee('2 attached files');
    }

    public function test_attachments_are_limited_in_type_count_and_size(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $this->user(Role::STUDENT, 'student@example.com');
        $base = ['audience' => 'students', 'target_type' => 'all', 'title' => 'T', 'message' => 'M', 'type' => 'announcement'];

        $this->actingAs($chair)->post(route('chair.communications.store'), $base + ['attachments' => [
            \Illuminate\Http\UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ]])->assertSessionHasErrors('attachments.0');

        $this->actingAs($chair)->post(route('chair.communications.store'), $base + ['attachments' => [
            \Illuminate\Http\UploadedFile::fake()->create('huge.pdf', 11 * 1024, 'application/pdf'),
        ]])->assertSessionHasErrors('attachments.0');

        $six = array_map(fn ($i) => \Illuminate\Http\UploadedFile::fake()->create("f{$i}.pdf", 10, 'application/pdf'), range(1, 6));
        $this->actingAs($chair)->post(route('chair.communications.store'), $base + ['attachments' => $six])
            ->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('communications', 0);
    }

    public function test_only_the_sender_and_the_recipients_can_open_an_attachment(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $recipient = $this->user(Role::STUDENT, 'student@example.com');
        $outsider = $this->user(Role::STUDENT, 'outsider@example.com', true);

        // Only the first student is picked, so the second never gets the notification.
        $this->actingAs($chair)->post(route('chair.communications.store'), [
            'audience' => 'students', 'target_type' => 'selected', 'recipient_ids' => [$recipient->id],
            'title' => 'Files', 'message' => 'Body', 'type' => 'announcement',
            'attachments' => [\Illuminate\Http\UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf')],
        ])->assertSessionHasNoErrors();
        $attachment = \App\Models\CommunicationAttachment::firstOrFail();

        $this->actingAs($chair)->get(route('communications.attachments.download', $attachment->id))->assertOk();
        $this->actingAs($recipient)->get(route('communications.attachments.download', $attachment->id))->assertOk();
        $this->actingAs($outsider)->get(route('communications.attachments.download', $attachment->id))->assertForbidden();
    }

    public function test_an_announcement_opens_with_who_has_seen_it(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $reader = $this->user(Role::STUDENT, 'reader@example.com');
        $other = $this->user(Role::STUDENT, 'other@example.com');

        $this->actingAs($chair)->post(route('chair.communications.store'), [
            'audience' => 'students', 'target_type' => 'all', 'title' => 'Read me', 'message' => "Line one\nLine two", 'type' => 'reminder',
        ])->assertSessionHasNoErrors();
        $communication = \App\Models\Communication::firstOrFail();

        // One of the two opens it (marks the notification read).
        $note = DB::table('notifications')->where('recipient_id', $reader->id)->first();
        $this->actingAs($reader)->post(route('notifications.read', $note->id));

        $json = $this->actingAs($chair)->getJson(route('chair.communications.show', $communication->id))
            ->assertOk()->json();

        $this->assertSame('Read me', $json['title']);
        $this->assertSame("Line one\nLine two", $json['message']);
        $this->assertSame('Everyone', $json['to']);
        $this->assertSame(2, $json['recipient_count']);
        $this->assertSame(1, $json['seen_count']);
        $byName = collect($json['recipients'])->keyBy('name');
        $this->assertTrue($byName['Test Reader']['seen']);
        $this->assertNotNull($byName['Test Reader']['seen_at']);
        $this->assertFalse($byName['Test Other']['seen']);
        $this->assertNull($byName['Test Other']['seen_at']);
    }

    public function test_only_the_sender_can_open_an_announcements_details(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $otherChair = $this->user(Role::ADMIN, 'chair2@example.com');
        $student = $this->user(Role::STUDENT, 'student@example.com');
        $communication = \App\Models\Communication::create([
            'sender_id' => $chair->id, 'audience' => 'students', 'target_type' => 'all', 'target_filters' => [],
            'title' => 'Mine', 'message' => 'x', 'type' => 'announcement', 'priority' => 'normal', 'recipient_count' => 1,
        ]);

        $this->actingAs($chair)->getJson(route('chair.communications.show', $communication->id))->assertOk();
        $this->actingAs($otherChair)->getJson(route('chair.communications.show', $communication->id))->assertForbidden();
        $this->actingAs($student)->getJson(route('chair.communications.show', $communication->id))->assertForbidden();
    }

    private function user(int $roleId, string $email, bool $active = true): User
    {
        // setup_completed_at is set so EnsureAccountSetup does not divert these
        // requests into first-login onboarding before role checks are reached.
        return User::create([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => ucfirst(strtok($email, '@')),
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => $active,
            'setup_completed_at' => now(),
        ]);
    }
}
