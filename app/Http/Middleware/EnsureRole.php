<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Aborts with 403 if:
     *   - The user is not authenticated (should be caught by 'auth' first, but just in case)
     *   - The user's role does not match the required role for this route group
     *
     * Usage in web.php route group:
     *   Route::middleware(['auth', 'role:teacher'])->group(...)
     *   Route::middleware(['auth', 'role:student'])->group(...)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
