<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Autorise la requête si l'utilisateur possède l'un des rôles attendus.
     *
     * Usage : ->middleware('role:admin') ou ->middleware('role:stagiaire_op,stagiaire_fpc')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
