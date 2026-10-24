<?php

namespace App\Providers;

use App\Services\OperationalDateService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OperationalDateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('role-admin', fn ($user) => $user && $user->role === 'admin');
        Gate::define('role-guru', fn ($user) => $user && $user->role === 'guru');
        Gate::define('role-kepala-or-admin', fn ($user) => $user && in_array($user->role, ['kepala', 'admin'], true));
        Gate::define('role-kepala', fn ($user) => $user && $user->role === 'kepala');

        // Titik E: pastikan kolom guru_pengganti ada (shared hosting tanpa artisan).
        try {
            \App\Support\TitikESchema::ensure();
        } catch (\Throwable) {
            // diamkan bila DB belum siap saat boot CLI
        }
    }
}
