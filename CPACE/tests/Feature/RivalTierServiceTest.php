<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\RivalTierService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RivalTierServiceTest extends TestCase
{
    private const TABLES = ['quiz_sessions', 'users'];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('role_id');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type')->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('duration_secs')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Cache::flush();
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_falls_back_to_hand_picked_tiers_when_there_is_not_enough_history(): void
    {
        // A small school - 25 active students total - but only a handful of
        // them have actually built up a quiz history. Requirement is
        // max(10, ceil(25 * 0.5)) = 13 qualifying students; only 5 qualify here.
        $this->seedActiveStudents(25);
        for ($i = 1; $i <= 5; $i++) {
            $this->seedQualifyingStudent($i, spq: 10, acc: 0.90);
        }

        $service = new RivalTierService();

        $this->assertFalse($service->isDataDerived());
        $tiers = $service->tiers();
        $this->assertCount(8, $tiers);
        // The original hand-picked "Aria" tier is the known fallback marker.
        $this->assertSame('Aria', $tiers[0]['name']);
        $this->assertSame(13.0, (float) $tiers[0]['spq']);
    }

    public function test_derives_tiers_once_enough_of_a_small_schools_students_have_a_history(): void
    {
        // Same 25-student school, but now 15 of them qualify - comfortably
        // over the required 13 (max(10, ceil(25 * 0.5))). A fixed "30
        // students" floor would never trigger here; the ratio-based one does.
        $this->seedActiveStudents(25);
        for ($i = 1; $i <= 15; $i++) {
            $this->seedQualifyingStudent($i, spq: 10, acc: 0.95);
        }

        $service = new RivalTierService();

        $this->assertTrue($service->isDataDerived());
        $tiers = $service->tiers();
        $this->assertCount(8, $tiers);
        // Every tier's spq/acc should now be anchored to the seeded 10s/0.95
        // baseline rather than the original hand-picked numbers (13s/0.81 for
        // the same "Aria" personality slot).
        $this->assertNotEquals(13.0, (float) $tiers[0]['spq']);
    }

    public function test_requirement_scales_up_for_a_larger_school_instead_of_staying_fixed(): void
    {
        // A much bigger install - 200 active students - needs ceil(200*0.5)
        // = 100 qualifying students. 15 qualifying (which was enough for the
        // 25-student school above) must NOT be enough here: the same absolute
        // headcount means something different depending on school size.
        $this->seedActiveStudents(200);
        for ($i = 1; $i <= 15; $i++) {
            $this->seedQualifyingStudent($i, spq: 10, acc: 0.95);
        }

        $this->assertFalse((new RivalTierService())->isDataDerived());
    }

    public function test_practice_room_and_training_sessions_never_influence_the_derived_baseline(): void
    {
        // 15 genuine qualifying students in a 25-student school...
        $this->seedActiveStudents(25);
        for ($i = 1; $i <= 15; $i++) {
            $this->seedQualifyingStudent($i, spq: 10, acc: 0.95);
        }
        // ...plus a flood of practice-room and training sessions from a single
        // other student, all with a wildly different (very slow, low-accuracy)
        // pace. If these leaked into the computation the baseline would shift.
        for ($i = 0; $i < 10; $i++) {
            DB::table('quiz_sessions')->insert([
                'student_id' => 999,
                'session_type' => 'testing',
                'is_practice_room' => true,
                'total_items' => 10,
                'correct_answers' => 1,
                'duration_secs' => 10 * 200,
                'completed_at' => now(),
            ]);
            DB::table('quiz_sessions')->insert([
                'student_id' => 998,
                'session_type' => 'training',
                'is_practice_room' => false,
                'total_items' => 10,
                'correct_answers' => 1,
                'duration_secs' => 10 * 200,
                'completed_at' => now(),
            ]);
        }

        $service = new RivalTierService();
        $tiersWithNoise = $service->tiers();

        // Remove the noise and recompute with a fresh cache - the result must
        // be identical, proving the noisy sessions were never counted.
        Cache::flush();
        DB::table('quiz_sessions')->whereIn('student_id', [999, 998])->delete();
        $tiersWithoutNoise = (new RivalTierService())->tiers();

        $this->assertEquals($tiersWithoutNoise, $tiersWithNoise);
    }

    private function seedActiveStudents(int $count): void
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = ['id' => $i, 'role_id' => Role::STUDENT, 'is_active' => true];
        }
        DB::table('users')->insert($rows);
    }

    private function seedQualifyingStudent(int $studentId, float $spq, float $acc): void
    {
        // 3 completed sessions - the minimum to qualify - each with the same
        // pace/accuracy so the student's average is exactly spq/acc.
        for ($i = 0; $i < 3; $i++) {
            $totalItems = 10;
            DB::table('quiz_sessions')->insert([
                'student_id' => $studentId,
                'session_type' => 'testing',
                'is_practice_room' => false,
                'total_items' => $totalItems,
                'correct_answers' => (int) round($acc * $totalItems),
                'duration_secs' => (int) round($spq * $totalItems),
                'started_at' => now()->subMinutes(30),
                'completed_at' => now(),
            ]);
        }
    }
}
