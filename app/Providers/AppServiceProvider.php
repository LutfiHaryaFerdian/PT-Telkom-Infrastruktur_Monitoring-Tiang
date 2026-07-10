<?php

namespace App\Providers;

use App\Models\TiangTelekomunikasi;
use App\Observers\TiangObserver;
use App\Policies\TiangPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gunakan Bootstrap 5 untuk template pagination global
        Paginator::useBootstrapFive();

        // Daftarkan TiangPolicy
        Gate::policy(TiangTelekomunikasi::class, TiangPolicy::class);

        // [PERFORMA] Daftarkan Observer — otomatis invalidasi cache dashboard saat ada perubahan tiang
        TiangTelekomunikasi::observe(TiangObserver::class);

        // [PERFORMA] Tangkap N+1 query saat development/testing sebagai exception.
        // Di production ini otomatis dinonaktifkan karena env bukan local/testing.
        Model::preventLazyLoading(!app()->isProduction());

        // [KEAMANAN] Rate limiter untuk login — 5 percobaan per menit per email+IP.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . '|' . $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                    ], 429);
                });
        });

        // [KEAMANAN] Global Password complexity rules (minimal 8 karakter, huruf besar/kecil, angka, simbol)
        \Illuminate\Validation\Rules\Password::defaults(function () {
            $rule = \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
                
            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
