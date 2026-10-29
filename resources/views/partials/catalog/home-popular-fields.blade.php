@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $fields = collect($fields ?? []);
    $saudiLayout = (bool) ($saudiLayout ?? false);
@endphp

<section id="section-fields" @class(['popular-section', 'lg-fields-section', 'lg-fields-section--saudi' => $saudiLayout])>
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            @unless ($saudiLayout)
                <p class="home-catalog-section__eyebrow">{{ $isEn ? 'Platform specialties' : 'تخصصات المنصة' }}</p>
            @endunless
            <h2>{{ $saudiLayout ? ($isEn ? 'Browse all fields' : 'تصفح جميع المجالات') : ($isEn ? 'Explore our fields' : 'المجالات التدريبية') }}</h2>
            <p class="home-catalog-section__lead">{{ $saudiLayout ? ($isEn ? 'Training fields, from management to the job market. Choose a field to get started.' : 'مجالات التدريب من الإدارة والتسويق حتى المسار المهني. اختر مجالاً لتبدأ.') : ($isEn ? 'Choose a field and browse matching certificates and diplomas.' : 'اختر المجال ثم تصفّح الشهادات والدبلومات المناسبة لمسارك.') }}</p>
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
            <a href="{{ route('courses.index', ['locale' => $locale]) }}" @class(['btn btn-primary' => ! $saudiLayout, 'lg-fields-section__link' => $saudiLayout])>
                {{ $saudiLayout ? ($isEn ? 'Explore all training fields' : 'اكتشف جميع المجالات التدريبية') : ($isEn ? 'All fields' : 'جميع المجالات') }}
                @unless ($saudiLayout)
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                @endunless
            </a>
        </div>
    </div>
</section>
