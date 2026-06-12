<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChairMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // The Program Chair is the Admin role for the BSA program.
        if (!$request->user() || !$request->user()->isChair()) {
            abort(403, 'Access denied. Program Chair only.');
        }

        return $next($request);
    }
}
