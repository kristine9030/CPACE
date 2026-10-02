<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AiUsageLog;
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

        // Historical record for the Super Admin dashboard's AI usage report
        // — the rate-limit cache above only ever knows "today, this user".
        try {
            AiUsageLog::create(['user_id' => Auth::id(), 'feature' => $feature, 'created_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
        }

        return false;
    }

    protected function aiDailyLimitResponse(?string $message = null)
    {
        return response()->json([
            'message' => $message ?? 'You have reached today\'s limit for this AI feature. Please try again tomorrow.',
        ], 429);
    }
}
