<?php

namespace App\Support;

use App\Models\MockExamProctorEvent;
use Illuminate\Support\Facades\DB;

/**
 * Turns a sitting's raw flags into one weighted score and a Low / Medium / High
 * level, so a room of 40 students can be triaged at a glance.
 *
 * Computed on demand from mock_exam_proctor_events (no stored column), so the
 * weights can be tuned later without touching any data.
 *
 * A score is a signal for a human to look at, never a verdict: it is shown to
 * faculty and the Program Chair only, and no rule anywhere acts on it.
 */
class ProctorRisk
{
    /** Points per occurrence. Deliberate acts weigh more than a single lapse. */
    public const WEIGHTS = [
        MockExamProctorEvent::TYPE_SCREEN_LOST => 5,
        MockExamProctorEvent::TYPE_CAMERA_LOST => 5,
        MockExamProctorEvent::TYPE_MULTIPLE_FACES => 4,
        MockExamProctorEvent::TYPE_PARTIAL_SCREEN => 4,
        MockExamProctorEvent::TYPE_SECOND_MONITOR => 3,
        MockExamProctorEvent::TYPE_BLUR => 2,
        MockExamProctorEvent::TYPE_HIDDEN => 2,
        MockExamProctorEvent::TYPE_NO_FACE => 2,
        MockExamProctorEvent::TYPE_LOOKING_AWAY => 1,
        MockExamProctorEvent::TYPE_FULLSCREEN_EXIT => 1,
        MockExamProctorEvent::TYPE_PASTE_BLOCKED => 1,
    ];

    /** A type stops adding points after this many occurrences, so 50 glances away can't bury one serious flag. */
    public const CAP_PER_TYPE = 5;

    public const LEVEL_NONE = 'none';
    public const LEVEL_LOW = 'low';
    public const LEVEL_MEDIUM = 'medium';
    public const LEVEL_HIGH = 'high';

    /** Lowest score for each level. */
    public const MEDIUM_FROM = 5;
    public const HIGH_FROM = 12;

    /**
     * @param  array<string, int>  $counts  flag type => how many times
     * @return array{score: int, level: string, label: string, rows: array<int, array{type: string, label: string, count: int, counted: int, points: int}>}
     */
    public static function assess(array $counts): array
    {
        $score = 0;
        $rows = [];

        foreach (self::WEIGHTS as $type => $weight) {
            $count = (int) ($counts[$type] ?? 0);
            if ($count === 0) {
                continue;
            }

            $counted = min($count, self::CAP_PER_TYPE);
            $points = $counted * $weight;
            $score += $points;
            $rows[] = [
                'type' => $type,
                'label' => (new MockExamProctorEvent(['type' => $type]))->label(),
                'count' => $count,
                'counted' => $counted,
                'points' => $points,
            ];
        }

        usort($rows, fn ($a, $b) => $b['points'] <=> $a['points']);
        $level = self::level($score);

        return ['score' => $score, 'level' => $level, 'label' => self::label($level), 'rows' => $rows];
    }

    public static function level(int $score): string
    {
        return match (true) {
            $score >= self::HIGH_FROM => self::LEVEL_HIGH,
            $score >= self::MEDIUM_FROM => self::LEVEL_MEDIUM,
            $score > 0 => self::LEVEL_LOW,
            default => self::LEVEL_NONE,
        };
    }

    public static function label(string $level): string
    {
        return match ($level) {
            self::LEVEL_HIGH => 'High risk',
            self::LEVEL_MEDIUM => 'Medium risk',
            self::LEVEL_LOW => 'Low risk',
            default => 'No flags',
        };
    }

    /**
     * Flag counts for many attempts in a single query.
     *
     * @param  iterable<int>  $attemptIds
     * @return array<int, array<string, int>>  attempt id => [type => count]
     */
    public static function countsFor(iterable $attemptIds): array
    {
        $ids = collect($attemptIds)->all();
        if ($ids === []) {
            return [];
        }

        $out = [];
        $rows = DB::table('mock_exam_proctor_events')
            ->whereIn('attempt_id', $ids)
            ->selectRaw('attempt_id, type, count(*) as c')
            ->groupBy('attempt_id', 'type')
            ->get();

        foreach ($rows as $row) {
            $out[$row->attempt_id][$row->type] = (int) $row->c;
        }

        return $out;
    }
}
