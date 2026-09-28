<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'faculty' => \App\Http\Middleware\FacultyMiddleware::class,
            'chair'   => \App\Http\Middleware\ChairMiddleware::class,
            'alumni'  => \App\Http\Middleware\AlumniMiddleware::class,
            'api.auth' => \App\Http\Middleware\ApiAuthenticate::class,
            'no-back-cache' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Gate freshly-imported students into first-login Account Setup.
        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountSetup::class,
        ]);

        // The 'guest' middleware (RedirectIfAuthenticated) bounces an already
        // logged-in user who lands on /login — e.g. the browser's back button
        // after signing in — straight to route('dashboard') by default. That
        // name only exists once, for the student area (routes/web.php), so
        // without this override a signed-in chair or faculty member landing
        // back on /login was sent to the student dashboard instead of their
        // own. Mirrors AuthController::homeFor()'s per-role destination.
        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if ($user?->isChair()) {
                return route('chair.dashboard');
            }
            if ($user?->isFaculty()) {
                return route('faculty.dashboard');
            }
            if ($user?->isAlumni()) {
                return route('community.index');
            }

            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
