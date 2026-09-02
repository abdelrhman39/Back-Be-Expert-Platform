@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $fields = collect($fields ?? []);
@endphp

<section id="section-fields" class="popular-section lg-fields-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <p class="home-catalog-section__eyebrow">{{ $isEn ? 'Platform specialties' : 'تخصصات المنصة' }}</p>
            <h2>{{ $isEn ? 'Explore our fields' : 'المجالات التدريبية' }}</h2>
            <p class="home-catalog-section__lead">{{ $isEn ? 'Choose a field and browse matching certificates and diplomas.' : 'اختر المجال ثم تصفّح الشهادات والدبلومات المناسبة لمسارك.' }}</p>
        </div>

        @if ($fields->isNotEmpty())
            <div class="lg-fields-grid" data-aos="fade-up">
                @foreach ($fields as $field)
                    <a class="lg-fields-card" href="{{ $field->coursesIndexUrl($locale) }}">
                        <span class="lg-fields-card__icon" aria-hidden="true">
                            <img src="{{ $field->iconUrl() }}" alt="">
                        </span>
                        <h3>{{ $field->displayTitle() }}</h3>
                        @if ((int) ($field->courses_count ?? 0) > 0)
                            <span class="lg-fields-card__meta">{{ $field->courses_count }} {{ $isEn ? 'programs' : 'برنامج' }}</span>
                        @else
                            <span class="lg-fields-card__meta">{{ $isEn ? 'Browse programs' : 'تصفح البرامج' }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-center text-muted py-4">{{ $isEn ? 'Fields will appear here once they are published.' : 'ستظهر المجالات هنا بعد نشرها.' }}</p>
        @endif

        <div class="home-catalog-section__cta">
            <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">
                {{ $isEn ? 'All fields' : 'جميع المجالات' }}
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>
