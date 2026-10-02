<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rolling, cache-backed request/error/response-time counters for the Super
 * Admin dashboard — cheap enough to run on every request, deliberately not
 * persisted to the database (mirrors the AI daily-cap cache pattern rather
 * than logging every single request as a DB row).
 */
class RecordRequestMetrics
{
    private const MAX_SAMPLES = 200;

    /** How many days of daily request/error/response-time buckets to retain
     *  for the Super Admin Performance page's trend charts. */
    public const RETENTION_DAYS = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $today = now()->format('Y-m-d');
        $ttl = now()->addDays(self::RETENTION_DAYS + 1);

        Cache::increment("metrics.requests.{$today}");
        Cache::put("metrics.requests.{$today}", Cache::get("metrics.requests.{$today}", 0), $ttl);

        if ($response->getStatusCode() >= 500) {
            Cache::increment("metrics.errors.{$today}");
            Cache::put("metrics.errors.{$today}", Cache::get("metrics.errors.{$today}", 0), $ttl);
        }

        // Daily response-time average, kept as a sum/count pair rather than a
        // running average — cheap to update atomically without reading first.
        Cache::increment("metrics.duration_sum.{$today}", $durationMs);
        Cache::put("metrics.duration_sum.{$today}", Cache::get("metrics.duration_sum.{$today}", 0), $ttl);
        Cache::increment("metrics.duration_count.{$today}");
        Cache::put("metrics.duration_count.{$today}", Cache::get("metrics.duration_count.{$today}", 0), $ttl);

        $samples = Cache::get('metrics.durations', []);
        $samples[] = $durationMs;
        if (count($samples) > self::MAX_SAMPLES) {
            $samples = array_slice($samples, -self::MAX_SAMPLES);
        }
        Cache::put('metrics.durations', $samples, now()->addDay());

        return $response;
    }
}
