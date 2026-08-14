<?php

namespace App\Services;

use App\Models\AcademicStaff;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InstructorImpersonationService
{
    public const SESSION_KEY = 'instructor_impersonation';

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

    public function staffId(): ?int
    {
        $id = session(self::SESSION_KEY.'.staff_id');

        return is_numeric($id) ? (int) $id : null;
    }

    public function blockReason(AcademicStaff $staff): ?string
    {
        $staff->loadMissing('user');

        if (! $staff->user_id || ! $staff->user) {
            return 'لا يوجد حساب دخول مرتبط بهذا العضو.';
        }
        if ($staff->status !== 'active') {
            return 'سجل الكادر غير نشط.';
        }
        if (! $staff->user->isInstructor()) {
            return 'الحساب المرتبط ليس حساب مدرب.';
        }
        if ($staff->user->status !== 'active') {
            return 'حساب المدرب غير نشط.';
        }
        if ($staff->user->isLocked()) {
            return 'حساب المدرب مقفل مؤقتاً.';
        }
        if ($staff->schedules()->doesntExist()) {
            return 'لم يتم إسناد شعبة دراسية لهذا المدرب بعد.';
        }

        return null;
    }

    public function start(User $admin, AcademicStaff $staff): User
    {
        if (! $admin->canAdmin('staff.impersonate')) {
            abort(403, 'ليس لديك صلاحية تسجيل الدخول كمدرب.');
        }

        if (app(StudentImpersonationService::class)->isActive() || $this->isActive()) {
            throw ValidationException::withMessages([
                'staff' => 'أنهِ جلسة الانتحال الحالية قبل بدء جلسة جديدة.',
            ]);
        }

        if ($reason = $this->blockReason($staff)) {
            throw ValidationException::withMessages(['staff' => $reason]);
        }

        $user = $staff->user;

        if ($admin->id === $user->id) {
            throw ValidationException::withMessages(['staff' => 'لا يمكن تسجيل الدخول بنفس حساب المسؤول.']);
        }

        Auth::guard('portal')->login($user);
        session([
            self::SESSION_KEY.'.active' => true,
            self::SESSION_KEY.'.admin_id' => $admin->id,
            self::SESSION_KEY.'.staff_id' => $staff->id,
            self::SESSION_KEY.'.started_at' => now()->toIso8601String(),
        ]);

        app(AuditLogService::class)->log(
            action: 'instructor.impersonate.start',
            descriptionAr: 'تسجيل دخول مباشر كمدرب: '.$staff->name_ar,
            group: 'security',
            actor: $admin,
            subject: $staff,
            subjectLabel: $staff->name_ar,
            newValues: ['staff_id' => $staff->id, 'instructor_user_id' => $user->id],
        );

        return $user;
    }

    public function stop(): void
    {
        $adminId = $this->adminId();
        $staffId = $this->staffId();
        Auth::guard('portal')->logout();
        session()->forget(self::SESSION_KEY);

        if ($admin = User::query()->find($adminId)) {
            app(AuditLogService::class)->log(
                action: 'instructor.impersonate.stop',
                descriptionAr: 'إنهاء جلسة تسجيل الدخول كمدرب',
                group: 'security',
                actor: $admin,
                newValues: ['staff_id' => $staffId],
            );
        }
    }
}
