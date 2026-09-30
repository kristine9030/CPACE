<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Splits a date range into chart buckets: days for up to two weeks, weeks up
 * to ~4 months, months beyond that — enough points to show a shape without
 * crowding the x-axis. Shared by the chair and faculty dashboards so a range
 * is bucketed the same way on both.
 */
class PeriodBuckets
{
    /**
     * @return Collection<int, array{unit: string, label: string, start: Carbon, end: Carbon}>
     */
    public static function for(Carbon $from, Carbon $to): Collection
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $days = (int) $from->diffInDays($to) + 1;
        [$unit, $format] = match (true) {
            $days <= 14 => ['day', 'M j'],
            $days <= 120 => ['week', 'M j'],
            default => ['month', 'M Y'],
        };

        $buckets = collect();
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $end = match ($unit) {
                'day' => $cursor->copy()->endOfDay(),
                'week' => $cursor->copy()->addDays(6)->endOfDay(),
                default => $cursor->copy()->endOfMonth(),
            };
            if ($end->gt($to)) {
                $end = $to->copy();
            }

            $buckets->push(['unit' => $unit, 'label' => $cursor->format($format), 'start' => $cursor->copy(), 'end' => $end]);
            $cursor = $end->copy()->addSecond()->startOfDay();
        }

        return $buckets;
    }
}
