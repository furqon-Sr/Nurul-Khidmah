<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized.');
        }

        if (empty($roles)) {
            // Jika tidak ada parameter role spesifik yang diberikan, 
            // izinkan super_admin atau admin secara default
            $roles = ['super_admin', 'admin'];
        }

        $userRole = $request->user()->role ?? 'admin';

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'Forbidden. You do not have the required role.');
        }

        return $next($request);
    }
}
