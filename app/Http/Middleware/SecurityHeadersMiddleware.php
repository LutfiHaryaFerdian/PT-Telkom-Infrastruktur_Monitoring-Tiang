<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * [KEAMANAN] Tambahkan security headers pada setiap response HTTP.
 *
 * Alasan tiap header:
 *  - CSP             : Mencegah XSS dan code injection dengan membatasi sumber resource yang boleh dimuat.
 *  - X-Frame-Options : Mencegah clickjacking (iframe embedding dari domain asing).
 *  - X-Content-Type  : Mencegah browser "sniff" MIME type berbeda dari yang dikirim server (MIME confusion attack).
 *  - HSTS            : Memaksa browser selalu gunakan HTTPS (hanya aktif di production + HTTPS).
 *  - Referrer-Policy : Kontrol informasi referrer yang dikirim saat navigasi antar domain.
 *  - X-XSS-Protection: Legacy header untuk browser lama (IE/Chrome lama) yang belum punya CSP support.
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // CDN yang benar-benar dipakai project ini:
        //   - Bootstrap CSS/JS  : cdn.jsdelivr.net
        //   - Leaflet.js        : unpkg.com, *.tile.openstreetmap.org (tiles peta)
        //   - Chart.js          : cdn.jsdelivr.net
        //   - jQuery            : code.jquery.com
        //   - DataTables        : cdn.datatables.net
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net unpkg.com code.jquery.com cdn.datatables.net",
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net unpkg.com fonts.googleapis.com cdn.datatables.net",
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net unpkg.com",
            "img-src 'self' data: blob: *.tile.openstreetmap.org",
            "connect-src 'self'",
            "frame-ancestors 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // HSTS hanya aktif di production + request HTTPS (jangan aktifkan di local/staging HTTP)
        if (app()->isProduction() && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
