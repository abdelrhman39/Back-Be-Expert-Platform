@php
    $locale = app()->getLocale();
    $courseUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
@endphp

<div class="course-enroll-hero">
    <div class="container">
        <nav aria-label="breadcrumb" class="course-enroll-hero__crumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('courses.index', ['locale' => $locale]) }}">الشهادات الإحترافية</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ $courseUrl }}">{{ \Illuminate\Support\Str::limit($course->displayTitle(), 48) }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">التسجيل</li>
            </ol>
        </nav>

        <a href="{{ $courseUrl }}" class="course-enroll-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M14 6L8 12L14 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>العودة لتفاصيل البرنامج</span>
        </a>

        <div class="course-enroll-hero__head">
            <div>
                <h1 class="course-enroll-hero__title">التسجيل في البرنامج</h1>
                <p class="course-enroll-hero__subtitle">{{ $course->displayTitle() }}</p>
            </div>
        </div>

        <div class="course-enroll-steps" aria-label="خطوات التسجيل">
            <div class="course-enroll-step is-active">
                <span class="course-enroll-step__num">1</span>
                <span class="course-enroll-step__label">بيانات التسجيل</span>
            </div>
            <span class="course-enroll-step__sep" aria-hidden="true"></span>
            <div class="course-enroll-step">
                <span class="course-enroll-step__num">2</span>
                <span class="course-enroll-step__label">الدفع</span>
            </div>
        </div>
    </div>
</div>
