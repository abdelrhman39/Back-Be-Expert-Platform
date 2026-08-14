<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName && ! AdminPermissions::canAccessRoute($request->user(), $routeName)) {
            if ($request->expectsJson()) {
                abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة.');
            }

            abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
