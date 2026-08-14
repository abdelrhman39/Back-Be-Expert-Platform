@php use App\Support\AcademicStudentOptions; @endphp

<div class="student-profile-board">
    <section class="student-profile-card student-profile-card--personal">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <h2 class="student-profile-card__title">المعلومات الشخصية</h2>
        </header>
        <div class="student-profile-card__body">
            <div class="admin-info-grid admin-info-grid--3">
                @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'الاسم (عربي)', 'value' => $student->name_ar])
                @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'الاسم (إنجليزي)', 'value' => $student->name_en ?: '—'])
                @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'الرقم الأكاديمي', 'value' => '<code class="admin-code">'.e($student->academic_id).'</code>'])
                @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'رقم الهوية', 'value' => $student->national_id ?: '—'])
                @include('partials.admin.info-card', ['icon' => 'phone', 'label' => 'الجوال', 'value' => $student->mobile ? '<a href="tel:'.e($student->mobile).'" class="admin-link" dir="ltr">'.e($student->mobile).'</a>' : '—'])
                @include('partials.admin.info-card', ['icon' => 'mail', 'label' => 'البريد', 'value' => $student->email ? '<a href="mailto:'.e($student->email).'" class="admin-link" dir="ltr">'.e($student->email).'</a>' : '—'])
                @include('partials.admin.info-card', ['icon' => 'flag', 'label' => 'الجنس', 'value' => $student->gender ?: '—'])
                @include('partials.admin.info-card', ['icon' => 'pin', 'label' => 'المدينة', 'value' => $student->city ?: '—'])
            </div>
        </div>
    </section>

    <section class="student-profile-card student-profile-card--status">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <h2 class="student-profile-card__title">الصلاحيات والحالة</h2>
        </header>
        <div class="student-profile-card__body">
            <div class="student-status-strip">
                <div class="student-status-strip__item">
                    <span class="student-status-strip__label">حالة الطالب</span>
                    <span class="admin-badge admin-badge--success">{{ $student->study_status ?: AcademicStudentOptions::academicStatusLabel($student->academic_status) }}</span>
                </div>
                <div class="student-status-strip__item">
                    <span class="student-status-strip__label">تسجيل الدخول</span>
                    @if ($student->login_allowed)
                        <span class="admin-badge admin-badge--success">مفعّل</span>
                    @else
                        <span class="admin-badge admin-badge--danger">موقوف</span>
                    @endif
                </div>
                @if ($student->user)
                    <div class="student-status-strip__item">
                        <span class="student-status-strip__label">حساب المنصة</span>
                        <a href="{{ route('admin.users.show', $student->user) }}" class="admin-link">عرض المستخدم #{{ $student->user_id }}</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<footer class="student-profile-meta-bar">
    <div class="student-profile-meta-bar__item">
        <span class="student-profile-meta-bar__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </span>
        <div>
            <span class="student-profile-meta-bar__label">تاريخ الانضمام</span>
            <span class="student-profile-meta-bar__value">
                @if ($student->joined_at)
                    <time datetime="{{ $student->joined_at->toIso8601String() }}">{{ $student->joined_at->format('Y-m-d H:i') }}</time>
                    <span class="student-profile-meta-bar__ago"> · {{ $student->joined_at->diffForHumans() }}</span>
                @else
                    —
                @endif
            </span>
        </div>
    </div>
    @if ($student->batch)
        <div class="student-profile-meta-bar__item">
            <span class="student-profile-meta-bar__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
            </span>
            <div>
                <span class="student-profile-meta-bar__label">الدفعة</span>
                <span class="student-profile-meta-bar__value">
                    <a href="{{ route('admin.batches.show', $student->batch) }}" class="admin-link">{{ $student->batch->name }}</a>
                </span>
            </div>
        </div>
    @endif
    @if ($student->section)
        <div class="student-profile-meta-bar__item">
            <span class="student-profile-meta-bar__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            </span>
            <div>
                <span class="student-profile-meta-bar__label">الشعبة</span>
                <span class="student-profile-meta-bar__value">
                    <a href="{{ route('admin.sections.show', $student->section) }}" class="admin-link">{{ $student->section->name }}</a>
                    <code class="admin-code admin-code--inline">{{ $student->section->code }}</code>
                </span>
            </div>
        </div>
    @endif
</footer>
