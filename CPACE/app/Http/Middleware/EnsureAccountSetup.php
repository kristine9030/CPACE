<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountSetup
{
    /**
     * Force freshly-imported students through the first-login Account Setup
     * before letting them reach any other page. Everyone else passes through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->needsSetup() && ! $this->isAllowed($request)) {
            return redirect()->route($user->isFaculty() ? 'faculty.account-setup' : 'account-setup');
        }

        return $next($request);
    }

    /**
     * Routes a not-yet-set-up student/faculty is still allowed to hit, so we
     * don't trap them in a redirect loop.
     */
    private function isAllowed(Request $request): bool
    {
        return $request->routeIs(
            'account-setup',
            'account-setup.store',
            'onboarding.welcome',
            'faculty.account-setup',
            'faculty.account-setup.store',
            'logout',
        );
    }
}
