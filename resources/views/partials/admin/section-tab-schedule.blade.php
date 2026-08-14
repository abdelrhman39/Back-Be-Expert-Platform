@php
    use App\Support\AcademicScheduleOptions;
@endphp

<div class="admin-section-schedule-panel">
    @if ($schedule)
        <div class="admin-detail-grid admin-detail-grid--sections">
            <section class="admin-detail-section">
                <h3 class="admin-detail-section__title">
                    <span class="admin-detail-section__title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    موعد المحاضرة
                </h3>
                <div class="admin-detail-fields admin-detail-fields--2">
                    @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'اليوم', 'value' => AcademicScheduleOptions::dayLabel($schedule->day_of_week)])
                    @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'الوقت', 'value' => '<span dir="ltr">'.e(AcademicScheduleOptions::formatTimeRange($schedule->time_start, $schedule->time_end)).'</span>'])
                    @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'الفترة', 'value' => \App\Support\AcademicSectionOptions::periodLabel($schedule->period ?: $section->period)])
                    @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'المدرب', 'value' => e($schedule->displayTrainer())])
                </div>
            </section>

            <section class="admin-detail-section">
                <h3 class="admin-detail-section__title">
                    <span class="admin-detail-section__title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
                    </span>
                    السياق الأكاديمي
                </h3>
                <div class="admin-detail-fields admin-detail-fields--2">
                    @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'الفصل الدراسي', 'value' => \App\Support\AcademicBatchOptions::semesterLabel($schedule->semester_key, $section->displaySemester())])
                    @if ($section->level)
                        @include('partials.admin.detail-field', ['icon' => 'layers', 'label' => 'المستوى', 'value' => $section->level->name_ar])
                    @endif
                    @if ($section->batch)
                        @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'الدفعة', 'value' => '<a href="'.route('admin.batches.show', $section->batch).'" class="admin-link">'.e($section->batch->name).'</a>'])
                    @endif
                </div>
            </section>
        </div>

        @php
            $scheduleUrl = route('admin.schedules', array_filter([
                'semesterKey' => $schedule->semester_key ?: $section->semester_key,
                'batchId' => $schedule->batch_id ?: $section->batch_id,
                'levelId' => $schedule->level_id ?: $section->level_id,
                'period' => $schedule->period ?: $section->period,
            ]));
        @endphp
        <div class="admin-filter-actions" style="margin-top:1rem;">
            <a href="{{ $scheduleUrl }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض في الجداول الدراسية</a>
        </div>
    @else
        <div class="admin-table-empty-state" style="padding:2rem 1rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <p>لم يُربَط جدول دراسي بهذه الشعبة بعد.</p>
            @if ($section->batch_id && $section->level_id && $section->semester_key && $section->period)
                <a href="{{ route('admin.schedules', ['semesterKey' => $section->semester_key, 'batchId' => $section->batch_id, 'levelId' => $section->level_id, 'period' => $section->period]) }}" class="admin-btn-primary admin-btn-primary--sm">فتح الجداول الدراسية</a>
            @else
                <a href="{{ route('admin.schedules') }}" class="admin-btn-secondary admin-btn-secondary--sm">الجداول الدراسية</a>
            @endif
        </div>
    @endif
</div>
