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
 * Hand-built schema matching the real production `notifications` table
 * (migration files drift from it - see other Feature tests for the same
 * rationale). RefreshDatabase is not used.
 */
class NotificationTest extends TestCase
{
    private const TABLES = ['notifications', 'messages', 'conversation_participants', 'conversations', 'users'];

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
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        // The shared topbar layout queries unread messages on every
        // authenticated page load, so these have to exist too.
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
            $table->unsignedBigInteger('communication_id')->nullable();
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type', 50);
            $table->string('title', 150);
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
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

    public function test_a_user_only_sees_their_own_notifications(): void
    {
        $student = $this->user('me@example.com');
        $other = $this->user('other@example.com');
        $this->notify($student->id, 'Mine');
        $this->notify($other->id, 'Not mine');

        $response = $this->actingAs($student)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Mine');
        $response->assertDontSee('Not mine');
    }

    public function test_marking_a_notification_read_requires_it_to_belong_to_the_current_user(): void
    {
        $student = $this->user('me@example.com');
        $other = $this->user('other@example.com');
        $notification = $this->notify($other->id, 'Not mine');

        $this->actingAs($student)->post(route('notifications.read', $notification))->assertNotFound();

        $this->assertFalse((bool) DB::table('notifications')->find($notification)->is_read);
    }

    public function test_marking_a_notification_read_updates_only_that_notification(): void
    {
        $student = $this->user('me@example.com');
        $unread = $this->notify($student->id, 'One');
        $other = $this->notify($student->id, 'Two');

        $this->actingAs($student)->post(route('notifications.read', $unread))->assertRedirect();

        $this->assertTrue((bool) DB::table('notifications')->find($unread)->is_read);
        $this->assertFalse((bool) DB::table('notifications')->find($other)->is_read);
    }

    public function test_read_all_marks_every_unread_notification_for_the_current_user_only(): void
    {
        $student = $this->user('me@example.com');
        $other = $this->user('other@example.com');
        $mine1 = $this->notify($student->id, 'One');
        $mine2 = $this->notify($student->id, 'Two');
        $theirs = $this->notify($other->id, 'Theirs');

        $this->actingAs($student)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertTrue((bool) DB::table('notifications')->find($mine1)->is_read);
        $this->assertTrue((bool) DB::table('notifications')->find($mine2)->is_read);
        $this->assertFalse((bool) DB::table('notifications')->find($theirs)->is_read);
    }

    private function notify(int $recipientId, string $title): int
    {
        return DB::table('notifications')->insertGetId([
            'recipient_id' => $recipientId,
            'type' => 'normal',
            'title' => $title,
            'message' => $title,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function user(string $email): User
    {
        // ADMIN (chair), not STUDENT, so User::booted() doesn't need the
        // community/conversation tables this test's schema doesn't create.
        return User::create([
            'role_id' => Role::ADMIN,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
