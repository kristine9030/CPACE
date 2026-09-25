<?php

namespace Tests\Feature;

use App\Services\PerformanceRecorder;
use App\Services\WeaknessDetector;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The single source of truth for "weak" and "strong" (WeaknessDetector), and the
 * real "wrong in a row" streak that PerformanceRecorder feeds it.
 */
class WeaknessStrengthRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
            $table->timestamp('last_attempted')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('performance_records');
        parent::tearDown();
    }

    private function record(int $correct, int $attempts, int $wrongRun = 0): object
    {
        return (object) ['correct_count' => $correct, 'total_attempts' => $attempts, 'consecutive_wrong' => $wrongRun];
    }

    // ── streak arithmetic ────────────────────────────────────────────────

    public function test_a_sitting_with_no_correct_answer_extends_the_running_streak(): void
    {
        $this->assertSame(5, WeaknessDetector::nextStreak(2, ['attempts' => 3, 'correct' => 0, 'trailing_wrong' => 3]));
    }

    public function test_a_sitting_that_ends_on_a_correct_answer_resets_the_streak(): void
    {
        $this->assertSame(0, WeaknessDetector::nextStreak(7, ['attempts' => 4, 'correct' => 3, 'trailing_wrong' => 0]));
    }

    public function test_only_the_wrong_answers_at_the_end_of_a_mixed_sitting_count(): void
    {
        // e.g. right, wrong, right, wrong, wrong -> the streak is the last 2, not the 3 wrongs.
        $this->assertSame(2, WeaknessDetector::nextStreak(9, ['attempts' => 5, 'correct' => 2, 'trailing_wrong' => 2]));
    }

    // ── recorder: the streak is real, not a running total of wrong answers ──

    public function test_a_topic_answered_mostly_right_never_builds_a_false_streak_across_sittings(): void
    {
        $recorder = app(PerformanceRecorder::class);

        // Ten sittings of 9/10 (one wrong somewhere in the middle, never last).
        for ($i = 0; $i < 10; $i++) {
            $recorder->record(1, [5 => ['attempts' => 10, 'correct' => 9, 'trailing_wrong' => 0]]);
        }

        $row = DB::table('performance_records')->first();
        $this->assertSame(0, (int) $row->consecutive_wrong);
        $this->assertFalse((bool) $row->is_weak_area, '90% accuracy must not be flagged weak by a phantom streak');
    }

    public function test_three_wrong_answers_in_a_row_across_sittings_do_flag_the_topic_weak(): void
    {
        $recorder = app(PerformanceRecorder::class);

        $recorder->record(1, [5 => ['attempts' => 10, 'correct' => 10, 'trailing_wrong' => 0]]);
        $recorder->record(1, [5 => ['attempts' => 2, 'correct' => 0, 'trailing_wrong' => 2]]);
        $this->assertFalse((bool) DB::table('performance_records')->value('is_weak_area'));

        $recorder->record(1, [5 => ['attempts' => 1, 'correct' => 0, 'trailing_wrong' => 1]]);

        $row = DB::table('performance_records')->first();
        $this->assertSame(3, (int) $row->consecutive_wrong);
        $this->assertTrue((bool) $row->is_weak_area);
    }

    public function test_a_correct_answer_breaks_the_streak(): void
    {
        $recorder = app(PerformanceRecorder::class);

        $recorder->record(1, [5 => ['attempts' => 2, 'correct' => 0, 'trailing_wrong' => 2]]);
        $recorder->record(1, [5 => ['attempts' => 2, 'correct' => 1, 'trailing_wrong' => 0]]); // wrong-then-right
        $recorder->record(1, [5 => ['attempts' => 1, 'correct' => 0, 'trailing_wrong' => 1]]);

        $this->assertSame(1, (int) DB::table('performance_records')->value('consecutive_wrong'));
    }

    // ── shared strength rule ─────────────────────────────────────────────

    public function test_a_strength_needs_75_percent_over_enough_attempts(): void
    {
        $d = new WeaknessDetector();

        $this->assertTrue($d->isStrong($this->record(15, 20)));      // exactly 75%
        $this->assertTrue($d->isStrong($this->record(5, 5)));        // 100% at the attempt floor
        $this->assertFalse($d->isStrong($this->record(7, 10)));      // 70%
        $this->assertFalse($d->isStrong($this->record(4, 4)));       // 100% but only 4 attempts
    }

    public function test_the_75_percent_line_is_compared_unrounded(): void
    {
        // 74.6% rounds to 75 for display, but is not at the mastery line.
        $this->assertFalse((new WeaknessDetector())->isStrong($this->record(373, 500)));
    }

    public function test_a_topic_flagged_weak_is_never_also_a_strength(): void
    {
        // 90% accuracy, but the last 3 answers were wrong -> weak, so not strong.
        $d = new WeaknessDetector();
        $topic = $this->record(9, 10, wrongRun: 3);

        $this->assertTrue($d->evaluate($topic)[0]);
        $this->assertFalse($d->isStrong($topic));
    }

    public function test_exactly_60_percent_is_not_weak(): void
    {
        $this->assertFalse((new WeaknessDetector())->evaluate($this->record(3, 5))[0]);
        $this->assertTrue((new WeaknessDetector())->evaluate($this->record(2, 5))[0]);
    }
}
