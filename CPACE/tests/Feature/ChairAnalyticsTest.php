<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\ChairAnalyticsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChairAnalyticsTest extends TestCase
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
            $table->rememberToken();
            $table->timestamps();
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->string('difficulty');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count');
            $table->integer('total_attempts');
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type');
            $table->integer('total_items');
            $table->integer('correct_answers');
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
        });

        $this->seedAnalyticsData();
    }

    protected function tearDown(): void
    {
        foreach (['notifications', 'quiz_sessions', 'performance_records', 'questions', 'topics', 'subjects', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_chair_can_open_both_analytics_reports(): void
    {
        $chair = User::where('email', 'chair@example.com')->firstOrFail();

        $this->actingAs($chair)->get(route('chair.analytics.performance'))
            ->assertOk()
            ->assertSee('Class-Level Performance')
            ->assertSee('70%')
            ->assertSee('Predicted Pass Rate');

        $this->actingAs($chair)->get(route('chair.analytics.test-bank-coverage'))
            ->assertOk()
            ->assertSee('Test Bank Coverage')
            ->assertSee('Thin Topics')
            ->assertSee('Needs 23 more');
    }

    public function test_service_calculates_weighted_accuracy_readiness_and_coverage(): void
    {
        $analytics = app(ChairAnalyticsService::class);
        $report = $analytics->performanceReport();
        $coverage = $analytics->coverageReport();

        $this->assertSame(70, $report['overall_accuracy']);
        $this->assertSame(100, $report['readiness']['readiness_rate']);
        $this->assertSame(100, $report['readiness']['pass_projection']);
        $this->assertSame('thin', $coverage->firstWhere('name', 'Topic 1')['status']);
        $this->assertSame('critical', $coverage->firstWhere('name', 'Topic 2')['status']);
    }

    public function test_student_cannot_open_chair_analytics(): void
    {
        $student = User::where('email', 'student@example.com')->firstOrFail();

        $this->actingAs($student)->get(route('chair.analytics.performance'))->assertForbidden();
    }

    private function seedAnalyticsData(): void
    {
        User::create(['role_id' => Role::ADMIN, 'first_name' => 'Program', 'last_name' => 'Chair', 'email' => 'chair@example.com', 'password' => Hash::make('password'), 'is_active' => true]);
        $student = User::create(['role_id' => Role::STUDENT, 'first_name' => 'Test', 'last_name' => 'Student', 'email' => 'student@example.com', 'password' => Hash::make('password'), 'is_active' => true]);

        foreach ([['AUD', 'Auditing'], ['FAR', 'Financial Accounting'], ['TAX', 'Taxation']] as $index => [$code, $name]) {
            $subjectId = DB::table('subjects')->insertGetId(['code' => $code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Topic '.($index + 1), 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $topicId, 'correct_count' => 14, 'total_attempts' => 20]);
            DB::table('quiz_sessions')->insert(['student_id' => $student->id, 'subject_id' => $subjectId, 'session_type' => 'adaptive', 'total_items' => 20, 'correct_answers' => 14, 'completed_at' => now()]);

            if ($index === 0) {
                DB::table('questions')->insert([
                    ['topic_id' => $topicId, 'difficulty' => 'easy', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['topic_id' => $topicId, 'difficulty' => 'moderate', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        }
    }
}
