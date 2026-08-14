<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user
            || $user->status !== 'active'
            || $user->isLocked()
            || ! AdminPermissions::canAccessAdmin($user)
        ) {
            if ($request->expectsJson()) {
                abort(403, 'Admin access required.');
            }

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
