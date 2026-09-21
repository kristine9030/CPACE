<?php

namespace App\Support;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use App\Models\User;

/**
 * Single choke point for writing a mock exam's audit trail.
 *
 * Faculty collaborate on the same exam and the Program Chair edits it during
 * review, so every mutation has to be attributable afterwards. Routing all of
 * them through here keeps the wording consistent and means a new edit path
 * can't quietly forget to log.
 */
class MockExamAuditor
{
    public static function record(MockExam $exam, ?User $user, string $action, ?string $details = null): MockExamAudit
    {
        return MockExamAudit::create([
            'exam_id' => $exam->id,
            'user_id' => $user?->id,
            'action' => $action,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    /**
     * Describe an item-list change in the terms a reviewer actually cares
     * about ("Questions: 70 -> 75 (added 8, removed 3)") rather than dumping
     * a diff nobody will read.
     */
    public static function describeItemChange(int $before, int $after, int $added, int $removed): string
    {
        $summary = "Questions: {$before} → {$after}";
        $parts = [];
        if ($added > 0) {
            $parts[] = "added {$added}";
        }
        if ($removed > 0) {
            $parts[] = "removed {$removed}";
        }

        return $parts === [] ? $summary : $summary . ' (' . implode(', ', $parts) . ')';
    }

    /**
     * Summarise which settings actually moved, so the trail reads as
     * "Schedule, Duration" instead of restating every field every save.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public static function describeSettingsChange(array $before, array $after): ?string
    {
        $labels = [
            'title' => 'Title',
            'scheduled_at' => 'Schedule',
            'duration_minutes' => 'Duration',
        ];

        $changed = [];
        foreach ($labels as $key => $label) {
            $was = $before[$key] ?? null;
            $now = $after[$key] ?? null;
            if ((string) $was !== (string) $now) {
                $changed[] = $label;
            }
        }

        return $changed === [] ? null : 'Changed: ' . implode(', ', $changed);
    }
}
