<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request — verifikasi role dan status akun.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // Blokir akun yang email-nya belum terverifikasi (lewati jika di lingkungan local/testing)
        if (! app()->environment('local', 'testing') && ! $user->email_verified_at) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda belum diverifikasi. Hubungi Super Admin.',
            ]);
        }

        if (empty($roles)) {
            $roles = ['super_admin', 'admin'];
        }

        $userRole = $user->role ?? 'admin';

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'Forbidden. You do not have the required role.');
        }

        return $next($request);
    }
}
