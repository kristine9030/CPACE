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
 * Covers ChatController: sending, DM start/resume, group creation/rename/
 * membership/leave, and the participant-only access gate. Hand-built schema,
 * same rationale as the other Feature tests in this suite (migration set
 * doesn't run cleanly from scratch).
 */
class MessagingTest extends TestCase
{
    private const TABLES = ['notifications', 'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users'];

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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('direct');
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('is_alumni')->default(false);
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_category', 20)->nullable();
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

    public function test_a_non_participant_cannot_open_a_conversation(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $outsider = $this->user('outsider@example.com');
        $conversationId = $this->directConversation($a->id, $b->id);

        $this->actingAs($outsider)->get(route('messages.show', $conversationId))->assertForbidden();
    }

    public function test_a_participant_can_send_a_message(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $conversationId = $this->directConversation($a->id, $b->id);

        $this->actingAs($a)->post(route('messages.send', $conversationId), [
            'body' => 'Hello there',
        ])->assertRedirect();

        $this->assertSame(1, DB::table('messages')->where('conversation_id', $conversationId)->where('sender_id', $a->id)->count());
    }

    public function test_a_non_participant_cannot_send_a_message(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $outsider = $this->user('outsider@example.com');
        $conversationId = $this->directConversation($a->id, $b->id);

        $this->actingAs($outsider)->post(route('messages.send', $conversationId), ['body' => 'sneaky'])
            ->assertForbidden();

        $this->assertSame(0, DB::table('messages')->where('conversation_id', $conversationId)->count());
    }

    public function test_a_message_needs_either_text_or_a_file(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $conversationId = $this->directConversation($a->id, $b->id);

        $this->actingAs($a)->post(route('messages.send', $conversationId), [])
            ->assertStatus(422);
    }

    public function test_polling_only_returns_messages_newer_than_the_given_id(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $conversationId = $this->directConversation($a->id, $b->id);
        $old = DB::table('messages')->insertGetId(['conversation_id' => $conversationId, 'sender_id' => $b->id, 'body' => 'Old message', 'created_at' => now(), 'updated_at' => now()]);
        $new = DB::table('messages')->insertGetId(['conversation_id' => $conversationId, 'sender_id' => $b->id, 'body' => 'New message', 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($a)->getJson(route('messages.poll', $conversationId) . "?after_id={$old}");

        $response->assertOk();
        $bodies = collect($response->json('messages'))->pluck('body');
        $this->assertTrue($bodies->contains('New message'));
        $this->assertFalse($bodies->contains('Old message'));
    }

    public function test_a_non_participant_cannot_poll_a_conversation(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $outsider = $this->user('outsider@example.com');
        $conversationId = $this->directConversation($a->id, $b->id);

        $this->actingAs($outsider)->getJson(route('messages.poll', $conversationId))->assertForbidden();
    }

    public function test_starting_a_direct_message_resumes_the_existing_conversation_instead_of_duplicating_it(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');
        $existing = $this->directConversation($a->id, $b->id);

        $this->actingAs($a)->post(route('messages.start'), ['user_id' => $b->id])
            ->assertRedirect(route('messages.show', $existing));

        $this->assertSame(1, DB::table('conversations')->where('type', 'direct')->count());
    }

    public function test_starting_a_direct_message_with_a_new_user_creates_a_conversation(): void
    {
        $a = $this->user('a@example.com');
        $b = $this->user('b@example.com');

        $this->actingAs($a)->post(route('messages.start'), ['user_id' => $b->id])->assertRedirect();

        $this->assertSame(1, DB::table('conversations')->where('type', 'direct')->count());
        $conversationId = DB::table('conversations')->where('type', 'direct')->value('id');
        $participantIds = DB::table('conversation_participants')->where('conversation_id', $conversationId)->pluck('user_id')->sort()->values()->all();
        $this->assertSame([$a->id, $b->id], $participantIds);
    }

    public function test_alumni_can_create_a_group_chat(): void
    {
        $alumnus = $this->user('alumnus@example.com', Role::ALUMNI);
        $member = $this->user('member@example.com');

        $this->actingAs($alumnus)->post(route('messages.group.create'), [
            'name' => 'Study Group',
            'member_ids' => [$member->id],
        ])->assertRedirect();

        $conversationId = DB::table('conversations')->where('name', 'Study Group')->value('id');
        $this->assertNotNull($conversationId);
        $this->assertTrue(DB::table('conversation_participants')->where('conversation_id', $conversationId)->where('user_id', $alumnus->id)->exists());
        $this->assertTrue(DB::table('conversation_participants')->where('conversation_id', $conversationId)->where('user_id', $member->id)->exists());
    }

    public function test_a_student_cannot_create_a_group_chat(): void
    {
        $student = $this->user('student@example.com');
        $member = $this->user('member@example.com');

        $this->actingAs($student)->post(route('messages.group.create'), [
            'name' => 'Should Fail',
            'member_ids' => [$member->id],
        ])->assertForbidden();

        $this->assertSame(0, DB::table('conversations')->where('name', 'Should Fail')->count());
    }

    public function test_only_the_group_creator_or_chair_can_rename_the_group(): void
    {
        $alumnus = $this->user('alumnus@example.com', Role::ALUMNI);
        $groupId = $this->groupConversation($alumnus->id, [$alumnus->id]);
        $bystander = $this->user('bystander@example.com', Role::ALUMNI);
        $this->addParticipant($groupId, $bystander->id);

        $this->actingAs($bystander)->put(route('messages.rename', $groupId), ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->actingAs($alumnus)->put(route('messages.rename', $groupId), ['name' => 'Renamed Group'])
            ->assertRedirect();
        $this->assertSame('Renamed Group', DB::table('conversations')->find($groupId)->name);
    }

    public function test_only_the_group_creator_or_chair_can_add_members(): void
    {
        $alumnus = $this->user('alumnus@example.com', Role::ALUMNI);
        $groupId = $this->groupConversation($alumnus->id, [$alumnus->id]);
        $bystander = $this->user('bystander@example.com', Role::ALUMNI);
        $this->addParticipant($groupId, $bystander->id);
        $newMember = $this->user('new@example.com');

        $this->actingAs($bystander)->post(route('messages.members.add', $groupId), ['member_ids' => [$newMember->id]])
            ->assertForbidden();

        $this->actingAs($alumnus)->post(route('messages.members.add', $groupId), ['member_ids' => [$newMember->id]])
            ->assertRedirect();
        $this->assertTrue(DB::table('conversation_participants')->where('conversation_id', $groupId)->where('user_id', $newMember->id)->exists());
    }

    public function test_a_member_can_leave_a_group_chat(): void
    {
        $alumnus = $this->user('alumnus@example.com', Role::ALUMNI);
        $member = $this->user('member@example.com');
        $groupId = $this->groupConversation($alumnus->id, [$alumnus->id, $member->id]);

        $this->actingAs($member)->post(route('messages.leave', $groupId))->assertRedirect(route('messages.index'));

        $this->assertFalse(DB::table('conversation_participants')->where('conversation_id', $groupId)->where('user_id', $member->id)->exists());
    }

    public function test_a_member_cannot_leave_the_default_community_group(): void
    {
        $member = $this->user('member@example.com');
        $groupId = DB::table('conversations')->insertGetId([
            'type' => 'group', 'name' => 'CPACE Community', 'is_default_group' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->addParticipant($groupId, $member->id);

        $this->actingAs($member)->post(route('messages.leave', $groupId))->assertStatus(400);

        $this->assertTrue(DB::table('conversation_participants')->where('conversation_id', $groupId)->where('user_id', $member->id)->exists());
    }

    private function user(string $email, int $roleId = Role::STUDENT): User
    {
        return User::create([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }

    private function directConversation(int $userIdA, int $userIdB): int
    {
        $conversationId = DB::table('conversations')->insertGetId([
            'type' => 'direct', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->addParticipant($conversationId, $userIdA);
        $this->addParticipant($conversationId, $userIdB);

        return $conversationId;
    }

    private function groupConversation(int $creatorId, array $memberIds): int
    {
        $groupId = DB::table('conversations')->insertGetId([
            'type' => 'group', 'name' => 'Study Group', 'created_by' => $creatorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($memberIds as $id) {
            $this->addParticipant($groupId, $id);
        }

        return $groupId;
    }

    private function addParticipant(int $conversationId, int $userId): void
    {
        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId, 'user_id' => $userId, 'joined_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
