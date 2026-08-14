<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('portal')->check()) {
            Auth::shouldUse('portal');

            return $next($request);
        }

        $webUser = Auth::guard('web')->user();

        if ($webUser) {
            if (AdminPermissions::canAccessAdmin($webUser)) {
                $locale = $request->route('locale') ?? 'ar';

                return redirect()->route('admin.dashboard');
            }

            Auth::shouldUse('web');

            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(401);
        }

        $locale = $request->route('locale') ?? 'ar';

        return redirect()->guest(route('login', ['locale' => $locale]));
    }
}
