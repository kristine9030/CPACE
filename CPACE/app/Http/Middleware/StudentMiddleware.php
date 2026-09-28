<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the student area (dashboard, subjects, quizzes, mock exams,
 * calendar, achievements, review notes, student performance/settings).
 * Before this existed, that whole block only required 'auth' — any signed-in
 * chair, faculty or alumni account could open it directly (or land on it via
 * an old browser-history entry) and it would render using their own id,
 * silently zeroed out because they have no student_profiles row. That looked
 * like "my account got swapped for a student's" when it was really just a
 * missing role check on the route.
 */
class StudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isStudent()) {
            abort(403, 'Access denied. Student only.');
        }

        return $next($request);
    }
}
