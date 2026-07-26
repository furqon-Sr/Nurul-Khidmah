<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Security headers untuk mencegah berbagai serangan web.
     * Diterapkan ke SEMUA response dari aplikasi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah Clickjacking — halaman tidak boleh di-frame oleh situs lain
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Cegah MIME Sniffing — browser harus ikuti Content-Type dari server
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Cegah XSS via Referrer — hanya kirim origin, bukan full URL
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.quilljs.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.quilljs.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: blob: https://images.unsplash.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        // $response->headers->set('Content-Security-Policy', $csp);

        // Batasi fitur browser — nonaktifkan kamera, mikrofon, geolocation dll
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Strict Transport Security — paksa HTTPS selama 1 tahun
        if ($request->isSecure() || app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Jangan cache halaman admin (mencegah back button reveal data)
        if (str_starts_with($request->path(), 'admin') || str_starts_with($request->path(), 'dashboard')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
