<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\User;
use App\Support\StudentImpersonation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StudentImpersonationService
{
    public const SESSION_KEY = 'student_impersonation';

    public function isActive(): bool
    {
        return (bool) session(self::SESSION_KEY.'.active')
            && Auth::guard('portal')->check();
    }

    public function adminId(): ?int
    {
        $id = session(self::SESSION_KEY.'.admin_id');

        return is_numeric($id) ? (int) $id : null;
    }

    public function studentId(): ?int
    {
        $id = session(self::SESSION_KEY.'.student_id');

        return is_numeric($id) ? (int) $id : null;
    }

    public function start(User $admin, AcademicStudent $student): User
    {
        if (! StudentImpersonation::can($admin)) {
            abort(403, 'ليس لديك صلاحية تسجيل الدخول كطالب.');
        }

        $student->loadMissing('user');
        $user = $student->user;
        $blockReason = $student->impersonationBlockReason();

        if ($blockReason) {
            throw ValidationException::withMessages([
                'student' => [$blockReason],
            ]);
        }

        if ($admin->id === $user->id) {
            throw ValidationException::withMessages([
                'student' => ['لا يمكن تسجيل الدخول بنفس حساب المسؤول.'],
            ]);
        }

        Auth::guard('portal')->login($user);

        session([
            self::SESSION_KEY.'.active' => true,
            self::SESSION_KEY.'.admin_id' => $admin->id,
            self::SESSION_KEY.'.student_id' => $student->id,
            self::SESSION_KEY.'.started_at' => now()->toIso8601String(),
        ]);

        app(AuditLogService::class)->log(
            action: 'student.impersonate.start',
            descriptionAr: "تسجيل دخول كطالب: {$student->name_ar} ({$student->academic_id})",
            group: 'security',
            actor: $admin,
            subject: $student,
            subjectLabel: $student->name_ar,
            newValues: [
                'student_user_id' => $user->id,
                'academic_student_id' => $student->id,
            ],
        );

        return $user;
    }

    public function stop(): void
    {
        $adminId = $this->adminId();
        $studentId = $this->studentId();

        Auth::guard('portal')->logout();

        session()->forget(self::SESSION_KEY);

        if ($adminId) {
            $admin = User::query()->find($adminId);

            if ($admin) {
                app(AuditLogService::class)->log(
                    action: 'student.impersonate.stop',
                    descriptionAr: 'إنهاء جلسة تسجيل الدخول كطالب',
                    group: 'security',
                    actor: $admin,
                    newValues: [
                        'academic_student_id' => $studentId,
                    ],
                );
            }
        }
    }
}
