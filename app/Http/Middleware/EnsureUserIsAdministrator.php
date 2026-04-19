<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdministrator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $role = strtolower(trim((string) ($user->role ?? '')));
        $isAdmin = in_array($role, ['administrator', 'admin'], true);

        if (!$user || !$isAdmin) {
            abort(403);
        }

        return $next($request);
    }
}
