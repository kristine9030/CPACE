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

    /**
     * Routes that synchronously call an external AI provider (Gemini/
     * OpenRouter/Claude) before responding. Their multi-second latency is
     * third-party API wait time, not app performance — mixing them into the
     * general response-time samples made the Performance page's p95/p99
     * look like the app itself was slow, when a handful of AI calls were
     * just dominating a 200-sample rolling window. They're still counted in
     * requests/errors as normal, but their durations go into a separate
     * "ai.*" bucket (see PerformanceController) instead of the general one,
     * so AI latency stays visible without skewing the app's own numbers.
     */
    private const AI_ROUTES = [
        'ai-tutor.chat',
        'ai-tutor.performance-insights',
        'faculty.question.ai-draft',
        'chair.ai-review.generate',
        'faculty.test-bank.ai-review.generate',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $today = now()->format('Y-m-d');
        $ttl = now()->addDays(self::RETENTION_DAYS + 1);
        $isAiRoute = in_array($request->route()?->getName(), self::AI_ROUTES, true);
        $prefix = $isAiRoute ? 'metrics.ai_' : 'metrics.';

        $this->bumpCounter("metrics.requests.{$today}", $ttl);

        if ($response->getStatusCode() >= 500) {
            $this->bumpCounter("metrics.errors.{$today}", $ttl);
        }

        // Daily response-time average, kept as a sum/count pair rather than a
        // running average — cheap to update atomically without reading first.
        // AI routes use their own "ai_"-prefixed keys, kept separate from the
        // app's own response-time metrics (see AI_ROUTES above).
        $this->bumpCounter("{$prefix}duration_sum.{$today}", $ttl, $durationMs);
        $this->bumpCounter("{$prefix}duration_count.{$today}", $ttl);

        // Each sample carries the route name (falling back to the raw path for
        // unnamed routes) alongside its duration, so the Performance page can
        // break the p95/p99 tail down by endpoint instead of just one number.
        // This is the one metric backed by a read-modify-write of a whole array,
        // which under the file cache driver needs an exclusive lock — acquired
        // non-blocking (->get() tries once and gives up immediately) so a busy
        // moment just skips recording this one sample instead of queuing real
        // requests behind the lock. Dashboard stat, not business data: losing an
        // occasional sample is harmless, blocking a request on it is not.
        $samplesKey = $isAiRoute ? 'metrics.ai_durations' : 'metrics.durations';
        Cache::lock("{$samplesKey}.lock", 2)->get(function () use ($samplesKey, $request, $durationMs) {
            $samples = Cache::get($samplesKey, []);
            $samples[] = ['route' => $request->route()?->getName() ?? $request->path(), 'ms' => $durationMs];
            if (count($samples) > self::MAX_SAMPLES) {
                $samples = array_slice($samples, -self::MAX_SAMPLES);
            }
            Cache::put($samplesKey, $samples, now()->addDay());
        });

        return $response;
    }

    /**
     * Atomically bump a rolling daily counter without reading it first.
     * Cache::add() only writes (and sets the TTL) the first time the key
     * doesn't exist yet; increment() alone bumps the value in place on every
     * other request — no read-modify-write cycle, no extra lock acquisition.
     */
    private function bumpCounter(string $key, \DateTimeInterface $ttl, int $by = 1): void
    {
        Cache::add($key, 0, $ttl);
        Cache::increment($key, $by);
    }
}
