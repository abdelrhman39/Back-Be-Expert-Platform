<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\EnsureInstructor;
use App\Http\Middleware\EnsurePortalAuth;
use App\Livewire\Hooks\PrefetchComponentStyles;
use App\Models\AcademicStudent;
use App\Models\ExamAttempt;
use App\Observers\AcademicStudentObserver;
use App\Observers\ExamAttemptObserver;
use App\Services\CartService;
use App\Services\WishlistService;
use App\Support\RuntimeSettings;
use App\Support\StudentImpersonation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Must register before Livewire boots ComponentHookRegistry listeners.
        Livewire::componentHook(PrefetchComponentStyles::class);

        // Ensure custom Livewire pagination views resolve even when package
        // discovery / view hints load in an unexpected order on the server.
        $this->callAfterResolving('view', function ($view): void {
            $path = resource_path('views/vendor/livewire');

            if (is_dir($path)) {
                $view->prependNamespace('livewire', $path);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RuntimeSettings::applyRuntimeConfig();

        AcademicStudent::observe(AcademicStudentObserver::class);
        ExamAttempt::observe(ExamAttemptObserver::class);

        // Livewire resolves this as livewire::{theme} → resources/views/vendor/livewire/admin.blade.php
        config(['livewire.pagination_theme' => 'admin']);

        // Fallback for any non-Livewire ->links() calls in admin context.
        if (view()->exists('livewire::admin')) {
            Paginator::defaultView('livewire::admin');
            Paginator::defaultSimpleView('livewire::simple-admin');
        }

        Blade::if('canAdmin', fn (string $permission): bool => (bool) auth()->user()?->canAdmin($permission));
        Blade::if('canInstructor', fn (string $permission): bool => (bool) auth()->user()?->canInstructor($permission));
        Blade::if('canImpersonateStudent', fn (): bool => StudentImpersonation::can(auth()->user()));

        Livewire::addPersistentMiddleware([
            EnsureAdmin::class,
            EnsureAdminPermission::class,
            EnsurePortalAuth::class,
            EnsureInstructor::class,
        ]);

        View::composer('partials.header', function ($view) {
            $view->with([
                'cartCount' => app(CartService::class)->count(),
                'wishlistCount' => app(WishlistService::class)->count(),
            ]);
        });

        ResetPassword::createUrlUsing(function ($user, string $token): string {
            $locale = $user->locale ?: 'ar';

            return route('password.reset', [
                'locale' => $locale,
                'token' => $token,
                'email' => $user->email,
            ]);
        });
    }
}
