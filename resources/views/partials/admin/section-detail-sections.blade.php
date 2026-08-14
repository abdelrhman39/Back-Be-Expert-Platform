@php
    use App\Support\AcademicBatchOptions;
    use App\Support\AcademicSectionOptions;

    $statusBadge = $section->status === 'active'
        ? '<span class="admin-badge admin-badge--success">'.AcademicSectionOptions::statusLabel($section->status).'</span>'
        : '<span class="admin-badge admin-badge--danger">'.AcademicSectionOptions::statusLabel($section->status).'</span>';
@endphp

<div class="admin-detail-grid admin-detail-grid--sections">
    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            </span>
            المعلومات الأساسية
        </h3>
        <div class="admin-detail-fields admin-detail-fields--2">
            @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'اسم الشعبة', 'value' => $section->name])
            @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'رمز الشعبة', 'value' => '<code class="admin-code">'.e($section->code).'</code>'])
            @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'السعة القصوى', 'value' => (string) $section->max_capacity])
            @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'عدد الطلاب', 'value' => (string) $section->students_count])
            @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'الحالة', 'value' => $statusBadge])
            @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'الفترة', 'value' => AcademicSectionOptions::periodLabel($section->period)])
        </div>
    </section>

    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
            </span>
            المقرر والبرنامج
        </h3>
        <div class="admin-detail-fields admin-detail-fields--2">
            @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'المقرر', 'value' => $section->course ? '<a href="'.route('admin.academic-courses.show', $section->course).'" class="admin-link">'.e($section->course->name_ar).'</a>' : ($section->subtitle ?: '—')])
            @include('partials.admin.detail-field', ['icon' => 'layers', 'label' => 'المستوى', 'value' => $section->level?->name_ar ?? '—'])
            @if ($section->program)
                @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'البرنامج', 'value' => '<a href="'.route('admin.programs.show', $section->program).'" class="admin-link">'.e($section->program->name_ar).'</a>'])
            @endif
            @if ($section->batch)
                @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'الدفعة', 'value' => '<a href="'.route('admin.batches.show', $section->batch).'" class="admin-link">'.e($section->batch->name).'</a>'])
            @endif
            @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'فصل القبول', 'value' => AcademicBatchOptions::semesterLabel($section->semester_key, $section->semester)])
            @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'المشرف', 'value' => $section->supervisor ?: '—'])
            @if ($section->trainerName() !== '—')
                @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'عضو هيئة التدريس', 'value' => $section->trainerName()])
            @endif
        </div>
    </section>
</div>
