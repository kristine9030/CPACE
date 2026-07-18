<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChairAnalyticsService
{
    public const READY_ACCURACY = 60;
    public const READY_ATTEMPTS = 50;
    public const READY_SUBJECTS = 3;
    public const DEVELOPING_ACCURACY = 50;
    public const DEVELOPING_ATTEMPTS = 20;
    public const COVERAGE_TARGET = 25;

    public function dashboardSummary(): array
    {
        $subjects = $this->subjectPerformance();
        $readiness = $this->readinessSummary();
        $coverage = $this->coverageReport();
        $trend = $this->readinessTrend(8);
        $last = $trend->last();
        $previous = $trend->count() > 1 ? $trend->slice(-2, 1)->first() : null;

        return [
            'class_accuracy' => $subjects->sum('attempts') > 0
                ? (int) round($subjects->sum('correct') / $subjects->sum('attempts') * 100)
                : null,
            'weakest_subject' => $subjects->where('attempts', '>', 0)->sortBy('accuracy')->first(),
            'readiness_rate' => $readiness['readiness_rate'],
            'readiness_change' => $last && $previous
                ? $last['rate'] - $previous['rate']
                : null,
            'thin_topics' => $coverage->whereIn('status', ['critical', 'thin'])->count(),
            'pass_projection' => $readiness['pass_projection'],
            'eligible_students' => $readiness['eligible'],
        ];
    }

    public function performanceReport(?int $subjectId = null): array
    {
        $subjects = $this->subjectPerformance($subjectId);
        $readiness = $this->readinessSummary($subjectId);

        return [
            'subjects' => $subjects,
            'overall_accuracy' => $subjects->sum('attempts') > 0
                ? (int) round($subjects->sum('correct') / $subjects->sum('attempts') * 100)
                : null,
            'total_attempts' => (int) $subjects->sum('attempts'),
            'participating_students' => $this->participatingStudentCount($subjectId),
            'readiness' => $readiness,
            'trend' => $this->readinessTrend(8, $subjectId),
        ];
    }

    public function subjectPerformance(?int $subjectId = null): Collection
    {
        $metrics = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->groupBy('topics.subject_id')
            ->select(
                'topics.subject_id',
                DB::raw('SUM(performance_records.correct_count) as correct'),
                DB::raw('SUM(performance_records.total_attempts) as attempts'),
                DB::raw('COUNT(DISTINCT performance_records.student_id) as students')
            )
            ->get()
            ->keyBy('subject_id');

        return DB::table('subjects')
            ->when($subjectId, fn ($query) => $query->where('id', $subjectId))
            ->orderBy('id')
            ->get()
            ->map(function ($subject) use ($metrics) {
                $metric = $metrics->get($subject->id);
                $attempts = (int) ($metric->attempts ?? 0);
                $correct = (int) ($metric->correct ?? 0);

                return [
                    'id' => (int) $subject->id,
                    'code' => $subject->code,
                    'name' => $subject->name,
                    'correct' => $correct,
                    'attempts' => $attempts,
                    'students' => (int) ($metric->students ?? 0),
                    'accuracy' => $attempts > 0 ? (int) round($correct / $attempts * 100) : null,
                ];
            });
    }

    public function readinessSummary(?int $subjectId = null): array
    {
        $rows = $this->studentReadinessRows($subjectId);
        $eligible = $rows->where('attempts', '>=', self::DEVELOPING_ATTEMPTS);
        $ready = $eligible->where('band', 'ready')->count();
        $developing = $eligible->where('band', 'developing')->count();
        $atRisk = $eligible->where('band', 'at_risk')->count();
        $eligibleCount = $eligible->count();

        return [
            'ready' => $ready,
            'developing' => $developing,
            'at_risk' => $atRisk,
            'eligible' => $eligibleCount,
            'insufficient' => $rows->count() - $eligibleCount,
            'readiness_rate' => $eligibleCount ? (int) round($ready / $eligibleCount * 100) : null,
            'pass_projection' => $eligibleCount
                ? (int) round(($ready + ($developing * .5)) / $eligibleCount * 100)
                : null,
        ];
    }

    public function readinessTrend(int $weeks = 8, ?int $subjectId = null): Collection
    {
        $sessions = DB::table('quiz_sessions')
            ->whereNotNull('completed_at')
            ->where('session_type', '!=', 'training')
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->select('student_id', 'subject_id', 'total_items', 'correct_answers', 'completed_at')
            ->get();

        return collect(range($weeks - 1, 0))->map(function ($offset) use ($sessions, $subjectId) {
            $cutoff = now()->startOfWeek()->subWeeks($offset)->endOfWeek();
            $eligible = $sessions
                ->filter(fn ($session) => Carbon::parse($session->completed_at)->lte($cutoff))
                ->groupBy('student_id')
                ->map(function ($studentSessions) use ($subjectId) {
                    $attempts = (int) $studentSessions->sum('total_items');
                    $correct = (int) $studentSessions->sum('correct_answers');
                    $subjects = $studentSessions->pluck('subject_id')->filter()->unique()->count();
                    $accuracy = $attempts ? (int) round($correct / $attempts * 100) : 0;

                    return [
                        'attempts' => $attempts,
                        'ready' => $attempts >= self::READY_ATTEMPTS
                            && $accuracy >= self::READY_ACCURACY
                            && ($subjectId || $subjects >= self::READY_SUBJECTS),
                    ];
                })
                ->filter(fn ($row) => $row['attempts'] >= self::DEVELOPING_ATTEMPTS);

            $ready = $eligible->where('ready', true)->count();

            return [
                'label' => $cutoff->format('M j'),
                'rate' => $eligible->count() ? (int) round($ready / $eligible->count() * 100) : 0,
                'eligible' => $eligible->count(),
                'ready' => $ready,
            ];
        });
    }

    public function coverageReport(?int $subjectId = null): Collection
    {
        $counts = DB::table('questions')
            ->select(
                'topic_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw("SUM(CASE WHEN LOWER(difficulty) = 'easy' THEN 1 ELSE 0 END) as easy"),
                DB::raw("SUM(CASE WHEN LOWER(difficulty) IN ('medium','moderate') THEN 1 ELSE 0 END) as moderate"),
                DB::raw("SUM(CASE WHEN LOWER(difficulty) IN ('hard','difficult') THEN 1 ELSE 0 END) as difficult"),
                DB::raw('MAX(created_at) as last_added')
            )
            ->groupBy('topic_id')
            ->get()
            ->keyBy('topic_id');

        return DB::table('topics')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->when($subjectId, fn ($query) => $query->where('subjects.id', $subjectId))
            ->where('topics.is_active', true)
            ->orderBy('subjects.id')
            ->orderBy('topics.sort_order')
            ->select('topics.id', 'topics.name', 'subjects.id as subject_id', 'subjects.code', 'subjects.name as subject_name')
            ->get()
            ->map(function ($topic) use ($counts) {
                $count = $counts->get($topic->id);
                $active = (int) ($count->active ?? 0);
                $status = $active === 0 ? 'critical' : ($active < self::COVERAGE_TARGET ? 'thin' : 'adequate');

                return [
                    'id' => (int) $topic->id,
                    'name' => $topic->name,
                    'subject_id' => (int) $topic->subject_id,
                    'subject_code' => $topic->code,
                    'subject_name' => $topic->subject_name,
                    'total' => (int) ($count->total ?? 0),
                    'active' => $active,
                    'easy' => (int) ($count->easy ?? 0),
                    'moderate' => (int) ($count->moderate ?? 0),
                    'difficult' => (int) ($count->difficult ?? 0),
                    'last_added' => $count?->last_added ? Carbon::parse($count->last_added) : null,
                    'status' => $status,
                    'gap' => max(0, self::COVERAGE_TARGET - $active),
                ];
            });
    }

    private function studentReadinessRows(?int $subjectId = null): Collection
    {
        return DB::table('users')
            ->leftJoin('quiz_sessions', function ($join) use ($subjectId) {
                $join->on('quiz_sessions.student_id', '=', 'users.id')
                    ->whereNotNull('quiz_sessions.completed_at')
                    ->where('quiz_sessions.session_type', '!=', 'training');
                if ($subjectId) {
                    $join->where('quiz_sessions.subject_id', '=', $subjectId);
                }
            })
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->groupBy('users.id')
            ->select(
                'users.id',
                DB::raw('COALESCE(SUM(quiz_sessions.total_items), 0) as attempts'),
                DB::raw('COALESCE(SUM(quiz_sessions.correct_answers), 0) as correct'),
                DB::raw('COUNT(DISTINCT quiz_sessions.subject_id) as subjects')
            )
            ->get()
            ->map(function ($row) use ($subjectId) {
                $attempts = (int) $row->attempts;
                $accuracy = $attempts ? (int) round((int) $row->correct / $attempts * 100) : 0;
                $ready = $attempts >= self::READY_ATTEMPTS
                    && $accuracy >= self::READY_ACCURACY
                    && ($subjectId || (int) $row->subjects >= self::READY_SUBJECTS);

                return [
                    'attempts' => $attempts,
                    'accuracy' => $accuracy,
                    'band' => $ready
                        ? 'ready'
                        : (($attempts >= self::DEVELOPING_ATTEMPTS && $accuracy >= self::DEVELOPING_ACCURACY) ? 'developing' : 'at_risk'),
                ];
            });
    }

    private function participatingStudentCount(?int $subjectId = null): int
    {
        return DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->where('performance_records.total_attempts', '>', 0)
            ->when($subjectId, fn ($query) => $query->where('topics.subject_id', $subjectId))
            ->distinct('performance_records.student_id')
            ->count('performance_records.student_id');
    }
}
