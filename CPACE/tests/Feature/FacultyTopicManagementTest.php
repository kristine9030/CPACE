<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FacultyTopicManagementTest extends TestCase
{
    private const TABLES = [
        'questions', 'topics', 'faculty_subjects', 'subjects', 'activity_logs',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 20)->nullable();
            $table->string('action', 60);
            $table->text('description')->nullable();
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
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
            $table->json('audience_years')->nullable();
            $table->json('audience_sections')->nullable();
            $table->json('audience_semesters')->nullable();
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
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_faculty_can_create_a_topic_in_an_assigned_subject(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subject();
        $this->assign($faculty->id, $subjectId);

        $this->actingAs($faculty)->post(route('faculty.test-bank.topics.store', $subjectId), [
            'name' => 'Revenue Recognition',
            'sort_order' => 1,
            'is_active' => '1',
            'audience_years' => [3],
            'audience_semesters' => [2],
        ])->assertRedirect();

        $topic = DB::table('topics')->where('subject_id', $subjectId)->where('name', 'Revenue Recognition')->first();
        $this->assertNotNull($topic);
        $this->assertSame([3], json_decode($topic->audience_years, true));
        $this->assertSame([2], json_decode($topic->audience_semesters, true));
    }

    public function test_faculty_cannot_manage_topics_in_a_subject_they_are_not_assigned_to(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subject();
        // Not assigned.

        $this->actingAs($faculty)->post(route('faculty.test-bank.topics.store', $subjectId), [
            'name' => 'Should Not Save', 'sort_order' => 1, 'is_active' => '1',
        ])->assertForbidden();

        $this->assertNull(DB::table('topics')->where('name', 'Should Not Save')->first());
    }

    public function test_faculty_can_edit_and_delete_a_topic_in_an_assigned_subject(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subject();
        $this->assign($faculty->id, $subjectId);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($faculty)->put(route('faculty.test-bank.topics.update', [$subjectId, $topicId]), [
            'name' => 'Inventory Valuation', 'sort_order' => 1, 'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame('Inventory Valuation', DB::table('topics')->find($topicId)->name);

        $this->actingAs($faculty)->delete(route('faculty.test-bank.topics.destroy', [$subjectId, $topicId]))
            ->assertRedirect();
        $this->assertNull(DB::table('topics')->find($topicId));
    }

    public function test_faculty_cannot_edit_a_topic_in_a_subject_they_are_not_assigned_to(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subject();
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        // Not assigned to $subjectId.

        $this->actingAs($faculty)->put(route('faculty.test-bank.topics.update', [$subjectId, $topicId]), [
            'name' => 'Hijacked', 'sort_order' => 1, 'is_active' => '1',
        ])->assertForbidden();

        $this->assertSame('Inventory', DB::table('topics')->find($topicId)->name);
    }

    public function test_the_topics_page_only_lists_the_faculty_members_own_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $assigned = $this->subject('FAR');
        $other = $this->subject('AUD');
        $this->assign($faculty->id, $assigned);

        $response = $this->actingAs($faculty)->get(route('faculty.test-bank.topics'));
        $response->assertOk();
        $response->assertSee('FAR');
        $response->assertDontSee('AUD');
    }

    public function test_faculty_cannot_open_an_unassigned_subjects_topics_by_guessing_the_url(): void
    {
        $faculty = $this->faculty();
        $other = $this->subject('AUD');

        $this->actingAs($faculty)->get(route('faculty.test-bank.topics', ['subject' => $other]))
            ->assertForbidden();
    }

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty-topics@example.com', 'password' => Hash::make('password'),
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

    private function assign(int $facultyId, int $subjectId): void
    {
        DB::table('faculty_subjects')->insert(['faculty_id' => $facultyId, 'subject_id' => $subjectId, 'assigned_at' => now()]);
    }
}
