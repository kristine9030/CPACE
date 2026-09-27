<?php

namespace App\Support;

use App\Models\MockExamProctorEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Notices when a monitored sitting goes quiet.
 *
 * Every flag is reported by the student's own browser, so a missing flag is
 * ambiguous: the student behaved, or the page stopped being able to report.
 * The runner therefore sends a small heartbeat; any signal from the page
 * (heartbeat, flag or frame) refreshes last_seen_at, and when one arrives after
 * a long silence the gap is written to the record as a connection_gap flag.
 *
 * It catches a blocked request, a frozen or closed page, a sleeping laptop and
 * a dropped connection. It cannot stop someone who forges heartbeats, and it
 * is described that way to reviewers.
 */
class ProctorHeartbeat
{
    /**
     * Silence longer than this counts as a gap. Deliberately generous: Chrome
     * slows timers in a background tab to about once a minute, and that must
     * not be read as absence.
     */
    public const GAP_SECONDS = 150;

    /**
     * Record that the page is alive and, if it was silent too long, note the gap.
     *
     * @param  Model  $attempt   a MockExamAttempt or FacultyQuizAttempt
     * @param  string  $eventClass  the matching proctor event model
     */
    public static function touch(Model $attempt, string $eventClass): void
    {
        $now = now();
        $last = $attempt->last_seen_at ?? $attempt->started_at;
        $silent = $last ? max(0, $now->timestamp - $last->timestamp) : 0;

        DB::transaction(function () use ($attempt, $eventClass, $now, $silent) {
            if ($silent > self::GAP_SECONDS) {
                $eventClass::create([
                    'attempt_id' => $attempt->id,
                    'type' => MockExamProctorEvent::TYPE_CONNECTION_GAP,
                    'occurred_at' => $now,
                    'meta' => 'No signal for ' . self::duration($silent),
                ]);
                $attempt->increment('flag_count');
            }

            $attempt->newQuery()->whereKey($attempt->id)->update(['last_seen_at' => $now]);
        });

        $attempt->last_seen_at = $now;
    }

    /** "4m 12s", "1h 03m". */
    public static function duration(int $seconds): string
    {
        if ($seconds >= 3600) {
            return intdiv($seconds, 3600) . 'h ' . str_pad((string) intdiv($seconds % 3600, 60), 2, '0', STR_PAD_LEFT) . 'm';
        }

        return $seconds >= 60 ? intdiv($seconds, 60) . 'm ' . ($seconds % 60) . 's' : $seconds . 's';
    }

    /**
     * How long an unfinished sitting has been silent, for the monitor. Null
     * when there is nothing to report (already submitted).
     */
    public static function silentFor(Model $attempt): ?int
    {
        if ($attempt->submitted_at) {
            return null;
        }

        $last = $attempt->last_seen_at ?? $attempt->started_at;

        return $last ? max(0, now()->timestamp - $last->timestamp) : null;
    }
}
