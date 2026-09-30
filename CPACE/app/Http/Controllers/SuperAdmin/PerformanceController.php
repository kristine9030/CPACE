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

            return [
                'date' => $key,
                'label' => $date->format('M j'),
                'requests' => $requests,
                'errors' => $errors,
                'avg_response_ms' => $durationCount > 0 ? (int) round($durationSum / $durationCount) : null,
                'error_rate' => $requests > 0 ? round($errors / $requests * 100, 2) : null,
            ];
        })->values();

        $windowRequests = (int) $trend->sum('requests');
        $windowErrors = (int) $trend->sum('errors');

        $durations = Cache::get('metrics.durations', []);

        $summary = [
            'requests_today' => (int) Cache::get("metrics.requests.{$today}", 0),
            'errors_today' => (int) Cache::get("metrics.errors.{$today}", 0),
            'avg_response_ms' => count($durations) > 0 ? (int) round(array_sum($durations) / count($durations)) : null,
            'p95_response_ms' => $this->percentile($durations, 95),
            'p99_response_ms' => $this->percentile($durations, 99),
            'sample_size' => count($durations),
            'window_requests' => $windowRequests,
            'window_errors' => $windowErrors,
            'reliability' => $windowRequests > 0 ? round((1 - $windowErrors / $windowRequests) * 100, 2) : null,
            'retention_days' => RecordRequestMetrics::RETENTION_DAYS,
        ];

        return ['trend' => $trend, 'summary' => $summary];
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
}
