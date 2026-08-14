<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicStaff;
use App\Services\InstructorImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstructorImpersonationController extends Controller
{
    public function start(
        Request $request,
        AcademicStaff $staff,
        InstructorImpersonationService $impersonation,
    ): RedirectResponse {
        $user = $impersonation->start($request->user('web'), $staff);

        return redirect()
            ->route('instructor.dashboard', ['locale' => $user->locale ?? 'ar'])
            ->with('portal_message', 'تم الدخول إلى لوحة المدرب. جلسة المسؤول ما زالت نشطة.');
    }

    public function stop(Request $request, InstructorImpersonationService $impersonation): RedirectResponse
    {
        $staffId = $impersonation->staffId();
        $impersonation->stop();

        return redirect()
            ->route($staffId ? 'admin.staff.show' : 'admin.staff.members', $staffId ? ['staff' => $staffId] : [])
            ->with('admin_message', 'تم إنهاء جلسة الدخول كمدرب.');
    }
}
