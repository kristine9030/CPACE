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
 * Covers Faculty\MaterialController: the same "faculty must be assigned to
 * the subject" authorization pattern already fixed once in the test bank
 * (see TestBankController), applied here to uploading/deleting materials.
 * Hand-built schema, same rationale as the other Feature tests in this suite.
 */
class FacultyMaterialTest extends TestCase
{
    private const TABLES = [
        'materials', 'topics', 'subjects', 'faculty_subjects',
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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->integer('sort_order')->default(0);
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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('kind', 10)->default('file');
            $table->string('file_category', 20)->default('other');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('external_url')->nullable();
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

    public function test_a_faculty_member_can_add_a_link_material_to_their_assigned_subject(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topicUnderAssignedSubject($faculty);

        $this->actingAs($faculty)->post(route('faculty.materials.store'), [
            'topic_id' => $topicId,
            'title' => 'Reference Slides',
            'kind' => 'link',
            'external_url' => 'https://example.com/slides',
        ])->assertRedirect();

        $this->assertSame(1, DB::table('materials')->where('topic_id', $topicId)->count());
    }

    public function test_a_faculty_member_cannot_add_a_material_to_a_subject_they_are_not_assigned_to(): void
    {
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($faculty)->post(route('faculty.materials.store'), [
            'topic_id' => $topicId,
            'title' => 'Should Not Save',
            'kind' => 'link',
            'external_url' => 'https://example.com/slides',
        ])->assertForbidden();

        $this->assertSame(0, DB::table('materials')->where('topic_id', $topicId)->count());
    }

    public function test_a_faculty_member_cannot_delete_a_material_outside_their_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        $materialId = DB::table('materials')->insertGetId([
            'topic_id' => $topicId, 'title' => 'Foreign Material', 'kind' => 'link', 'external_url' => 'https://example.com',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($faculty)->delete(route('faculty.materials.destroy', $materialId))->assertForbidden();

        $this->assertNotNull(DB::table('materials')->find($materialId));
    }

    public function test_a_faculty_member_can_delete_a_material_in_their_assigned_subject(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topicUnderAssignedSubject($faculty);
        $materialId = DB::table('materials')->insertGetId([
            'topic_id' => $topicId, 'title' => 'My Material', 'kind' => 'link', 'external_url' => 'https://example.com',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($faculty)->delete(route('faculty.materials.destroy', $materialId))->assertRedirect();

        $this->assertNull(DB::table('materials')->find($materialId));
    }

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function topicUnderAssignedSubject(User $faculty): int
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);

        return DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Risk Assessment', 'created_at' => now(), 'updated_at' => now()]);
    }
}
