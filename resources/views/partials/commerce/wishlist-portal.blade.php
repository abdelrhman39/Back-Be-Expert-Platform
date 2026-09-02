@include('partials.portal.shell-start', ['portalActive' => 'profile', 'portalTitle' => $t('title')])
<div class="portal-dashboard portal-commerce-page portal-cart-page">
    <section class="portal-commerce-hero">
        <div class="portal-commerce-hero__main">
            <span class="portal-commerce-hero__icon" aria-hidden="true">
                <i class="fa-regular fa-heart"></i>
            </span>
            <div class="portal-commerce-hero__text">
                <h1 class="portal-commerce-hero__title">{{ $t('title') }}</h1>
                <p class="portal-commerce-hero__desc">{{ $count > 0 ? $t('intro') : $t('intro_empty') }}</p>
            </div>
        </div>
        <div class="portal-commerce-hero__aside">
            <div class="portal-commerce-hero__stats">
                <div class="portal-commerce-hero__stat">
                    <span class="portal-commerce-hero__stat-label">{{ $t('items') }}</span>
                    <strong class="portal-commerce-hero__stat-value">{{ $count }}</strong>
                </div>
            </div>
        </div>
    </section>

    @if ($count === 0)
        <section class="portal-panel portal-commerce-empty-panel">
            <div class="portal-commerce-empty">
                <span class="portal-commerce-empty__icon" aria-hidden="true">
                    <i class="fa-regular fa-heart"></i>
                </span>
                <h2 class="portal-commerce-empty__title">{{ $t('empty_title') }}</h2>
                <p class="portal-commerce-empty__hint">{{ $t('empty_hint') }}</p>
                <div class="portal-commerce-empty__actions">
                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">
                        <i class="fa-solid fa-compass"></i> {{ $t('browse') }}
                    </a>
                    <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-outline-secondary">
                        {{ $t('cart') }}
                    </a>
                </div>
            </div>
        </section>
    @else
        <div class="row g-3 portal-commerce-layout">
            <div class="col-lg-8">
                <section class="portal-panel">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-regular fa-heart"></i> {{ $t('items_title') }}</h2>
                        <span class="portal-commerce-badge">{{ $count }}</span>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="commerce-list commerce-list--cart">
                            @foreach ($items as $item)
                                @include('components.commerce.wishlist-item', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <aside class="portal-panel portal-commerce-summary sticky-top" style="top:100px">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-bookmark"></i> {{ $t('summary_title') }}</h2>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="portal-commerce-summary__rows">
                            <div class="portal-commerce-summary__row portal-commerce-summary__row--total">
                                <span>{{ $t('saved_count') }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                        </div>
                        <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-primary w-100 portal-commerce-summary__checkout">
                            <i class="fa-solid fa-cart-shopping"></i> {{ $t('cart') }}
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    @endif
</div>
@include('partials.portal.shell-end')
