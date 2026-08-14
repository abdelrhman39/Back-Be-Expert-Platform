<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\EnsureInstructor;
use App\Http\Middleware\EnsureNotInstallmentSuspended;
use App\Http\Middleware\EnsurePortalAuth;
use App\Http\Middleware\RedirectIfPortalAuthenticated;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPlatformAnalytics;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/moyasar',
            'webhooks/tabby',
            'webhooks/tamara',
            'webhooks/zoom',
            'integrations/microsoft/callback',
        ]);

        $middleware->alias([
            'set.locale' => SetLocale::class,
            'admin' => EnsureAdmin::class,
            'admin.permission' => EnsureAdminPermission::class,
            'instructor' => EnsureInstructor::class,
            'installment.active' => EnsureNotInstallmentSuspended::class,
            'portal.auth' => EnsurePortalAuth::class,
            'portal.guest' => RedirectIfPortalAuthenticated::class,
        ]);

        $middleware->web(append: [
            TrackPlatformAnalytics::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            $locale = $request->route('locale') ?? 'ar';

            return route('login', ['locale' => $locale]);
        });
        $middleware->redirectUsersTo(function ($request) {
            $locale = $request->route('locale') ?? 'ar';
            $user = $request->user();

            if ($user?->isInstructor() && $user->academicStaff) {
                return route('instructor.dashboard', ['locale' => $locale]);
            }

            return route('profile', ['locale' => $locale]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
