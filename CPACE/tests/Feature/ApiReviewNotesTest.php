<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the mobile API's Review Notes endpoints (Api\ReviewNoteApiController)
 * - the same ownership boundary already covered for the web version in
 * ReviewNoteCrudTest, verified here for the mobile token-auth path too.
 * Hand-built schema, same rationale as the other Feature tests in this suite
 * (migration set doesn't run cleanly from scratch).
 */
class ApiReviewNotesTest extends TestCase
{
    private const TABLES = [
        'review_notes', 'topics', 'subjects', 'api_tokens',
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
            $table->boolean('is_read')->default(false);
        });
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token', 128)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name', 150);
        });
        Schema::create('review_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('title', 180);
            $table->longText('content')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->softDeletes();
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

    public function test_a_student_can_create_a_note_via_the_api(): void
    {
        $student = $this->student();

        $response = $this->postJson('/api/review-notes', [
            'title' => 'Partnership Dissolution',
        ], $this->tokenHeaders($student->id));

        $response->assertStatus(201)->assertJsonPath('note.title', 'Partnership Dissolution');
        $this->assertSame(1, DB::table('review_notes')->where('student_id', $student->id)->count());
    }

    public function test_the_index_only_lists_the_authenticated_students_own_notes(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $this->note($me, 'Mine');
        $this->note($other, 'Not Mine');

        $response = $this->getJson('/api/review-notes', $this->tokenHeaders($me->id));

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('Mine'));
        $this->assertFalse($titles->contains('Not Mine'));
    }

    public function test_a_student_cannot_view_another_students_note(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($other, 'Private');

        $this->getJson("/api/review-notes/{$noteId}", $this->tokenHeaders($me->id))->assertStatus(403);
    }

    public function test_a_student_cannot_update_another_students_note(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($other, 'Private');

        $this->putJson("/api/review-notes/{$noteId}", ['title' => 'Hijacked'], $this->tokenHeaders($me->id))
            ->assertStatus(403);

        $this->assertNotSame('Hijacked', DB::table('review_notes')->find($noteId)->title);
    }

    public function test_a_student_cannot_delete_another_students_note(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($other, 'Private');

        $this->deleteJson("/api/review-notes/{$noteId}", [], $this->tokenHeaders($me->id))->assertStatus(403);

        $this->assertNotNull(DB::table('review_notes')->find($noteId));
    }

    public function test_a_student_can_delete_their_own_note(): void
    {
        $student = $this->student();
        $noteId = $this->note($student, 'Mine');

        $this->deleteJson("/api/review-notes/{$noteId}", [], $this->tokenHeaders($student->id))->assertOk();

        $this->assertNull(DB::table('review_notes')->whereNull('deleted_at')->find($noteId));
    }

    public function test_favoriting_a_note_toggles_its_favorite_flag(): void
    {
        $student = $this->student();
        $noteId = $this->note($student, 'Mine');

        $this->postJson("/api/review-notes/{$noteId}/favorite", [], $this->tokenHeaders($student->id))
            ->assertJson(['is_favorite' => true]);
        $this->postJson("/api/review-notes/{$noteId}/favorite", [], $this->tokenHeaders($student->id))
            ->assertJson(['is_favorite' => false]);
    }

    private function student(string $email = 'student@example.com'): User
    {
        return User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function tokenHeaders(int $userId): array
    {
        $token = ApiToken::create([
            'user_id' => $userId,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDay(),
        ]);

        return ['Authorization' => 'Bearer ' . $token->token];
    }

    private function note(User $owner, string $title): int
    {
        return DB::table('review_notes')->insertGetId([
            'student_id' => $owner->id, 'title' => $title, 'content' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
