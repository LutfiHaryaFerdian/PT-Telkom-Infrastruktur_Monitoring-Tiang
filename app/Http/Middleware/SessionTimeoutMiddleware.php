<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * [KEAMANAN] Session Idle Timeout Middleware.
 *
 * Jika user tidak ada aktivitas selama lebih dari SESSION_IDLE_TIMEOUT menit
 * (default: 120 menit), paksa logout dan redirect ke halaman login.
 * Ini berbeda dari SESSION_LIFETIME Laravel yang mengatur lifetime cookie/file session —
 * middleware ini secara aktif memeriksa kapan terakhir ada aktivitas.
 */
class SessionTimeoutMiddleware
{
    /**
     * Idle timeout dalam menit. Ambil dari config atau default 120 menit.
     */
    protected int $idleTimeout;

    public function __construct()
    {
        $this->idleTimeout = (int) config('session.idle_timeout', 120);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity_at');

            if ($lastActivity && now()->diffInMinutes($lastActivity) >= $this->idleTimeout) {
                // Session sudah idle terlalu lama — paksa logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['session' => 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.']);
            }

            // Perbarui timestamp aktivitas terakhir
            $request->session()->put('last_activity_at', now());
        }

        return $next($request);
    }
}
