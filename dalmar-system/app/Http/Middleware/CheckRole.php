<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Restrict access to certain routes based on the authenticated
     * user's role, e.g. Route::middleware('role:admin')
     */
    public function handle($request, Closure $next, ...$roles): Response
    {
        if (! Auth::check() || ! in_array(Auth::user()->role, $roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
