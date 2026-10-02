<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RecordRequestMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PerformanceController extends Controller
{
    private const TREND_DAYS = 14;

    public function index(Request $request)
    {
        return view('superadmin.performance', $this->buildData());
    }

    public function data(Request $request)
    {
        return response()->json($this->buildData());
    }

    private function buildData(): array
    {
        $today = now()->format('Y-m-d');
        $days = collect(range(self::TREND_DAYS - 1, 0))->map(fn ($offset) => now()->subDays($offset));

        $trend = $days->map(function ($date) {
            $key = $date->format('Y-m-d');
            $requests = (int) Cache::get("metrics.requests.{$key}", 0);
            $errors = (int) Cache::get("metrics.errors.{$key}", 0);
            $durationSum = (int) Cache::get("metrics.duration_sum.{$key}", 0);
            $durationCount = (int) Cache::get("metrics.duration_count.{$key}", 0);
            $aiDurationSum = (int) Cache::get("metrics.ai_duration_sum.{$key}", 0);
            $aiDurationCount = (int) Cache::get("metrics.ai_duration_count.{$key}", 0);

            return [
                'date' => $key,
                'label' => $date->format('M j'),
                'requests' => $requests,
                'errors' => $errors,
                'avg_response_ms' => $durationCount > 0 ? (int) round($durationSum / $durationCount) : null,
                'error_rate' => $requests > 0 ? round($errors / $requests * 100, 2) : null,
                'ai_avg_response_ms' => $aiDurationCount > 0 ? (int) round($aiDurationSum / $aiDurationCount) : null,
            ];
        })->values();

        $windowRequests = (int) $trend->sum('requests');
        $windowErrors = (int) $trend->sum('errors');

        // Samples are [{'route' => ..., 'ms' => ...}, ...] since RecordRequestMetrics
        // started tagging the route name — ms() pulls out just the durations for
        // avg/percentile math, routeBreakdown() groups them to find which specific
        // endpoints are driving the p95/p99 tail.
        $durations = Cache::get('metrics.durations', []);
        $aiDurations = Cache::get('metrics.ai_durations', []);
        $durationMs = $this->ms($durations);
        $aiDurationMs = $this->ms($aiDurations);

        $summary = [
            'requests_today' => (int) Cache::get("metrics.requests.{$today}", 0),
            'errors_today' => (int) Cache::get("metrics.errors.{$today}", 0),
            'avg_response_ms' => count($durationMs) > 0 ? (int) round(array_sum($durationMs) / count($durationMs)) : null,
            'p95_response_ms' => $this->percentile($durationMs, 95),
            'p99_response_ms' => $this->percentile($durationMs, 99),
            'sample_size' => count($durationMs),
            'window_requests' => $windowRequests,
            'window_errors' => $windowErrors,
            'reliability' => $windowRequests > 0 ? round((1 - $windowErrors / $windowRequests) * 100, 2) : null,
            'retention_days' => RecordRequestMetrics::RETENTION_DAYS,
            'slow_routes' => $this->routeBreakdown($durations),
            // AI-calling routes (ai-tutor chat, ai-draft, gap-fill generate) wait
            // on Gemini/OpenRouter/Claude — tracked separately from the app's own
            // response time so one doesn't skew the other (see RecordRequestMetrics).
            'ai_avg_response_ms' => count($aiDurationMs) > 0 ? (int) round(array_sum($aiDurationMs) / count($aiDurationMs)) : null,
            'ai_p95_response_ms' => $this->percentile($aiDurationMs, 95),
            'ai_sample_size' => count($aiDurationMs),
            'ai_slow_routes' => $this->routeBreakdown($aiDurations),
        ];

        return ['trend' => $trend, 'summary' => $summary];
    }

    /** Pull just the millisecond durations out of route-tagged samples
     *  (and tolerate older plain-int samples left over from before route
     *  tagging, until that rolling cache window rolls past them). */
    private function ms(array $samples): array
    {
        return array_map(fn ($s) => (int) (is_array($s) ? $s['ms'] : $s), $samples);
    }

    private function percentile(array $samples, int $p): ?int
    {
        if (empty($samples)) {
            return null;
        }

        sort($samples);
        $index = (int) ceil($p / 100 * count($samples)) - 1;

        return $samples[max(0, min($index, count($samples) - 1))];
    }

    /** Group route-tagged samples by route, so the Performance page can show
     *  which specific endpoints are driving the p95/p99 tail instead of just
     *  one aggregate number. Sorted by average duration, slowest first. */
    private function routeBreakdown(array $samples, int $limit = 8): array
    {
        $byRoute = [];

        foreach ($samples as $sample) {
            if (! is_array($sample)) {
                continue; // older plain-int sample, predates route tagging
            }

            $route = $sample['route'] ?? 'unknown';
            $byRoute[$route]['count'] = ($byRoute[$route]['count'] ?? 0) + 1;
            $byRoute[$route]['sum'] = ($byRoute[$route]['sum'] ?? 0) + $sample['ms'];
            $byRoute[$route]['max'] = max($byRoute[$route]['max'] ?? 0, $sample['ms']);
        }

        $rows = collect($byRoute)->map(fn ($stats, $route) => [
            'route' => $route,
            'count' => $stats['count'],
            'avg_ms' => (int) round($stats['sum'] / $stats['count']),
            'max_ms' => $stats['max'],
        ])->sortByDesc('avg_ms')->values();

        return $rows->take($limit)->all();
    }
}
