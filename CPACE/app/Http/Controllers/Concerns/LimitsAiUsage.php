<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Per-user, per-day cap on AI-backed endpoints, independent of which
 * provider ends up serving the request. Bounds worst-case API spend from a
 * single account regardless of provider fallback order.
 */
trait LimitsAiUsage
{
    /**
     * @return bool true if the request should be blocked (limit reached)
     */
    protected function aiDailyLimitReached(string $feature, int $maxPerDay): bool
    {
        $key = "ai_daily.{$feature}." . (Auth::id() ?? 'guest');

        if (RateLimiter::tooManyAttempts($key, $maxPerDay)) {
            return true;
        }

        RateLimiter::hit($key, 86400);

        return false;
    }

    protected function aiDailyLimitResponse(?string $message = null)
    {
        return response()->json([
            'message' => $message ?? 'You have reached today\'s limit for this AI feature. Please try again tomorrow.',
        ], 429);
    }
}
