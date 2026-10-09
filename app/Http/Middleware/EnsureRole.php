<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a route on the current user's role.
 *
 * Middleware parameters are the allowed role values, for example
 * `role:admin,super_admin`. Guests are never allowed through, so this is always
 * stacked behind `auth`.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if($user === null || ! in_array($user->role->value, $roles, true), 403);

        return $next($request);
    }
}
