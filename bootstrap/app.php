<?php

use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // [KEAMANAN] Security headers pada semua response
        $middleware->append(SecurityHeadersMiddleware::class);

        // Daftarkan alias middleware custom
        $middleware->alias([
            'role'            => \App\Http\Middleware\EnsureUserRole::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeoutMiddleware::class,
            // [KEAMANAN] Gunakan named rate limiter 'login' yang didefinisikan di AppServiceProvider
            'throttle:login'  => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // [KEAMANAN] Render semua error API dalam format JSON konsisten
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // [KEAMANAN] Format error response konsisten untuk endpoint API/JSON
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!($request->is('api/*') || $request->expectsJson())) {
                return null; // Biarkan web request ditangani normal (redirect ke halaman error Blade)
            }

            // ValidationException → 422
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // ModelNotFoundException → 404
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang diminta tidak ditemukan.',
                    'errors'  => null,
                ], 404);
            }

            // AuthorizationException → 403
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melakukan aksi ini.',
                    'errors'  => null,
                ], 403);
            }

            // AuthenticationException → 401
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login untuk mengakses resource ini.',
                    'errors'  => null,
                ], 401);
            }

            // ThrottleRequestsException → 429
            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                    'errors'  => null,
                ], 429);
            }

            // HttpException (404, 405, dsb dari routing) → status code sesuai
            if ($e instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Terjadi kesalahan HTTP.',
                    'errors'  => null,
                ], $e->getStatusCode());
            }

            // QueryException & error umum → 500
            $isDebug = config('app.debug');
            return response()->json([
                'success' => false,
                'message' => $isDebug ? $e->getMessage() : 'Terjadi kesalahan pada server. Silakan hubungi administrator.',
                'errors'  => null,
            ], 500);
        });
    })->create();
