<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $raw = $request->bearerToken();

        if (! $raw) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $record = ApiToken::with('user')->where('token', $raw)->first();

        if (! $record || $record->isExpired() || ! $record->user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Bind the authenticated user so controllers can call Auth::user() / Auth::id().
        auth()->setUser($record->user);

        try {
            $record->forceFill(['last_used_at' => now()])->saveQuietly();
        } catch (\Throwable $e) {
            // Never let a missing last_used_at column (e.g. a test's hand-built
            // api_tokens schema) break authentication.
        }

        return $next($request);
    }
}
