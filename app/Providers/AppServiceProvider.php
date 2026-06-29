<?php

namespace App\Providers;

use App\Models\TiangTelekomunikasi;
use App\Policies\TiangPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Daftarkan TiangPolicy
        Gate::policy(TiangTelekomunikasi::class, TiangPolicy::class);
    }
}
