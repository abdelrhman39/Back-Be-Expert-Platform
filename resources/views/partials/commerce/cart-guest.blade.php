<div class="apply-page cart-page">
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
                    <span class="cart-hero-stats__item">{{ $t('total') }}: <strong dir="ltr">{{ number_format($total, 0) }} {{ $sar }}</strong></span>
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
                            <div class="cart-empty">
                                <span class="cart-empty__icon" aria-hidden="true"><i class="fa-solid fa-cart-shopping"></i></span>
                                <h2 class="cart-empty__title">{{ $t('empty_title') }}</h2>
                                <p class="cart-empty__hint">{{ $t('empty_hint') }}</p>
                                <div class="cart-empty__actions">
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary apply-submit">{{ $t('browse') }}</a>
                                    <a href="{{ route('wishlist', ['locale' => $locale]) }}" class="btn btn-outline-primary">{{ $t('wishlist') }}</a>
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
                                        @include('components.commerce.cart-item', ['item' => $item])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    @if ($count > 0)
                        <aside class="apply-sidebar cart-sticky-panel">
                            <div class="apply-sidebar-card">
                                <h3>{{ $t('guest_title') }}</h3>
                                <p class="apply-sidebar-lead">{{ $t('guest_lead') }}</p>

                                <div class="cart-summary-rows">
                                    <div class="cart-summary-rows__row">
                                        <span>{{ $t('program_count') }}</span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                    <div class="cart-summary-rows__row cart-summary-rows__row--total">
                                        <span>{{ $t('total') }}</span>
                                        <strong dir="ltr">{{ number_format($total, 0) }} {{ $sar }}</strong>
                                    </div>
                                </div>

                                <form wire:submit="registerAndCheckout" class="cart-guest-form">
                                    <div class="apply-form-field mb-3">
                                        <label class="form-label" for="guest-name">{{ $t('name') }} <span class="text-danger">*</span></label>
                                        <input id="guest-name" type="text" class="form-control @error('guestName') is-invalid @enderror" wire:model="guestName" placeholder="{{ $t('name_ph') }}" autocomplete="name">
                                        @error('guestName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="apply-form-field mb-3">
                                        <label class="form-label" for="guest-email">{{ $t('email') }} <span class="text-danger">*</span></label>
                                        <input id="guest-email" type="email" class="form-control @error('guestEmail') is-invalid @enderror" wire:model="guestEmail" placeholder="name@example.com" dir="ltr" autocomplete="email">
                                        @error('guestEmail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="apply-form-field mb-3">
                                        <label class="form-label" for="guest-phone">{{ $t('phone') }} <span class="text-danger">*</span></label>
                                        <div class="apply-tel input-group">
                                            <span class="input-group-text apply-tel__prefix" dir="ltr">🇸🇦 +966</span>
                                            <input id="guest-phone" type="tel" class="form-control @error('guestPhone') is-invalid @enderror" wire:model="guestPhone" placeholder="5xxxxxxxx" dir="ltr" autocomplete="tel">
                                        </div>
                                        <p class="apply-form-field__hint">{{ $t('phone_hint') }}</p>
                                        @error('guestPhone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 apply-submit" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="registerAndCheckout">
                                            <i class="fa-solid fa-user-plus" aria-hidden="true"></i> {{ $t('submit') }}
                                        </span>
                                        <span wire:loading wire:target="registerAndCheckout">{{ $t('submitting') }}</span>
                                    </button>
                                </form>

                                <ul class="apply-org-points mt-3 mb-3">
                                    <li>{{ $t('trust_1') }}</li>
                                    <li>{{ $t('trust_2') }}</li>
                                    <li>{{ $t('trust_3') }}</li>
                                </ul>
                                <p class="apply-secure mb-3"><i class="fa-solid fa-shield-halved"></i> {{ $t('secure') }}</p>
                                <p class="text-center small text-muted mb-0">
                                    {{ $t('have_account') }}
                                    <a class="fw-semibold" href="{{ route('login', ['locale' => $locale]) }}">{{ $t('login') }}</a>
                                </p>
                            </div>

                            <div class="apply-sidebar-card">
                                <h3>{{ $t('how_title') }}</h3>
                                <ol class="apply-steps">
                                    <li><span class="apply-steps__icon">1</span><span>{{ $t('how_1') }}</span></li>
                                    <li><span class="apply-steps__icon">2</span><span>{{ $t('how_2') }}</span></li>
                                    <li><span class="apply-steps__icon">3</span><span>{{ $t('how_3') }}</span></li>
                                </ol>
                            </div>
                        </aside>
                    @else
                        <aside class="apply-sidebar">
                            <div class="apply-sidebar-card">
                                <h3>{{ $t('how_title') }}</h3>
                                <ol class="apply-steps">
                                    <li><span class="apply-steps__icon">1</span><span>{{ $t('how_1') }}</span></li>
                                    <li><span class="apply-steps__icon">2</span><span>{{ $t('how_2') }}</span></li>
                                    <li><span class="apply-steps__icon">3</span><span>{{ $t('how_3') }}</span></li>
                                </ol>
                            </div>
                            <div class="apply-sidebar-card">
                                <h3>{{ $t('links_title') }}</h3>
                                <div class="apply-sidebar-links">
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}">
                                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                                        {{ $t('browse') }}
                                    </a>
                                    <a href="{{ route('wishlist', ['locale' => $locale]) }}">
                                        <i class="fa-regular fa-heart" aria-hidden="true"></i>
                                        {{ $t('wishlist') }}
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
                        </aside>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
