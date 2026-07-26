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
        Schema::dropIfExists('communications');
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

        $response->assertRedirect(route('chair.communications', ['tab' => 'history']));
        $this->assertDatabaseHas('communications', ['sender_id' => $chair->id, 'recipient_count' => 1]);
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $activeStudent->id,
            'sender_id' => $chair->id,
            'title' => 'Schedule Change',
            'is_read' => false,
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_chair_can_open_the_communications_interface(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');

        $this->actingAs($chair)->get(route('chair.communications'))
            ->assertOk()
            ->assertSee('Student Announcements')
            ->assertSee('Faculty Messages')
            ->assertSee('Sent History');
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
