@include('partials.portal.shell-start', ['portalActive' => 'profile', 'portalTitle' => $t('title')])
<div class="portal-dashboard portal-commerce-page portal-cart-page">
    <section class="portal-commerce-hero">
        <div class="portal-commerce-hero__main">
            <span class="portal-commerce-hero__icon" aria-hidden="true">
                <i class="fa-solid fa-cart-shopping"></i>
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
                @if ($count > 0)
                    <div class="portal-commerce-hero__stat portal-commerce-hero__stat--total">
                        <span class="portal-commerce-hero__stat-label">{{ $t('total') }}</span>
                        <strong class="portal-commerce-hero__stat-value" dir="ltr">{{ number_format($total, 2) }} <small>{{ $sar }}</small></strong>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($count === 0)
        <section class="portal-panel portal-commerce-empty-panel">
            <div class="portal-commerce-empty">
                <span class="portal-commerce-empty__icon" aria-hidden="true">
                    <i class="fa-solid fa-cart-shopping"></i>
                </span>
                <h2 class="portal-commerce-empty__title">{{ $t('empty_title') }}</h2>
                <p class="portal-commerce-empty__hint">{{ $t('empty_hint') }}</p>
                <div class="portal-commerce-empty__actions">
                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">
                        <i class="fa-solid fa-compass"></i> {{ $t('browse') }}
                    </a>
                    <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="btn btn-outline-secondary">
                        {{ $t('learning_list') }}
                    </a>
                </div>
            </div>
        </section>
    @else
        <div class="row g-3 portal-commerce-layout">
            <div class="col-lg-8">
                <section class="portal-panel">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-list"></i> {{ $t('items_title') }}</h2>
                        <span class="portal-commerce-badge">{{ $count }}</span>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="commerce-list commerce-list--cart">
                            @foreach ($items as $item)
                                @include('components.commerce.cart-item', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <aside class="portal-panel portal-commerce-summary sticky-top" style="top:100px">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-receipt"></i> {{ $t('summary_title') }}</h2>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="cart-summary-items">
                            @foreach ($items as $item)
                                @php($meta = app(\App\Support\LegacyCourseCatalog::class)->resolveForItem($item))
                                <div class="cart-summary-item">
                                    <a href="{{ $meta['url'] }}" class="cart-summary-item__title">{{ \Illuminate\Support\Str::limit($meta['title'], 42) }}</a>
                                    <span class="cart-summary-item__price" dir="ltr">{{ number_format((float) $item->price_snapshot, 0) }} {{ $sar }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="portal-commerce-summary__rows">
                            <div class="portal-commerce-summary__row">
                                <span>{{ $t('program_count') }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                            <div class="portal-commerce-summary__row portal-commerce-summary__row--total">
                                <span>{{ $t('total') }}</span>
                                <strong dir="ltr">{{ number_format($total, 2) }} <small>{{ $sar }}</small></strong>
                            </div>
                        </div>
                        <a href="{{ route('checkout', ['locale' => $locale]) }}" class="btn btn-primary w-100 portal-commerce-summary__checkout">
                            <i class="fa-solid fa-credit-card"></i> {{ $t('checkout') }}
                        </a>
                        <p class="portal-commerce-summary__note">
                            <i class="fa-solid fa-shield-halved"></i>
                            {{ $t('secure') }}
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    @endif
</div>
@include('partials.portal.shell-end')
