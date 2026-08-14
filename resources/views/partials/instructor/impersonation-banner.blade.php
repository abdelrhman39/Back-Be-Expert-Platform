@php($instructorImpersonation = app(\App\Services\InstructorImpersonationService::class))

@if ($instructorImpersonation->isActive())
    <div class="portal-impersonation-banner" role="status">
        <div class="portal-impersonation-banner__inner">
            <span class="portal-impersonation-banner__text">
                <i class="fa-solid fa-user-secret" aria-hidden="true"></i>
                أنت تعرض لوحة المدرب كـ <strong>{{ auth()->user()?->displayName() }}</strong>
                — جميع الإجراءات مسجلة باسم المسؤول
            </span>
            <div class="portal-impersonation-banner__actions">
                <a href="{{ route('admin.staff.members') }}" class="portal-impersonation-banner__link">إدارة الكوادر</a>
                <form method="post" action="{{ route('admin.staff.impersonate.stop') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="portal-impersonation-banner__stop">إنهاء جلسة المدرب</button>
                </form>
            </div>
        </div>
    </div>
@endif
