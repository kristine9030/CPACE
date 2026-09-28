<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Authenticated pages must never be served from the browser's back/forward
 * cache after a session ends. Without this, hitting the back button after
 * logout (or after the session was invalidated) shows a stale snapshot of
 * the last authenticated page instead of bouncing to login — and it carries
 * a stale CSRF token, so the next click on it 419s instead of working. Worse,
 * on a machine used to test multiple accounts, that failed request can leave
 * a leftover "remember me" cookie from an earlier login as the one credential
 * source, so the very next fresh visit silently signs back in as whoever's
 * remember cookie is still valid — not the account the user thinks they're
 * using. Forcing no-store here means back/forward always re-fetches from the
 * server, which re-checks auth and never reuses a stale CSRF token.
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
