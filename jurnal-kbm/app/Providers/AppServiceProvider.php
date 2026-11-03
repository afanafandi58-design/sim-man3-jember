<?php

namespace App\Providers;

use App\Services\AbsensiHarianService;
use App\Services\OperationalDateService;
use App\Support\TitikESchema;
use App\Support\TitikFSchema;
use Illuminate\Pagination\Paginator;
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
        // AdminLTE = Bootstrap 4. Default Tailwind pagination SVG (w-5/h-5) has no size
        // without Tailwind CSS and renders as huge black chevrons.
        Paginator::useBootstrapFour();

        Gate::define('role-admin', fn ($user) => $user && $user->role === 'admin');
        Gate::define('role-guru', fn ($user) => $user && $user->role === 'guru');
        Gate::define('role-staff', fn ($user) => $user && $user->role === 'staff');
        Gate::define('role-kepala-or-admin', fn ($user) => $user && in_array($user->role, ['kepala', 'admin'], true));
        Gate::define('role-kepala', fn ($user) => $user && $user->role === 'kepala');
        Gate::define('role-absensi-modul', fn ($user) => $user && in_array($user->role, ['guru', 'staff', 'admin', 'kepala'], true));
        Gate::define('role-absen-digital', fn ($user) => $user && in_array($user->role, ['admin', 'guru', 'staff', 'kepala'], true));
        Gate::define('role-absen-admin', fn ($user) => $user && $user->role === 'admin');
        Gate::define('role-lms', fn ($user) => $user && in_array($user->role, ['admin', 'guru', 'kepala'], true));
        Gate::define('role-lms-guru-or-admin', fn ($user) => $user && in_array($user->role, ['admin', 'guru'], true));
        Gate::define('role-lms-kamad-or-admin', fn ($user) => $user && in_array($user->role, ['admin', 'kepala'], true));
        Gate::define('role-lms-admin', fn ($user) => $user && $user->role === 'admin');

        Gate::define('role-wali-or-admin', function ($user) {
            if (! $user) {
                return false;
            }
            if (in_array($user->role, ['admin', 'kepala'], true)) {
                return true;
            }
            try {
                return app(AbsensiHarianService::class)->isWaliKelas($user);
            } catch (\Throwable) {
                return false;
            }
        });

        // Titik E/F: pastikan skema ada (shared hosting tanpa artisan).
        try {
            TitikESchema::ensure();
            TitikFSchema::ensure();
        } catch (\Throwable) {
            // diamkan bila DB belum siap saat boot CLI
        }
    }
}
