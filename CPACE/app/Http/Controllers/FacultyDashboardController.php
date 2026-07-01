<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Subject;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Faculty landing dashboard. Every figure, list and bar on the page is computed
 * live from the database - questions / quiz_sessions / performance_records - and
 * scoped to the CPALE subjects the Program Chair has assigned to the signed-in
 * faculty member (falling back to every subject for a brand-new account so the
 * page is never empty). Nothing here is hard-coded.
 */
class FacultyDashboardController extends Controller
{
    /** Difficulty enum -> human label used across the UI. */
    private const DIFFICULTY_LABELS = [
        'easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard',
    ];

    /** Subject brand colours reused for the "Questions by Subject" bars. */
    private const SUBJECT_COLORS = [
        'FAR'  => '#3b82f6',
        'AFAR' => '#17a2b8',
        'MS'   => '#8b5cf6',
        'TAX'  => '#27ae60',
        'AUD'  => '#e8567d',
        'RFBT' => '#f59e0b',
    ];

    public function index(Request $request)
    {
        // Subjects assigned to this faculty; fall back to all subjects so a
        // freshly-created account still sees the whole picture.
        $assigned = Auth::user()->assignedSubjects()->orderBy('subjects.id')->get();
        if ($assigned->isEmpty()) {
            $assigned = Subject::orderBy('id')->get();
        }
        $subjectIds = $assigned->pluck('id')->all();

        $now         = Carbon::now();
        $weekAgo     = $now->copy()->subDays(7);
        $monthAgo    = $now->copy()->subDays(30);
        $twoMonthAgo = $now->copy()->subDays(60);

        return view('faculty.dashboard', [
            'assigned'        => $assigned,
            'stats'           => $this->headlineStats($subjectIds, $weekAgo, $monthAgo, $twoMonthAgo),
            'recentQuestions' => $this->recentQuestions($subjectIds),
            'recentActivity'  => $this->recentActivity($subjectIds),
            'bySubject'       => $this->questionsBySubject($assigned),
            'byType'          => $this->questionsByType($subjectIds),
            'byDifficulty'    => $this->questionsByDifficulty($subjectIds),
            'topStudents'     => $this->topStudents($subjectIds),
        ]);
    }

    /**
     * Base query for the questions that belong to the faculty's scoped subjects.
     */
    private function scopedQuestions(array $subjectIds)
    {
        return DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereIn('topics.subject_id', $subjectIds);
    }

    /**
     * Base query for completed, graded (non-training) sessions in scope.
     */
    private function scopedSessions(array $subjectIds)
    {
        return DB::table('quiz_sessions')
            ->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')
            ->whereIn('subject_id', $subjectIds);
    }

    /**
     * The four headline cards, each with a real delta.
     */
    private function headlineStats(array $subjectIds, Carbon $weekAgo, Carbon $monthAgo, Carbon $twoMonthAgo): array
    {
        $totalQuestions = (clone $this->scopedQuestions($subjectIds))->count();
        $addedThisWeek  = (clone $this->scopedQuestions($subjectIds))
            ->where('questions.created_at', '>=', $weekAgo)->count();

        // Active students: distinct students with graded activity in scope.
        $activeStudents = (clone $this->scopedSessions($subjectIds))
            ->distinct()->count('student_id');

        // "New this month": students whose earliest graded session in scope is
        // within the last 30 days.
        $firsts = $this->scopedSessions($subjectIds)
            ->select('student_id', DB::raw('MIN(started_at) as first_seen'))
            ->groupBy('student_id');

        $newThisMonth = DB::query()
            ->fromSub($firsts, 'firsts')
            ->where('first_seen', '>=', $monthAgo)
            ->count();

        // Average student score this month vs the previous month.
        $avgNow  = $this->avgScore($subjectIds, $monthAgo, null);
        $avgPrev = $this->avgScore($subjectIds, $twoMonthAgo, $monthAgo);
        $avgAll  = $this->avgScore($subjectIds, null, null);
        $avgDelta = ($avgNow !== null && $avgPrev !== null) ? $avgNow - $avgPrev : null;

        return [
            'total_questions'  => $totalQuestions,
            'added_this_week'  => $addedThisWeek,
            'active_students'  => $activeStudents,
            'new_this_month'   => $newThisMonth,
            'avg_score'        => $avgAll ?? 0,
            'avg_delta'        => $avgDelta,
        ];
    }

    /**
     * Average completed-session score (%) in scope over an optional window.
     */
    private function avgScore(array $subjectIds, ?Carbon $from, ?Carbon $to): ?int
    {
        $agg = $this->scopedSessions($subjectIds)
            ->when($from, fn ($q) => $q->where('started_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('started_at', '<', $to))
            ->selectRaw('COALESCE(SUM(total_items),0) as attempted, COALESCE(SUM(correct_answers),0) as correct')
            ->first();

        $attempted = (int) ($agg->attempted ?? 0);
        if ($attempted === 0) {
            return null;
        }

        return (int) round(((int) $agg->correct) / $attempted * 100);
    }

    /**
     * The most recently added questions in scope (for the left table).
     */
    private function recentQuestions(array $subjectIds)
    {
        return $this->scopedQuestions($subjectIds)
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->orderByDesc('questions.id')
            ->limit(5)
            ->select(
                'questions.id',
                'questions.question_text',
                'questions.question_type',
                'questions.difficulty',
                'questions.is_active',
                'questions.created_at',
                'subjects.code as subject_code'
            )
            ->get()
            ->map(fn ($q) => [
                'id'         => (int) $q->id,
                'text'       => $q->question_text,
                'type_label' => $q->question_type === 'mcq' ? 'Multiple Choice' : 'True / False',
                'difficulty' => self::DIFFICULTY_LABELS[$q->difficulty] ?? ucfirst($q->difficulty),
                'active'     => (bool) $q->is_active,
                'subject'    => $q->subject_code,
                'ago'        => $q->created_at ? Carbon::parse($q->created_at)->diffForHumans() : '',
            ]);
    }

    /**
     * Recent completed quiz sessions in scope, as an activity feed.
     */
    private function recentActivity(array $subjectIds)
    {
        $sessions = $this->scopedSessions($subjectIds)
            ->join('users', 'users.id', '=', 'quiz_sessions.student_id')
            ->join('subjects', 'subjects.id', '=', 'quiz_sessions.subject_id')
            ->where('users.role_id', Role::STUDENT)
            ->orderByDesc('quiz_sessions.completed_at')
            ->limit(6)
            ->select(
                'users.first_name',
                'users.last_name',
                'quiz_sessions.session_type',
                'quiz_sessions.score_percent',
                'quiz_sessions.completed_at',
                'subjects.code as subject_code'
            )
            ->get();

        return $sessions->map(function ($s) {
            $score = $s->score_percent !== null ? (int) round($s->score_percent) : null;
            $name  = trim("{$s->first_name} {$s->last_name}");

            // Icon / tone driven by the achieved score.
            if ($score === null) {
                $tone = ['bg' => '#f1f5f9', 'fg' => '#64748b', 'icon' => 'fa-hourglass-half'];
            } elseif ($score < 50) {
                $tone = ['bg' => '#fde8e8', 'fg' => '#c0392b', 'icon' => 'fa-exclamation-circle'];
            } elseif ($score < 75) {
                $tone = ['bg' => '#dbeafe', 'fg' => '#2563eb', 'icon' => 'fa-brain'];
            } else {
                $tone = ['bg' => '#d1fae5', 'fg' => '#059669', 'icon' => 'fa-check-circle'];
            }

            $typeLabel = match ($s->session_type) {
                'mock_exam'     => 'Mock Exam',
                'spaced_review' => 'Spaced Review',
                'testing'       => 'Quiz',
                default         => ucfirst(str_replace('_', ' ', $s->session_type)),
            };

            return [
                'name'    => $name,
                'detail'  => "{$s->subject_code} {$typeLabel}" . ($score !== null ? " &bull; Score: {$score}%" : ''),
                'ago'     => $s->completed_at ? Carbon::parse($s->completed_at)->diffForHumans() : '',
                'tone'    => $tone,
            ];
        });
    }

    /**
     * Question count per assigned subject, with a bar width relative to the
     * busiest subject.
     */
    private function questionsBySubject($assigned)
    {
        $counts = DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereIn('topics.subject_id', $assigned->pluck('id')->all())
            ->groupBy('topics.subject_id')
            ->select('topics.subject_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'subject_id');

        $max = max(1, (int) ($counts->max() ?? 0));

        return $assigned->map(fn ($s) => [
            'code'  => $s->code,
            'total' => (int) ($counts[$s->id] ?? 0),
            'width' => (int) round(((int) ($counts[$s->id] ?? 0)) / $max * 100),
            'color' => self::SUBJECT_COLORS[$s->code] ?? '#7B1D1D',
        ])->sortByDesc('total')->values();
    }

    /**
     * Multiple-choice vs true/false split in scope.
     */
    private function questionsByType(array $subjectIds): array
    {
        $rows = (clone $this->scopedQuestions($subjectIds))
            ->groupBy('questions.question_type')
            ->select('questions.question_type', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'question_type');

        $mcq   = (int) ($rows['mcq'] ?? 0);
        $tf    = (int) ($rows['true_false'] ?? 0);
        $total = max(1, $mcq + $tf);

        return [
            'total' => $mcq + $tf,
            'mcq'   => ['count' => $mcq, 'pct' => round($mcq / $total * 100, 1)],
            'tf'    => ['count' => $tf,  'pct' => round($tf / $total * 100, 1)],
        ];
    }

    /**
     * Easy / Medium / Hard split in scope.
     */
    private function questionsByDifficulty(array $subjectIds): array
    {
        $rows = (clone $this->scopedQuestions($subjectIds))
            ->groupBy('questions.difficulty')
            ->select('questions.difficulty', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'difficulty');

        $easy   = (int) ($rows['easy'] ?? 0);
        $medium = (int) ($rows['moderate'] ?? 0);
        $hard   = (int) ($rows['difficult'] ?? 0);
        $total  = max(1, $easy + $medium + $hard);

        return [
            'easy'   => ['count' => $easy,   'pct' => round($easy / $total * 100, 1)],
            'medium' => ['count' => $medium, 'pct' => round($medium / $total * 100, 1)],
            'hard'   => ['count' => $hard,   'pct' => round($hard / $total * 100, 1)],
        ];
    }

    /**
     * Top students by average completed-session score in scope (min. sample so a
     * single lucky quiz can't top the board).
     */
    private function topStudents(array $subjectIds)
    {
        $agg = $this->scopedSessions($subjectIds)
            ->join('users', 'users.id', '=', 'quiz_sessions.student_id')
            ->where('users.role_id', Role::STUDENT)
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->havingRaw('SUM(total_items) >= ?', [WeaknessDetector::MIN_ATTEMPTS])
            ->select(
                'users.first_name',
                'users.last_name',
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct')
            )
            ->get();

        return $agg->map(fn ($r) => [
            'name'  => trim("{$r->first_name} {$r->last_name}"),
            'score' => (int) $r->attempted > 0 ? (int) round($r->correct / $r->attempted * 100) : 0,
        ])->sortByDesc('score')->take(3)->values();
    }
}
