<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Student dashboard - all figures are pulled live from the database.
     */
    public function index()
    {
        $user      = Auth::user();
        $studentId = $user->id;

        // ── Gamification / profile (streak, points, exam target) ──────────
        $profile = DB::table('student_profiles')->where('user_id', $studentId)->first();

        // Computed live so the day-streak card is always current - it reflects
        // a broken streak immediately, not only after the next quiz.
        $streak    = app(StreakService::class)->current($studentId);
        $points    = (int) ($profile->total_points ?? 0);
        $examDate  = $profile->exam_target_date ?? null;
        $daysToExam = $examDate
            ? max(0, (int) ceil((Carbon::parse($examDate)->startOfDay()->timestamp - Carbon::now()->startOfDay()->timestamp) / 86400))
            : null;

        // ── Questions attempted (every question served in completed quizzes) ──
        // Counts all served questions (total_items) so a skipped question still
        // counts as attempted. This matches the Performance and Quizzes pages.
        // Training is a no-stakes practice mode, so it is excluded from every
        // figure on the dashboard (it is still saved for the results review).
        $attemptedBase = fn() => DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at');

        $questionsAttempted = (int) $attemptedBase()->sum('total_items');

        $questionsThisWeek = (int) $attemptedBase()
            ->where('started_at', '>=', Carbon::now()->subDays(7))
            ->sum('total_items');

        // ── Study time ────────────────────────────────────────────────────
        $studySeconds     = (int) DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->sum('duration_secs');
        $studySecondsWeek  = (int) DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->where('started_at', '>=', Carbon::now()->subDays(7))
            ->sum('duration_secs');
        $studyHours     = (int) round($studySeconds / 3600);
        $studyHoursWeek = (int) round($studySecondsWeek / 3600);

        // ── Board readiness = overall accuracy ────────────────────────────
        // Uses the SAME source as the Performance page (completed, non-training
        // quiz_sessions: correct_answers / total_items) so the two pages always
        // agree.
        $agg = $attemptedBase()
            ->selectRaw('COALESCE(SUM(correct_answers),0) c, COALESCE(SUM(total_items),0) t')
            ->first();
        $readiness = ($agg && $agg->t > 0) ? (int) round($agg->c / $agg->t * 100) : 0;

        // ── Subject mastery (every subject, 0% when no attempts yet) ──────
        $subjectMastery = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('performance_records', function ($join) use ($studentId) {
                $join->on('performance_records.topic_id', '=', 'topics.id')
                    ->where('performance_records.student_id', '=', $studentId);
            })
            ->groupBy('subjects.id', 'subjects.code', 'subjects.name')
            ->orderBy('subjects.id')
            ->select(
                'subjects.id',
                'subjects.code',
                'subjects.name',
                DB::raw('COALESCE(SUM(performance_records.correct_count),0) as correct'),
                DB::raw('COALESCE(SUM(performance_records.total_attempts),0) as attempts')
            )
            ->get()
            ->map(function ($row) {
                $row->mastery = $row->attempts > 0 ? (int) round($row->correct / $row->attempts * 100) : 0;
                return $row;
            });

        // ── Top weaknesses (lowest accuracy topics with attempts) ─────────
        $weaknesses = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->where('performance_records.student_id', $studentId)
            ->where('performance_records.total_attempts', '>', 0)
            ->orderBy('performance_records.accuracy_rate', 'asc')
            ->orderByDesc('performance_records.total_attempts')
            ->limit(3)
            ->select(
                'topics.name as topic',
                'subjects.code as subject_code',
                'subjects.name as subject_name',
                'performance_records.accuracy_rate'
            )
            ->get();

        // ── Recent activity (latest sessions) ─────────────────────────────
        $recentActivity = DB::table('quiz_sessions')
            ->leftJoin('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->where('quiz_sessions.student_id', $studentId)
            ->where('quiz_sessions.session_type', '!=', 'training')
            ->orderByDesc('quiz_sessions.started_at')
            ->limit(5)
            ->select(
                'quiz_sessions.id',
                'quiz_sessions.session_type',
                'quiz_sessions.total_items',
                'quiz_sessions.score_percent',
                'quiz_sessions.started_at',
                'quiz_sessions.completed_at',
                'subjects.code as subject_code'
            )
            ->get();

        // ── Unread notifications (header bell) ────────────────────────────
        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $studentId)
            ->where('is_read', false)
            ->count();

        return view('student.dashboard', compact(
            'streak',
            'points',
            'daysToExam',
            'questionsAttempted',
            'questionsThisWeek',
            'studyHours',
            'studyHoursWeek',
            'readiness',
            'subjectMastery',
            'weaknesses',
            'recentActivity',
            'unreadNotifications'
        ));
    }
}
