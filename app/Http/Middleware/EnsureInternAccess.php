<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a route on intern access: an approved student or supervisor.
 *
 * Public members keep their own dashboard; they are pushed here only once an
 * administrator has approved them and promoted them into an intern role.
 */
class EnsureInternAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $allowed = $user !== null
            && $user->role->hasInternAccess()
            && $user->isApproved()
            && $user->isActive();

        abort_unless($allowed, 403);

        return $next($request);
    }
}
