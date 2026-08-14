<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicStudent;
use App\Services\StudentImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentImpersonationController extends Controller
{
    public function start(Request $request, AcademicStudent $student, StudentImpersonationService $impersonation): RedirectResponse
    {
        $impersonation->start($request->user('web'), $student);

        $locale = $student->user?->locale ?? 'ar';

        return redirect()
            ->route('profile', ['locale' => $locale])
            ->with('portal_message', 'تم تسجيل الدخول كطالب. جلسة المسؤول ما زالت نشطة في لوحة التحكم.');
    }

    public function stop(Request $request, StudentImpersonationService $impersonation): RedirectResponse
    {
        $studentId = $impersonation->studentId();

        $impersonation->stop();

        if ($request->user('web') && $studentId) {
            return redirect()
                ->route('admin.students.show', $studentId)
                ->with('admin_message', 'تم إنهاء جلسة تسجيل الدخول كطالب.');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('admin_message', 'تم إنهاء جلسة تسجيل الدخول كطالب.');
    }
}
