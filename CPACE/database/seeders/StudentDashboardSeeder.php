<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seeds realistic sample dashboard data (profile, performance, quiz sessions,
 * points, badges, notifications) for every student-role user.
 *
 * Idempotent: clears its own previously-seeded rows for each student first,
 * so it is safe to re-run.
 */
class StudentDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $students = DB::table('users')->where('role_id', Role::STUDENT)->get();

        foreach ($students as $student) {
            $this->seedForStudent($student->id);
        }
    }

    protected function seedForStudent(int $studentId): void
    {
        $now = Carbon::now();

        // ── Clean slate for this student (safe re-run) ────────────────────
        DB::table('performance_records')->where('student_id', $studentId)->delete();
        DB::table('quiz_answers')->whereIn('session_id',
            DB::table('quiz_sessions')->where('student_id', $studentId)->pluck('id'))->delete();
        DB::table('quiz_sessions')->where('student_id', $studentId)->delete();
        DB::table('points_log')->where('student_id', $studentId)->delete();
        DB::table('student_badges')->where('student_id', $studentId)->delete();

        // ── Student profile (gamification + exam target) ──────────────────
        DB::table('student_profiles')->updateOrInsert(
            ['user_id' => $studentId],
            [
                'student_number'   => '2026-' . str_pad((string) $studentId, 5, '0', STR_PAD_LEFT),
                'year_level'       => 4,
                'section'          => 'BSA-4A',
                'exam_target_date' => $now->copy()->addDays(78)->toDateString(),
                'total_points'     => 1850,
                'streak_days'      => 14,
            ]
        );

        // ── Per-topic performance (drives readiness, mastery, weaknesses) ─
        // [topic_id, total_attempts, correct_count]
        $perf = [
            [1, 40, 34], [2, 30, 25], [3, 28, 20], [4, 22, 18], [5, 18, 15],   // FAR
            [6, 26, 17], [7, 24, 14], [8, 20, 9],  [9, 16, 11],                // AFAR
            [10, 30, 26], [11, 22, 19], [12, 18, 14],                          // MS
            [14, 24, 11], [15, 20, 13], [16, 18, 8],                           // TAX (weak)
            [18, 26, 22], [19, 22, 18],                                        // AUD
            [22, 20, 17], [23, 18, 13],                                        // RFBT
        ];

        foreach ($perf as [$topicId, $attempts, $correct]) {
            $consecutiveWrong = $correct / max($attempts, 1) < 0.6 ? 3 : 0;
            DB::table('performance_records')->insert([
                'student_id'        => $studentId,
                'topic_id'          => $topicId,
                'total_attempts'    => $attempts,
                'correct_count'     => $correct,
                'consecutive_wrong' => $consecutiveWrong,
                'is_weak_area'      => ($correct / max($attempts, 1)) < 0.6,
                'last_attempted'    => $now->copy()->subDays(rand(0, 10)),
                // accuracy_rate is a GENERATED column - do not insert it.
            ]);
        }

        // ── Recent quiz sessions (drives metrics + recent activity) ───────
        $sessions = [
            ['type' => 'testing',   'subject' => 1,    'items' => 20, 'correct' => 17, 'mins' => 24, 'ago' => 2],
            ['type' => 'training',  'subject' => 4,    'items' => 15, 'correct' => 11, 'mins' => 18, 'ago' => 1],
            ['type' => 'mock_exam', 'subject' => null, 'items' => 70, 'correct' => 52, 'mins' => 90, 'ago' => 3],
            ['type' => 'testing',   'subject' => 3,    'items' => 20, 'correct' => 18, 'mins' => 22, 'ago' => 5],
            ['type' => 'spaced_review', 'subject' => 5, 'items' => 12, 'correct' => 9, 'mins' => 10, 'ago' => 6],
        ];

        foreach ($sessions as $s) {
            $startedAt = $now->copy()->subDays($s['ago'])->setTime(rand(8, 20), rand(0, 59));
            DB::table('quiz_sessions')->insert([
                'student_id'      => $studentId,
                'session_type'    => $s['type'],
                'subject_id'      => $s['subject'],
                'topic_id'        => null,
                'started_at'      => $startedAt,
                'completed_at'    => $startedAt->copy()->addMinutes($s['mins']),
                'total_items'     => $s['items'],
                'correct_answers' => $s['correct'],
                'score_percent'   => round($s['correct'] / $s['items'] * 100, 2),
                'duration_secs'   => $s['mins'] * 60,
            ]);
        }

        // ── Points log ────────────────────────────────────────────────────
        DB::table('points_log')->insert([
            ['student_id' => $studentId, 'points' => 100, 'reason' => 'quiz_completed', 'created_at' => $now->copy()->subDays(2)],
            ['student_id' => $studentId, 'points' => 250, 'reason' => 'mock_exam_completed', 'created_at' => $now->copy()->subDays(3)],
            ['student_id' => $studentId, 'points' => 50,  'reason' => 'daily_streak', 'created_at' => $now->copy()->subDay()],
        ]);

        // ── Badges earned ─────────────────────────────────────────────────
        $badgeIds = DB::table('badges')->whereIn('name', ['First Quiz', 'Week Streak', 'Mock Ready'])->pluck('id');
        foreach ($badgeIds as $badgeId) {
            DB::table('student_badges')->insert([
                'student_id' => $studentId,
                'badge_id'   => $badgeId,
                'earned_at'  => $now->copy()->subDays(rand(1, 12)),
            ]);
        }

        // ── A couple of unread notifications (header bell) ────────────────
        DB::table('notifications')->where('recipient_id', $studentId)->delete();
        DB::table('notifications')->insert([
            ['recipient_id' => $studentId, 'type' => 'review_reminder', 'title' => 'Spaced review due', 'message' => '5 topics are due for review today.', 'is_read' => false, 'created_at' => $now->copy()->subHours(3)],
            ['recipient_id' => $studentId, 'type' => 'score_ready', 'title' => 'Mock exam scored', 'message' => 'Your latest mock exam results are ready.', 'is_read' => false, 'created_at' => $now->copy()->subDay()],
        ]);
    }
}
