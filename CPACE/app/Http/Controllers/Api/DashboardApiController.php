<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StreakService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function index()
    {
        $studentId = Auth::id();

        $profile    = DB::table('student_profiles')->where('user_id', $studentId)->first();
        $streak     = app(StreakService::class)->current($studentId);
        $points     = (int) ($profile->total_points ?? 0);
        $examDate   = $profile->exam_target_date ?? null;
        $daysToExam = $examDate
            ? max(0, (int) ceil((Carbon::parse($examDate)->startOfDay()->timestamp - Carbon::now()->startOfDay()->timestamp) / 86400))
            : null;

        $base = fn () => DB::table('quiz_sessions')
            ->where('student_id', $studentId)
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at');

        $questionsAttempted = (int) $base()->sum('total_items');
        $questionsThisWeek  = (int) $base()->where('started_at', '>=', Carbon::now()->subDays(7))->sum('total_items');

        $studySeconds     = (int) DB::table('quiz_sessions')->where('student_id', $studentId)->where('session_type', '!=', 'training')->sum('duration_secs');
        $studySecondsWeek = (int) DB::table('quiz_sessions')->where('student_id', $studentId)->where('session_type', '!=', 'training')->where('started_at', '>=', Carbon::now()->subDays(7))->sum('duration_secs');

        $agg = DB::table('performance_records')
            ->where('student_id', $studentId)
            ->selectRaw('COALESCE(SUM(correct_count),0) c, COALESCE(SUM(total_attempts),0) t')
            ->first();
        $readiness = ($agg && $agg->t > 0) ? (int) round($agg->c / $agg->t * 100) : 0;

        $subjectMastery = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('performance_records', function ($join) use ($studentId) {
                $join->on('performance_records.topic_id', '=', 'topics.id')
                     ->where('performance_records.student_id', '=', $studentId);
            })
            ->groupBy('subjects.id', 'subjects.code', 'subjects.name', 'subjects.color')
            ->orderBy('subjects.id')
            ->select(
                'subjects.id',
                'subjects.code',
                'subjects.name',
                'subjects.color',
                DB::raw('COALESCE(SUM(performance_records.correct_count),0) as correct'),
                DB::raw('COALESCE(SUM(performance_records.total_attempts),0) as attempts')
            )
            ->get()
            ->map(function ($row) {
                return [
                    'id'      => $row->id,
                    'code'    => $row->code,
                    'name'    => $row->name,
                    'color'   => $row->color,
                    'mastery' => $row->attempts > 0 ? (int) round($row->correct / $row->attempts * 100) : 0,
                ];
            });

        $weaknesses = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->where('performance_records.student_id', $studentId)
            ->where('performance_records.total_attempts', '>', 0)
            ->orderBy('performance_records.accuracy_rate', 'asc')
            ->orderByDesc('performance_records.total_attempts')
            ->limit(3)
            ->select('topics.name as topic', 'subjects.code as subject_code', 'performance_records.accuracy_rate')
            ->get();

        $recentActivity = DB::table('quiz_sessions')
            ->leftJoin('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->where('quiz_sessions.student_id', $studentId)
            ->where('quiz_sessions.session_type', '!=', 'training')
            ->orderByDesc('quiz_sessions.started_at')
            ->limit(5)
            ->select(
                'quiz_sessions.id',
                'quiz_sessions.mode',
                'quiz_sessions.session_type',
                'quiz_sessions.total_items',
                'quiz_sessions.correct_answers',
                'quiz_sessions.score_percent',
                'quiz_sessions.started_at',
                'quiz_sessions.completed_at',
                'subjects.code as subject_code'
            )
            ->get();

        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $studentId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'streak'               => $streak,
            'points'               => $points,
            'days_to_exam'         => $daysToExam,
            'questions_attempted'  => $questionsAttempted,
            'questions_this_week'  => $questionsThisWeek,
            'study_hours'          => (int) round($studySeconds / 3600),
            'study_hours_week'     => (int) round($studySecondsWeek / 3600),
            'readiness'            => $readiness,
            'subject_mastery'      => $subjectMastery,
            'weaknesses'           => $weaknesses,
            'recent_activity'      => $recentActivity,
            'unread_notifications' => $unreadNotifications,
        ]);
    }
}
