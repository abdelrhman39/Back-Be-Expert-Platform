<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\InstructorImpersonationService;
use App\Services\StudentImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(
        Request $request,
        StudentImpersonationService $studentImpersonation,
        InstructorImpersonationService $instructorImpersonation,
    ): RedirectResponse {
        $locale = $request->route('locale') ?? 'ar';

        if ($instructorImpersonation->isActive()) {
            $instructorImpersonation->stop();

            if ($request->user('web')) {
                return redirect()
                    ->route('admin.staff.members')
                    ->with('admin_message', 'تم إنهاء جلسة تسجيل الدخول كمدرب.');
            }
        }

        if ($studentImpersonation->isActive()) {
            $studentImpersonation->stop();

            if ($request->user('web')) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('admin_message', 'تم إنهاء جلسة تسجيل الدخول كطالب.');
            }
        }

        Auth::guard('portal')->logout();

        if (Auth::guard('web')->check()) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home', ['locale' => $locale]);
    }
}
