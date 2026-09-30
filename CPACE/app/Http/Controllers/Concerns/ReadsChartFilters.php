<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Section;
use App\Models\Subject;
use App\Services\ChairDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The date range / subject / section (and at-risk priority / reason) filters
 * shared by the Program Chair's chart pages. Anything malformed falls back to
 * the default (last 30 days, everything) rather than erroring, since these
 * come from a shareable URL.
 */
trait ReadsChartFilters
{
    /** Default and maximum span of the date-range filter, in days. */
    protected int $chartDefaultDays = 30;
    protected int $chartMaxDays = 366;

    protected function chartFilters(Request $request): array
    {
        $parse = function ($value): ?Carbon {
            if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        };

        $to = $parse($request->query('to')) ?? today();
        $from = $parse($request->query('from')) ?? $to->copy()->subDays($this->chartDefaultDays - 1);
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) >= $this->chartMaxDays) {
            $from = $to->copy()->subDays($this->chartMaxDays - 1);
        }

        $subject = (int) $request->query('subject');
        $section = $request->query('section');

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'subject' => $subject > 0 && Subject::whereKey($subject)->exists() ? $subject : null,
            'section' => is_string($section) && $section !== '' && Section::where('name', $section)->exists() ? $section : null,
            'priority' => in_array($request->query('priority'), ['high', 'watch'], true) ? $request->query('priority') : null,
            'reason' => array_key_exists((string) $request->query('reason'), ChairDashboardService::REASONS) ? $request->query('reason') : null,
        ];
    }
}
