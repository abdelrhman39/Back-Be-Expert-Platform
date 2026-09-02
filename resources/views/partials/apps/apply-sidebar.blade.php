@php
    $locale = app()->getLocale();
    $type = $type ?? 'client';
    $t = fn (string $key) => \App\Support\PublicCopy::apply($key, $locale);
    $typed = fn (string $key) => \App\Support\PublicCopy::applyForType($key, $type, $locale);
    $sidebarVariant = in_array($type, ['company', 'instructor', 'cooperative'], true) ? $type : 'client';
@endphp

<aside class="apply-sidebar">
    <div class="apply-sidebar-card">
        <h3>{{ $t('how_title') }}</h3>
        <ol class="apply-steps">
            <li>
                <span class="apply-steps__icon">1</span>
                <span>{{ $typed('how_1') }}</span>
            </li>
            <li>
                <span class="apply-steps__icon">2</span>
                <span>{{ $typed('how_2') }}</span>
            </li>
            <li>
                <span class="apply-steps__icon">3</span>
                <span>{{ $typed('how_3') }}</span>
            </li>
        </ol>
    </div>

    @if ($sidebarVariant === 'company')
        <div class="apply-sidebar-card">
            <h3>{{ $t('org_points_title') }}</h3>
            <ul class="apply-org-points">
                <li>{{ $t('org_point_1') }}</li>
                <li>{{ $t('org_point_2') }}</li>
                <li>{{ $t('org_point_3') }}</li>
            </ul>
        </div>
    @elseif ($sidebarVariant === 'instructor')
        <div class="apply-sidebar-card">
            <h3>{{ $t('instructor_points_title') }}</h3>
            <ul class="apply-org-points">
                <li>{{ $t('instructor_point_1') }}</li>
                <li>{{ $t('instructor_point_2') }}</li>
                <li>{{ $t('instructor_point_3') }}</li>
            </ul>
        </div>
    @elseif ($sidebarVariant === 'cooperative')
        <div class="apply-sidebar-card">
            <h3>{{ $t('cooperative_points_title') }}</h3>
            <ul class="apply-org-points">
                <li>{{ $t('cooperative_point_1') }}</li>
                <li>{{ $t('cooperative_point_2') }}</li>
                <li>{{ $t('cooperative_point_3') }}</li>
            </ul>
        </div>
    @endif

    <div class="apply-sidebar-card">
        <h3>{{ $t('links_title') }}</h3>
        <div class="apply-sidebar-links">
            <a href="{{ route('apply.track', ['locale' => $locale]) }}">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                {{ $t('track_prev') }}
            </a>
            <a href="{{ route('courses.index', ['locale' => $locale]) }}">
                <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                {{ $t('browse') }}
            </a>
            <a href="{{ route('contact', ['locale' => $locale]) }}">
                <i class="fa-solid fa-headset" aria-hidden="true"></i>
                {{ \App\Support\PublicCopy::chrome('contact', $locale) }}
            </a>
            <a href="{{ route('support.faq', ['locale' => $locale]) }}">
                <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                {{ $t('faq') }}
            </a>
        </div>
    </div>

    @if ($sidebarVariant === 'company')
        <div class="apply-sidebar-card apply-sidebar-card--soft">
            <h3>{{ $t('org_cta_title') }}</h3>
            <p class="small text-muted mb-3">{{ $t('org_cta_lead') }}</p>
            <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">{{ $t('org_cta') }}</a>
        </div>
    @elseif ($sidebarVariant === 'instructor')
        <div class="apply-sidebar-card apply-sidebar-card--soft">
            <h3>{{ $t('instructor_cta_title') }}</h3>
            <p class="small text-muted mb-3">{{ $t('instructor_cta_lead') }}</p>
            <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">{{ $t('instructor_cta') }}</a>
        </div>
    @elseif ($sidebarVariant === 'cooperative')
        <div class="apply-sidebar-card apply-sidebar-card--soft">
            <h3>{{ $t('cooperative_cta_title') }}</h3>
            <p class="small text-muted mb-3">{{ $t('cooperative_cta_lead') }}</p>
            <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">{{ $t('cooperative_cta') }}</a>
        </div>
    @else
        <div class="apply-sidebar-card apply-sidebar-card--soft">
            <h3>{{ $t('need_account') }}</h3>
            <a href="{{ route('register', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">{{ $t('create_account') }}</a>
        </div>
    @endif

    @auth
        <div class="apply-sidebar-card">
            <h3>{{ $t('account_title') }}</h3>
            <p class="small text-muted mb-2">{{ $t('account_lead') }}</p>
            <p class="small mb-3"><strong>{{ auth()->user()->displayName() }}</strong></p>
            <a href="{{ route('profile', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">{{ $t('profile') }}</a>
        </div>
    @endauth
</aside>
