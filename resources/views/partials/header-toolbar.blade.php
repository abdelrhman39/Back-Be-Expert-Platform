@php
    $locale = app()->getLocale();
    $user = portal_user();
    $alternateLocale = $locale === 'ar' ? 'en' : 'ar';
    $pathWithoutLocale = preg_replace('#^'.preg_quote($locale, '#').'(/|$)#', '', request()->path());
    $alternateLocaleUrl = url('/'.$alternateLocale.($pathWithoutLocale ? '/'.$pathWithoutLocale : ''));
@endphp

<div class="site-header-actions">
    <a hreflang="{{ $alternateLocale }}" class="site-header-actions__lang" href="{{ $alternateLocaleUrl }}">
        {{ strtoupper($alternateLocale) }}
    </a>

    <div class="site-header-actions__tools">
        <a href="{{ route('wishlist', ['locale' => $locale]) }}" class="site-header-actions__icon" title="{{ public_copy('wishlist') }}" aria-label="{{ public_copy('wishlist') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7.5-4.35-9.5-8.5C1.2 9.2 2.6 5.5 6 5.5c1.9 0 3.2 1 4 2.1.8-1.1 2.1-2.1 4-2.1 3.4 0 4.8 3.7 3.5 7-2 4.15-9.5 8.5-9.5 8.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
            <span class="site-header-actions__badge" id="wichCount" @if(!($wishlistCount ?? 0)) style="display:none" @endif>{{ $wishlistCount ?? 0 }}</span>
        </a>
        <a href="{{ route('cart', ['locale' => $locale]) }}" class="site-header-actions__icon" title="{{ public_copy('cart') }}" aria-label="{{ public_copy('cart') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12L6 6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M6 6 5 3H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="20" r="1" fill="currentColor"/><circle cx="18" cy="20" r="1" fill="currentColor"/></svg>
            <span class="site-header-actions__badge" id="cartCount" @if(!($cartCount ?? 0)) style="display:none" @endif>{{ $cartCount ?? 0 }}</span>
        </a>
        @if (portal_authenticated())
            <livewire:shared.notification-bell panel="portal" wire:key="portal-notif-bell" />
        @endif
    </div>

    @if (portal_authenticated() && $user)
        <div class="site-header-actions__user dropdowns">
            <button type="button" class="site-header-actions__profile toggle" aria-haspopup="true" aria-expanded="false">
                <span class="site-header-actions__avatar">{{ $user->initials() }}</span>
                <span class="site-header-actions__profile-text">
                    <span class="site-header-actions__name">{{ $user->displayName() }}</span>
                    <span class="site-header-actions__role">{{ public_copy('learner') }}</span>
                </span>
                <i class="fa-solid fa-chevron-down site-header-actions__chevron" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu site-header-actions__menu">
                <div class="site-header-actions__menu-head">
                    <span class="site-header-actions__avatar site-header-actions__avatar--lg">{{ $user->initials() }}</span>
                    <div>
                        <strong>{{ $user->displayName() }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>
                <a class="site-header-actions__menu-item" href="{{ route('profile', ['locale' => $locale]) }}">
                    <i class="fa-solid fa-gauge-high"></i><span>{{ public_copy('dashboard') }}</span>
                </a>
                <a class="site-header-actions__menu-item" href="{{ route('learning-list', ['locale' => $locale]) }}">
                    <i class="fa-solid fa-book-open"></i><span>{{ public_copy('learning_list') }}</span>
                </a>
                <a class="site-header-actions__menu-item" href="{{ route('my-orders', ['locale' => $locale]) }}">
                    <i class="fa-solid fa-bag-shopping"></i><span>{{ public_copy('my_orders') }}</span>
                </a>
                <a class="site-header-actions__menu-item" href="{{ route('settings', ['locale' => $locale]) }}">
                    <i class="fa-solid fa-gear"></i><span>{{ public_copy('settings') }}</span>
                </a>
                <div class="site-header-actions__menu-divider"></div>
                <a class="site-header-actions__menu-item site-header-actions__menu-item--logout log-out" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-right-from-bracket"></i><span>{{ public_copy('logout') }}</span>
                </a>
            </div>
        </div>
    @else
            <a href="{{ route('login', ['locale' => $locale]) }}" class="btn btn-primary site-header-actions__login">
                {{ public_copy('login') }}
            </a>
    @endif
</div>
