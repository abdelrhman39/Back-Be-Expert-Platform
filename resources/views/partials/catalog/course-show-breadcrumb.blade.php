@php
    $locale = app()->getLocale();
    $schedule = app(\App\Services\CatalogCourseService::class)->trainingSchedule($course);
    $isDiploma = $course->relationLoaded('categories')
        ? $course->categories->contains('id', \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS)
        : $course->categories()->whereKey(\App\Services\CatalogCourseService::CATEGORY_DIPLOMAS)->exists();
    $hours = $course->duration_hours ?: ($schedule['hours'] ?? null);
    $days = $course->duration_days ?: ($schedule['days'] ?? null);
    $indexLabel = $isDiploma ? 'الدبلومات' : 'الشهادات الإحترافية';
    $indexUrl = $isDiploma
        ? route('courses.index', ['locale' => $locale, 'categories' => [\App\Services\CatalogCourseService::CATEGORY_DIPLOMAS]])
        : route('courses.index', ['locale' => $locale, 'categories' => [\App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES]]);
    $detailLabel = $isDiploma ? 'تفاصيل الدبلوم' : 'تفاصيل الدورة';

    $facts = [];
    if ($course->duration_label) {
        $facts[] = ['icon' => 'fa-hourglass-half', 'label' => 'مدة البرنامج', 'value' => $course->duration_label];
    }
    if ($hours) {
        $facts[] = ['icon' => 'fa-clock', 'label' => 'عدد الساعات', 'value' => $hours.' ساعة تدريبية'];
    }
    if ($days) {
        $facts[] = ['icon' => 'fa-calendar-days', 'label' => 'الأيام', 'value' => $days.' يوم'];
    }
    $facts[] = [
        'icon' => match ($course->normalizedDeliveryType()) {
            'online' => 'fa-laptop',
            'both' => 'fa-layer-group',
            default => 'fa-building',
        },
        'label' => 'نوع التقديم',
        'value' => $course->deliveryModesLabel(),
    ];
    $facts[] = [
        'icon' => $course->installmentOffered() ? 'fa-calendar-check' : 'fa-money-bill-wave',
        'label' => 'خيارات السداد',
        'value' => $course->installmentLabel(),
    ];
    if ($course->city) {
        $facts[] = ['icon' => 'fa-location-dot', 'label' => 'المدينة', 'value' => $course->city];
    }
@endphp

<div class="breadcrumb-bar breadcrumb-bar-info breadcrumb-info course-braedcrumb">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ $indexUrl }}">{{ $indexLabel }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $detailLabel }}</li>
                    </ol>
                </nav>

                <div class="breadcrumb-info">
                    <div class="course-hero-pills">
                        <span class="course-hero-pills__item">{{ $isDiploma ? 'دبلوم أكاديمي' : 'شهادة احترافية' }}</span>
                        <span class="course-hero-pills__item course-hero-pills__item--soft">{{ $course->deliveryModesLabel() }}</span>
                        @if ($course->installmentOffered())
                            <span class="course-hero-pills__item course-hero-pills__item--accent">تقسيط متاح</span>
                        @endif
                    </div>
                    <h1 class="breadcrumb-title my-3">{{ $course->displayTitle() }}</h1>
                </div>
            </div>

            @if ($facts !== [])
                <div class="col-12">
                    <div class="course-hero-facts" role="list">
                        @foreach ($facts as $fact)
                            <div class="course-hero-facts__item" role="listitem">
                                <span class="course-hero-facts__icon" aria-hidden="true">
                                    <i class="fa-solid {{ $fact['icon'] }}"></i>
                                </span>
                                <span class="course-hero-facts__text">
                                    <em>{{ $fact['label'] }}</em>
                                    <strong>{{ $fact['value'] }}</strong>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
