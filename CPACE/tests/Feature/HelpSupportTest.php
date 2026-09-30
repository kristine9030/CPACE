<?php

namespace Tests\Feature;

use App\Mail\IssueReportedMail;
use App\Mail\SupportReplyMail;
use App\Models\IssueReport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Help & Support: role pages, tickets, reply threads and the Chair's inbox.
 * Hand-built schema like the other Feature tests; RefreshDatabase is not used.
 */
class HelpSupportTest extends TestCase
{
    private const TABLES = [
        'issue_report_replies', 'issue_reports', 'notifications', 'messages',
        'conversation_participants', 'conversations', 'student_profiles', 'users',
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
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('avatar_color', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
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
        Schema::create('issue_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('category', 40)->default('other');
            $table->string('subject', 150)->nullable();
            $table->text('message');
            $table->string('page_url', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
        Schema::create('issue_report_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issue_report_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('body');
            $table->timestamps();
        });

        Mail::fake();
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_each_role_sees_its_own_help_page(): void
    {
        $this->actingAs($this->user(Role::STUDENT, 'stu@example.com'))->get(route('help.index'))
            ->assertOk()->assertSee('Student Reviewer')->assertSee('Mock exams &amp; proctoring', false)
            ->assertDontSee('Support Inbox');

        $this->actingAs($this->user(Role::FACULTY, 'fac@example.com'))->get(route('help.index'))
            ->assertOk()->assertSee('Test bank')->assertDontSee('Mock exams &amp; proctoring', false);

        $this->actingAs($this->user(Role::ADMIN, 'chair@example.com'))->get(route('help.index'))
            ->assertOk()->assertSee('Support Inbox')->assertSee('Program Chair');
    }

    public function test_a_user_can_file_a_request_and_chairs_are_notified(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $student = $this->user(Role::STUDENT, 'stu@example.com');

        $response = $this->actingAs($student)->post(route('help.tickets.store'), [
            'subject'  => 'Camera will not start',
            'category' => 'proctor',
            'message'  => 'The mock exam says my camera was refused even after allowing it.',
        ]);

        $report = IssueReport::sole();
        $response->assertRedirect(route('help.tickets.show', $report));
        $this->assertSame($student->id, (int) $report->user_id);
        $this->assertSame('stu@example.com', $report->email);
        $this->assertSame(IssueReport::STATUS_NEW, $report->status);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $chair->id, 'reference_type' => 'issue_report', 'reference_id' => $report->id,
        ]);
        Mail::assertSent(IssueReportedMail::class);
    }

    public function test_a_user_cannot_see_someone_elses_request(): void
    {
        $owner = $this->user(Role::STUDENT, 'owner@example.com');
        $other = $this->user(Role::STUDENT, 'other@example.com');
        $report = $this->ticket($owner);

        $this->actingAs($other)->get(route('help.tickets.show', $report))->assertNotFound();
        $this->actingAs($other)->post(route('help.tickets.reply', $report), ['body' => 'hi there'])->assertNotFound();
        $this->actingAs($owner)->get(route('help.tickets.show', $report))->assertOk()->assertSee($report->subject);
    }

    public function test_chair_reply_moves_request_to_in_progress_and_notifies_requester(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $student = $this->user(Role::STUDENT, 'stu@example.com');
        $report = $this->ticket($student);

        $this->actingAs($chair)->post(route('help.tickets.reply', $report), ['body' => 'Please try Chrome and allow the camera.'])
            ->assertRedirect();

        $this->assertSame(IssueReport::STATUS_IN_REVIEW, $report->fresh()->status);
        $this->assertDatabaseHas('notifications', ['recipient_id' => $student->id, 'reference_id' => $report->id]);
        Mail::assertSent(SupportReplyMail::class, fn ($m) => $m->hasTo('stu@example.com') && $m->reply !== null);
    }

    public function test_requester_reply_reopens_a_resolved_request(): void
    {
        $this->user(Role::ADMIN, 'chair@example.com');
        $student = $this->user(Role::STUDENT, 'stu@example.com');
        $report = $this->ticket($student, ['status' => IssueReport::STATUS_RESOLVED, 'resolved_at' => now()]);

        $this->actingAs($student)->post(route('help.tickets.reply', $report), ['body' => 'It happened again today.']);

        $report->refresh();
        $this->assertSame(IssueReport::STATUS_IN_REVIEW, $report->status);
        $this->assertNull($report->resolved_at);
        Mail::assertNotSent(SupportReplyMail::class);
    }

    public function test_chair_can_resolve_and_filter_the_inbox(): void
    {
        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $student = $this->user(Role::STUDENT, 'stu@example.com');
        $open = $this->ticket($student, ['subject' => 'Open one']);
        $this->ticket($student, ['subject' => 'Already done', 'status' => IssueReport::STATUS_RESOLVED]);

        $this->actingAs($chair)->get(route('chair.support.index'))
            ->assertOk()->assertSee('Open one')->assertDontSee('Already done');
        $this->actingAs($chair)->get(route('chair.support.index', ['status' => 'resolved']))
            ->assertOk()->assertSee('Already done')->assertDontSee('Open one');

        $this->actingAs($chair)->patch(route('chair.support.status', $open), ['status' => 'resolved'])->assertRedirect();

        $this->assertNotNull($open->fresh()->resolved_at);
        Mail::assertSent(SupportReplyMail::class, fn ($m) => $m->reply === null);
    }

    public function test_non_chairs_cannot_use_the_inbox(): void
    {
        $faculty = $this->user(Role::FACULTY, 'fac@example.com');
        $report = $this->ticket($faculty);

        $this->actingAs($faculty)->get(route('chair.support.index'))->assertStatus(403);
        $this->actingAs($faculty)->patch(route('chair.support.status', $report), ['status' => 'resolved']);
        $this->assertSame(IssueReport::STATUS_NEW, $report->fresh()->status);
    }

    public function test_the_support_reply_email_renders_with_the_brand_layout(): void
    {
        $student = $this->user(Role::STUDENT, 'stu@example.com');
        $report = $this->ticket($student);
        $reply = $report->replies()->create(['user_id' => null, 'body' => 'Fixed on our side.']);

        $html = (new SupportReplyMail($report, $reply))->render();

        $this->assertStringContainsString('Fixed on our side.', $html);
        $this->assertStringContainsString('alt="CPAce crest"', $html);
    }

    private function ticket(User $user, array $overrides = []): IssueReport
    {
        return IssueReport::create(array_merge([
            'user_id'  => $user->id,
            'name'     => $user->first_name . ' ' . $user->last_name,
            'email'    => $user->email,
            'category' => 'bug',
            'subject'  => 'Dashboard does not load',
            'message'  => 'The dashboard spins forever after signing in.',
            'status'   => IssueReport::STATUS_NEW,
        ], $overrides));
    }

    /** Inserted directly so User::booted()'s student hooks don't need the community tables. */
    private function user(int $roleId, string $email): User
    {
        $id = DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }
}
