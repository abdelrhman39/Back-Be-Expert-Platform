<div class="apply-page cart-page wishlist-page">
    <header class="apply-hero">
        <div class="container">
            <nav aria-label="breadcrumb" class="apply-hero__crumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['locale' => $locale]) }}">{{ \App\Support\PublicCopy::chrome('home', $locale) }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $t('title') }}</li>
                </ol>
            </nav>
            <p class="apply-hero__eyebrow">{{ platform_name() }}</p>
            <h1 class="apply-hero__title">{{ $t('title') }}</h1>
            <p class="apply-hero__lead">{{ $count > 0 ? $t('intro') : $t('intro_empty') }}</p>
            @if ($count > 0)
                <div class="cart-hero-stats">
                    <span class="cart-hero-stats__item">{{ $t('items') }}: <strong>{{ $count }}</strong></span>
                </div>
            @endif
        </div>
    </header>

    <div class="apply-form-page">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="apply-form-card">
                        @if ($count === 0)
                            <div class="cart-empty cart-empty--wishlist">
                                <span class="cart-empty__icon" aria-hidden="true"><i class="fa-regular fa-heart"></i></span>
                                <h2 class="cart-empty__title">{{ $t('empty_title') }}</h2>
                                <p class="cart-empty__hint">{{ $t('empty_hint') }}</p>
                                <div class="cart-empty__actions">
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary apply-submit">{{ $t('browse') }}</a>
                                    <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-outline-primary">{{ $t('cart') }}</a>
                                </div>
                            </div>
                        @else
                            <div class="apply-form-section">
                                <div class="apply-form-section__head">
                                    <span class="apply-form-section__num">1</span>
                                    <h2 class="apply-form-section__title">{{ $t('items_title') }}</h2>
                                    <span class="cart-count-badge">{{ $count }}</span>
                                </div>
                                <div class="commerce-list commerce-list--cart">
                                    @foreach ($items as $item)
                                        @include('components.commerce.wishlist-item', ['item' => $item])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    <aside class="apply-sidebar cart-sticky-panel">
                        @if ($count > 0)
                            <div class="apply-sidebar-card">
                                <h3>{{ $t('summary_title') }}</h3>
                                <div class="cart-summary-rows">
                                    <div class="cart-summary-rows__row cart-summary-rows__row--total">
                                        <span>{{ $t('saved_count') }}</span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                </div>
                                <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-primary w-100 apply-submit mb-3">
                                    <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i> {{ $t('cart') }}
                                </a>
                                <ul class="apply-org-points mb-3">
                                    <li>{{ $t('point_1') }}</li>
                                    <li>{{ $t('point_2') }}</li>
                                    <li>{{ $t('point_3') }}</li>
                                </ul>
                                @guest
                                    <p class="apply-sidebar-lead mb-2">{{ $t('login_note') }}</p>
                                    <a href="{{ route('login', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">{{ $t('login') }}</a>
                                @endguest
                            </div>
                        @endif

                        <div class="apply-sidebar-card">
                            <h3>{{ $t('how_title') }}</h3>
                            <ol class="apply-steps">
                                <li><span class="apply-steps__icon">1</span><span>{{ $t('how_1') }}</span></li>
                                <li><span class="apply-steps__icon">2</span><span>{{ $t('how_2') }}</span></li>
                                <li><span class="apply-steps__icon">3</span><span>{{ $t('how_3') }}</span></li>
                            </ol>
                        </div>

                        @if ($count === 0)
                            <div class="apply-sidebar-card">
                                <h3>{{ $t('links_title') }}</h3>
                                <div class="apply-sidebar-links">
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}">
                                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                                        {{ $t('browse') }}
                                    </a>
                                    <a href="{{ route('cart', ['locale' => $locale]) }}">
                                        <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                                        {{ $t('cart') }}
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
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>
