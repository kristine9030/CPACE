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
 * Covers ReviewNoteController's CRUD + trash/archive/favorite actions - the
 * "generate a quiz from a note" endpoint itself is covered separately by
 * NoteQuizTest. Hand-built schema, same rationale as the other Feature tests
 * in this suite (migration set doesn't run cleanly from scratch).
 */
class ReviewNoteCrudTest extends TestCase
{
    private const TABLES = [
        'review_notes', 'topics', 'subjects',
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

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
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
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_a_student_can_create_a_note(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('review-notes.store'), [
            'title' => 'Partnership Dissolution',
            'content' => 'Notes go here.',
        ])->assertRedirect(route('review-notes'));

        $this->assertSame(1, DB::table('review_notes')->where('student_id', $student->id)->count());
    }

    public function test_a_student_cannot_view_another_students_note(): void
    {
        $student = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($other);

        $this->actingAs($student)->getJson(route('review-notes.show', $noteId))->assertForbidden();
    }

    public function test_opening_a_note_to_read_it_increments_the_review_count(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->actingAs($student)->getJson(route('review-notes.show', $noteId) . '?read=1')->assertOk();

        $this->assertSame(1, DB::table('review_notes')->find($noteId)->review_count);
    }

    public function test_a_student_cannot_update_another_students_note(): void
    {
        $student = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($other);

        $this->actingAs($student)->putJson(route('review-notes.update', $noteId), [
            'title' => 'Hijacked',
        ])->assertForbidden();

        $this->assertNotSame('Hijacked', DB::table('review_notes')->find($noteId)->title);
    }

    public function test_a_student_can_update_their_own_note(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->actingAs($student)->putJson(route('review-notes.update', $noteId), [
            'title' => 'Updated Title',
        ])->assertOk();

        $this->assertSame('Updated Title', DB::table('review_notes')->find($noteId)->title);
    }

    public function test_deleting_an_active_note_soft_deletes_it(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->actingAs($student)->deleteJson(route('review-notes.destroy', $noteId))->assertOk();

        $this->assertNull(DB::table('review_notes')->whereNull('deleted_at')->find($noteId));
        $this->assertNotNull(DB::table('review_notes')->find($noteId), 'a first delete should trash it, not remove the row');
    }

    public function test_deleting_an_already_trashed_note_permanently_removes_it(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);
        DB::table('review_notes')->where('id', $noteId)->update(['deleted_at' => now()]);

        $this->actingAs($student)->deleteJson(route('review-notes.destroy', $noteId))->assertOk();

        $this->assertNull(DB::table('review_notes')->find($noteId));
    }

    public function test_a_student_cannot_delete_another_students_note(): void
    {
        $student = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $noteId = $this->note($other);

        $this->actingAs($student)->deleteJson(route('review-notes.destroy', $noteId))->assertForbidden();

        $this->assertNotNull(DB::table('review_notes')->find($noteId));
    }

    public function test_restoring_a_trashed_note_brings_it_back(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);
        DB::table('review_notes')->where('id', $noteId)->update(['deleted_at' => now()]);

        $this->actingAs($student)->postJson(route('review-notes.restore', $noteId))->assertOk();

        $this->assertNull(DB::table('review_notes')->find($noteId)->deleted_at);
    }

    public function test_archiving_a_note_toggles_its_archived_state(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->actingAs($student)->postJson(route('review-notes.archive', $noteId))->assertOk();
        $this->assertNotNull(DB::table('review_notes')->find($noteId)->archived_at);

        $this->actingAs($student)->postJson(route('review-notes.archive', $noteId))->assertOk();
        $this->assertNull(DB::table('review_notes')->find($noteId)->archived_at);
    }

    public function test_favoriting_a_note_toggles_its_favorite_flag(): void
    {
        $student = $this->student();
        $noteId = $this->note($student);

        $this->actingAs($student)->postJson(route('review-notes.favorite', $noteId))
            ->assertJson(['is_favorite' => true]);
        $this->actingAs($student)->postJson(route('review-notes.favorite', $noteId))
            ->assertJson(['is_favorite' => false]);
    }

    private function student(string $email = 'student@example.com'): User
    {
        return User::create([
            'role_id'    => Role::STUDENT,
            'first_name' => 'Stu',
            'last_name'  => 'Dent',
            'email'      => $email,
            'password'   => Hash::make('password'),
            'setup_completed_at' => now(),
        ]);
    }

    private function note(User $owner): int
    {
        return DB::table('review_notes')->insertGetId([
            'student_id' => $owner->id,
            'title' => 'Partnership dissolution',
            'content' => 'A partnership is dissolved when there is a change in the relation of the partners.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
