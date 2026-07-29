<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChairSubjectManagementTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order. Hand-built schema, same rationale as
     * the other Feature tests in this suite (migration set doesn't run
     * cleanly from scratch).
     */
    private const TABLES = [
        'questions', 'topics', 'subjects',
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
            $table->boolean('email_verified')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('passing_threshold')->default(75);
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->text('question_text');
            $table->string('difficulty')->default('moderate');
            $table->boolean('is_active')->default(true);
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

    public function test_chair_can_create_a_topic_under_a_subject(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject();

        $this->actingAs($chair)->post(route('chair.subjects.topics.store', $subjectId), [
            'name' => 'Revenue Recognition',
            'sort_order' => 1,
            'is_active' => '1',
        ])->assertRedirect();

        $topic = DB::table('topics')->where('subject_id', $subjectId)->where('name', 'Revenue Recognition')->first();
        $this->assertNotNull($topic);
    }

    public function test_a_topic_name_must_be_unique_within_its_subject_but_not_globally(): void
    {
        $chair = $this->chair();
        $subjectA = $this->subject('FAR');
        $subjectB = $this->subject('AUD');

        $this->actingAs($chair)->post(route('chair.subjects.topics.store', $subjectA), [
            'name' => 'Internal Controls', 'sort_order' => 1, 'is_active' => '1',
        ])->assertRedirect();

        // Same name, different subject: allowed.
        $this->actingAs($chair)->post(route('chair.subjects.topics.store', $subjectB), [
            'name' => 'Internal Controls', 'sort_order' => 1, 'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        // Same name, same subject: rejected.
        $this->actingAs($chair)->post(route('chair.subjects.topics.store', $subjectA), [
            'name' => 'Internal Controls', 'sort_order' => 2, 'is_active' => '1',
        ])->assertSessionHasErrors(['name']);

        $this->assertSame(2, DB::table('topics')->where('name', 'Internal Controls')->count());
    }

    public function test_a_topic_cannot_be_reparented_under_its_own_descendant(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject();

        $parentId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Parent', 'sort_order' => 1, 'is_active' => true]);
        $childId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'parent_id' => $parentId, 'name' => 'Child', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($chair)->put(route('chair.subjects.topics.update', [$subjectId, $parentId]), [
            'name' => 'Parent',
            'parent_id' => $childId,
            'sort_order' => 1,
            'is_active' => '1',
        ])->assertSessionHasErrors(['parent_id']);

        $this->assertNull(DB::table('topics')->find($parentId)->parent_id);
    }

    public function test_a_topic_with_questions_cannot_be_deleted(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject();
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        DB::table('questions')->insert(['topic_id' => $topicId, 'question_text' => 'x', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($chair)->delete(route('chair.subjects.topics.destroy', [$subjectId, $topicId]))
            ->assertRedirect();

        $this->assertNotNull(DB::table('topics')->find($topicId), 'a topic with test-bank questions must not be deletable');
    }

    public function test_a_topic_with_subtopics_cannot_be_deleted(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject();
        $parentId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Parent', 'sort_order' => 1, 'is_active' => true]);
        DB::table('topics')->insert(['subject_id' => $subjectId, 'parent_id' => $parentId, 'name' => 'Child', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($chair)->delete(route('chair.subjects.topics.destroy', [$subjectId, $parentId]))
            ->assertRedirect();

        $this->assertNotNull(DB::table('topics')->find($parentId), 'a topic with subtopics must not be deletable');
    }

    public function test_an_empty_leaf_topic_can_be_deleted(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject();
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Unused Topic', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($chair)->delete(route('chair.subjects.topics.destroy', [$subjectId, $topicId]))
            ->assertRedirect();

        $this->assertNull(DB::table('topics')->find($topicId));
    }

    public function test_a_topic_cannot_be_moved_to_a_subject_it_does_not_belong_to(): void
    {
        $chair = $this->chair();
        $subjectA = $this->subject('FAR');
        $subjectB = $this->subject('AUD');
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectA, 'name' => 'Foreign Topic', 'sort_order' => 1, 'is_active' => true]);

        // Addressing the topic through the wrong subject in the URL is a 404,
        // not a silent cross-subject move.
        $this->actingAs($chair)->put(route('chair.subjects.topics.update', [$subjectB, $topicId]), [
            'name' => 'Renamed', 'sort_order' => 1, 'is_active' => '1',
        ])->assertNotFound();
    }

    public function test_faculty_cannot_manage_subjects_or_topics(): void
    {
        $faculty = User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        $subjectId = $this->subject();

        $this->actingAs($faculty)->get(route('chair.subjects'))->assertForbidden();
        $this->actingAs($faculty)->post(route('chair.subjects.topics.store', $subjectId), [
            'name' => 'Should Not Save', 'sort_order' => 1, 'is_active' => '1',
        ])->assertForbidden();
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN,
            'first_name' => 'Program', 'last_name' => 'Chair',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function subject(string $code = 'FAR'): int
    {
        return DB::table('subjects')->insertGetId([
            'code' => $code, 'name' => $code, 'passing_threshold' => 75,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
