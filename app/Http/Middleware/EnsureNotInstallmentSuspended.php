<?php

namespace App\Http\Middleware;

use App\Services\InstallmentDunningService;
use App\Services\InstallmentOverdueService;
use App\Support\InstallmentSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstallmentSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $dunning = app(InstallmentDunningService::class);

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('installments', 'installments.*')) {
            return $next($request);
        }

        $learningBlocked = (
            InstallmentSettings::suspensionEnabled()
            && app(InstallmentOverdueService::class)->userIsSuspendedForInstallments($user)
        ) || (
            InstallmentSettings::dunningEnabled()
            && $dunning->userHasLearningSuspension($user)
        );

        $isExamRoute = $request->routeIs('exams', 'exams.*', 'exam.*', 'exam-attempts.*', 'portal.exams*');

        if ($isExamRoute && InstallmentSettings::dunningEnabled() && $dunning->userHasExamBlock($user)) {
            abort(403, 'تم منع الوصول للاختبارات بسبب تأخر في سداد الأقساط. يرجى السداد من صفحة الأقساط.');
        }

        if ($learningBlocked) {
            abort(403, 'تم إيقاف الالتحاق مؤقتاً بسبب تأخر في سداد الأقساط. يرجى السداد من صفحة الأقساط لاستعادة الوصول.');
        }

        return $next($request);
    }
}
