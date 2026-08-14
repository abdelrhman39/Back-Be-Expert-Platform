@php
    use App\Support\AcademicStudentOptions;

    $statusClass = match ($student->academic_status) {
        'studying' => 'admin-badge--success',
        'pending' => 'admin-badge--warn',
        'graduated' => 'admin-badge--success',
        'withdrawn', 'deferred' => 'admin-badge--danger',
        default => '',
    };
@endphp

<header class="student-profile-hero">
    <div class="student-profile-hero__bar">
        <div class="student-profile-hero__start">
            <div class="student-profile-avatar" aria-hidden="true">{{ $student->initials() }}</div>
            <div class="student-profile-hero__titles">
                <p class="student-profile-hero__eyebrow">ملف الطالب · #{{ $student->id }}</p>
                <h1 class="student-profile-hero__name">{{ $student->name_ar }}</h1>
                @if ($student->name_en)
                    <p class="student-profile-hero__name-en" dir="ltr">{{ $student->name_en }}</p>
                @endif
            </div>
        </div>

        <dl class="student-profile-hero__stats">
            <div class="student-profile-hero__stat">
                <dt>الرقم الأكاديمي</dt>
                <dd dir="ltr">{{ $student->academic_id ?? '—' }}</dd>
            </div>
            <div class="student-profile-hero__stat">
                <dt>الهوية</dt>
                <dd dir="ltr">{{ $student->national_id ?? '—' }}</dd>
            </div>
            <div class="student-profile-hero__stat">
                <dt>الجوال</dt>
                <dd dir="ltr">{{ $student->mobile ?? '—' }}</dd>
            </div>
        </dl>

        <div class="student-profile-hero__end">
            <div class="student-profile-hero__badges">
                <span @class(['admin-badge', 'admin-badge--with-icon', $statusClass])>
                    {{ $student->study_status ?: AcademicStudentOptions::academicStatusLabel($student->academic_status) }}
                </span>
                @if ($student->login_allowed)
                    <span class="admin-badge admin-badge--success">مسموح بالدخول</span>
                @else
                    <span class="admin-badge admin-badge--danger">الدخول موقوف</span>
                @endif
            </div>
            <div class="student-profile-hero__actions">
                @canImpersonateStudent
                    @if ($student->canBeImpersonated())
                        <form method="post" action="{{ route('admin.students.impersonate', $student) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">دخول كطالب</button>
                        </form>
                    @else
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" disabled title="{{ $student->impersonationBlockReason() }}">دخول كطالب</button>
                    @endif
                @endcanImpersonateStudent
                <a href="{{ route('admin.students.edit', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل البيانات</a>
                <a href="{{ route('admin.students') }}" class="admin-btn-primary admin-btn-primary--sm">كافة الطلاب</a>
            </div>
        </div>
    </div>
</header>
