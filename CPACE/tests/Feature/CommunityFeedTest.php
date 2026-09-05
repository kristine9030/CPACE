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
 * Covers CommunityController's mutating actions (post/comment/like CRUD and
 * who is allowed to do what) - not the index() feed page itself, which pulls
 * in several more read-only tables and is lower risk. Hand-built schema,
 * same rationale as the other Feature tests in this suite.
 */
class CommunityFeedTest extends TestCase
{
    private const TABLES = [
        'community_replies', 'community_post_likes', 'community_post_attachments', 'community_posts',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users',
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
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('is_alumni')->default(false);
        });
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('title', 200)->nullable();
            $table->text('body');
            $table->string('post_type', 20)->default('discussion');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });
        Schema::create('community_post_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_post_id');
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_category', 20)->default('other');
            $table->timestamps();
        });
        Schema::create('community_post_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_post_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('community_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('author_id');
            $table->text('body');
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

    public function test_an_alumnus_can_post_to_the_community(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'alumnus@example.com');

        $this->actingAs($alumnus)->post(route('community.posts.store'), [
            'post_type' => 'discussion',
            'body' => 'Welcome, everyone!',
        ])->assertRedirect();

        $this->assertSame(1, DB::table('community_posts')->where('author_id', $alumnus->id)->count());
    }

    public function test_a_student_cannot_post_to_the_community(): void
    {
        $student = $this->user(Role::STUDENT, 'student@example.com');

        $this->actingAs($student)->post(route('community.posts.store'), [
            'post_type' => 'discussion',
            'body' => 'I should not be able to post this.',
        ])->assertForbidden();

        $this->assertSame(0, DB::table('community_posts')->count());
    }

    public function test_posting_notifies_every_active_student(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'alumnus@example.com');
        $student1 = $this->user(Role::STUDENT, 's1@example.com');
        $student2 = $this->user(Role::STUDENT, 's2@example.com');
        $inactiveStudent = $this->user(Role::STUDENT, 's3@example.com', active: false);

        $this->actingAs($alumnus)->post(route('community.posts.store'), [
            'post_type' => 'discussion',
            'body' => 'New update for everyone.',
        ]);

        $this->assertTrue(DB::table('notifications')->where('recipient_id', $student1->id)->exists());
        $this->assertTrue(DB::table('notifications')->where('recipient_id', $student2->id)->exists());
        $this->assertFalse(DB::table('notifications')->where('recipient_id', $inactiveStudent->id)->exists());
    }

    public function test_a_post_can_be_deleted_by_its_author_or_the_chair_but_not_by_anyone_else(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'author@example.com');
        $postId = $this->communityPost($alumnus->id);
        $otherStudent = $this->user(Role::STUDENT, 'bystander@example.com');

        $this->actingAs($otherStudent)->delete(route('community.posts.destroy', $postId))->assertForbidden();
        $this->assertNotNull(DB::table('community_posts')->find($postId));

        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $this->actingAs($chair)->delete(route('community.posts.destroy', $postId))->assertRedirect();
        $this->assertNull(DB::table('community_posts')->find($postId));
    }

    public function test_toggling_like_adds_then_removes_it(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'author@example.com');
        $postId = $this->communityPost($alumnus->id);
        $student = $this->user(Role::STUDENT, 'liker@example.com');

        $this->actingAs($student)->post(route('community.posts.like', $postId));
        $this->assertSame(1, DB::table('community_post_likes')->where('community_post_id', $postId)->where('user_id', $student->id)->count());

        $this->actingAs($student)->post(route('community.posts.like', $postId));
        $this->assertSame(0, DB::table('community_post_likes')->where('community_post_id', $postId)->where('user_id', $student->id)->count());
    }

    public function test_anyone_can_comment_on_a_post(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'author@example.com');
        $postId = $this->communityPost($alumnus->id);
        $student = $this->user(Role::STUDENT, 'commenter@example.com');

        $this->actingAs($student)->post(route('community.comments.store', $postId), [
            'body' => 'Great post!',
        ])->assertRedirect();

        $this->assertSame(1, DB::table('community_replies')->where('post_id', $postId)->count());
    }

    public function test_only_the_comment_author_can_delete_their_comment(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'author@example.com');
        $postId = $this->communityPost($alumnus->id);
        $commenter = $this->user(Role::STUDENT, 'commenter@example.com');
        $commentId = DB::table('community_replies')->insertGetId([
            'post_id' => $postId, 'author_id' => $commenter->id, 'body' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $otherStudent = $this->user(Role::STUDENT, 'bystander@example.com');

        $this->actingAs($otherStudent)->delete(route('community.comments.destroy', $commentId))->assertForbidden();
        $this->assertNotNull(DB::table('community_replies')->find($commentId));

        $this->actingAs($commenter)->delete(route('community.comments.destroy', $commentId))->assertRedirect();
        $this->assertNull(DB::table('community_replies')->find($commentId));
    }

    private function user(int $roleId, string $email, bool $active = true): User
    {
        return User::create([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => $active,
            'setup_completed_at' => now(),
        ]);
    }

    private function communityPost(int $authorId): int
    {
        return DB::table('community_posts')->insertGetId([
            'author_id' => $authorId,
            'body' => 'A post body.',
            'post_type' => 'discussion',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
