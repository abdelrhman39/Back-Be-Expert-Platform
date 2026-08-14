<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfPortalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale') ?? 'ar';

        if (Auth::guard('portal')->check()) {
            return redirect()->route('profile', ['locale' => $locale]);
        }

        $webUser = Auth::guard('web')->user();

        if ($webUser && ! AdminPermissions::canAccessAdmin($webUser)) {
            return redirect()->route('profile', ['locale' => $locale]);
        }

        return $next($request);
    }
}
