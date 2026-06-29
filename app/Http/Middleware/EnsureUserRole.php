<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * Penggunaan di route:
     *   Route::middleware(['auth', 'role:admin'])
     *   Route::middleware(['auth', 'role:admin,teknisi'])
     *
     * @param  string  ...$roles  Role yang diizinkan
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! in_array(auth()->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk aksi ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk aksi ini.');
        }

        return $next($request);
    }
}
