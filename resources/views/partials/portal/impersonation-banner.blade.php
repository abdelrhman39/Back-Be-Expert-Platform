@php
    use App\Services\StudentImpersonationService;

    $impersonation = app(StudentImpersonationService::class);
@endphp

@if ($impersonation->isActive())
    <div class="portal-impersonation-banner" role="status">
        <div class="portal-impersonation-banner__inner">
            <span class="portal-impersonation-banner__text">
                <i class="fa-solid fa-user-secret" aria-hidden="true"></i>
                أنت تعرض بوابة الطالب كـ <strong>{{ auth()->user()?->displayName() }}</strong>
                — جلسة المسؤول ما زالت نشطة في لوحة التحكم
            </span>
            <div class="portal-impersonation-banner__actions">
                <a href="{{ route('admin.dashboard') }}" class="portal-impersonation-banner__link">لوحة التحكم</a>
                <form method="post" action="{{ route('admin.students.impersonate.stop') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="portal-impersonation-banner__stop">إنهاء الجلسة</button>
                </form>
            </div>
        </div>
    </div>
@endif
