@php
    use App\Support\AcademicBatchOptions;

    $statusBadge = match ($batch->status) {
        'active' => '<span class="admin-badge admin-badge--success">'.AcademicBatchOptions::statusLabel($batch->status).'</span>',
        'planned' => '<span class="admin-badge admin-badge--warn">'.AcademicBatchOptions::statusLabel($batch->status).'</span>',
        default => '<span class="admin-badge">'.e(AcademicBatchOptions::statusLabel($batch->status)).'</span>',
    };
@endphp

<div class="admin-detail-grid admin-detail-grid--sections">
    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </span>
            بيانات الدفعة
        </h3>
        <div class="admin-detail-fields admin-detail-fields--2">
            @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'اسم الدفعة', 'value' => $batch->name])
            @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'كود الدفعة', 'value' => '<code class="admin-code">'.e($batch->code).'</code>'])
            @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'حالة الدفعة', 'value' => $statusBadge, 'tone' => 'success'])
            @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'فصل القبول', 'value' => $batch->displaySemester()])
            @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'نمط الدراسة', 'value' => AcademicBatchOptions::studyModeLabel($batch->study_mode)])
            @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'منسق الدفعة', 'value' => $batch->coordinator ?: '—'])
        </div>
    </section>

    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </span>
            الجدولة والسعة
        </h3>
        <div class="admin-detail-fields admin-detail-fields--2">
            @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'تاريخ البدء', 'value' => $batch->start_date?->format('Y-m-d') ?? '—'])
            @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'تاريخ الانتهاء', 'value' => $batch->end_date?->format('Y-m-d') ?? '—'])
            @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'عدد الطلاب', 'value' => (string) $batch->students_count])
            @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'السعة القصوى', 'value' => $batch->capacity ? (string) $batch->capacity : '—'])
            @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'التسجيل', 'value' => $batch->enrollment_open ? '<span class="admin-badge admin-badge--success">مفتوح</span>' : '<span class="admin-badge admin-badge--danger">مغلق</span>'])
        </div>
    </section>

    @if ($batch->program)
        <section class="admin-detail-section admin-detail-section--wide">
            <h3 class="admin-detail-section__title">
                <span class="admin-detail-section__title-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
                </span>
                البرنامج المرتبط
            </h3>
            <div class="admin-detail-fields admin-detail-fields--3">
                @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'اسم البرنامج', 'value' => '<a href="'.route('admin.programs.show', $batch->program).'" class="admin-link">'.e($batch->program->name_ar).'</a>'])
                @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'كود البرنامج', 'value' => '<code class="admin-code">'.e($batch->program->code).'</code>'])
                @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'مدة البرنامج', 'value' => $batch->program->displayDuration()])
            </div>
        </section>
    @endif

    @if ($batch->notes)
        <section class="admin-detail-section admin-detail-section--wide">
            <h3 class="admin-detail-section__title">
                <span class="admin-detail-section__title-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                </span>
                ملاحظات
            </h3>
            <p class="admin-detail-text">{{ $batch->notes }}</p>
        </section>
    @endif
</div>
