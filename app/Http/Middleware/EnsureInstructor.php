<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user?->isInstructor()
            || $user->status !== 'active'
            || $user->isLocked()
            || ! $user->academicStaff
            || $user->academicStaff->status !== 'active'
        ) {
            abort(403, 'هذه الصفحة مخصصة للمدربين المرتبطين بسجل كادر تدريبي.');
        }

        return $next($request);
    }
}
