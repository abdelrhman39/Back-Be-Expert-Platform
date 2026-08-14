@php
    $locale = $locale ?? app()->getLocale();
    $showActions = $showActions ?? false;
@endphp

<article @class(['portal-inst-sec', 'portal-inst-sec--rich' => $showActions])>
    <a href="{{ route('instructor.sections.show', ['locale' => $locale, 'section' => $section->id]) }}" class="portal-inst-sec__link">
        <div class="portal-inst-sec__media">
            <span class="portal-inst-sec__icon" aria-hidden="true"><i class="fa-solid fa-chalkboard-user"></i></span>
            <div class="portal-inst-sec__media-top">
                <span class="portal-inst-sec__code" dir="ltr">{{ $section->code }}</span>
                <span class="portal-inst-sec__term">{{ $section->displaySemester() }}</span>
            </div>
            @if ($section->program?->name_ar)
                <span class="portal-inst-sec__program">{{ $section->program->name_ar }}</span>
            @endif
        </div>
        <div class="portal-inst-sec__body">
            <h3 class="portal-inst-sec__title">{{ $section->name }}</h3>
            <p class="portal-inst-sec__course">{{ $section->course?->name_ar ?: ($section->subtitle ?: 'مقرر غير محدد') }}</p>
            <div class="portal-inst-sec__meta">
                <span><i class="fa-solid fa-users"></i> {{ number_format((int) $section->students_count) }} طالب</span>
                @if ($section->schedule)
                    <span dir="ltr"><i class="fa-solid fa-clock"></i> {{ \Illuminate\Support\Str::of($section->schedule->time_start)->substr(0, 5) }}–{{ \Illuminate\Support\Str::of($section->schedule->time_end)->substr(0, 5) }}</span>
                @endif
                @if ($section->batch?->name)
                    <span><i class="fa-solid fa-layer-group"></i> {{ $section->batch->name }}</span>
                @endif
            </div>
        </div>
    </a>
    @if ($showActions)
        <div class="portal-inst-sec__actions">
            <a href="{{ route('instructor.sections.show', ['locale' => $locale, 'section' => $section->id]) }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-door-open"></i> إدارة الشعبة
            </a>
            @canInstructor('instructor.sections.view_all_students')
                <a href="{{ route('instructor.sections.roster', ['locale' => $locale, 'section' => $section->id]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-users"></i> الطلاب
                </a>
            @endcanInstructor
            @canInstructor('instructor.exams.view')
                <a href="{{ route('instructor.exams', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-file-circle-check"></i> اختبارات
                </a>
            @endcanInstructor
        </div>
    @endif
</article>
