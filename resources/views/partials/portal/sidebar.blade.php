@php
    use App\Support\PortalNavigation;

    $locale = app()->getLocale();
    $user = auth()->user();
    $portalActive = $portalActive ?? 'profile';
@endphp

<aside class="portal-sidebar user-sidebar">
    <div class="portal-drawer-head">
        <span class="portal-drawer-head__title">{{ app()->getLocale() === 'en' ? 'Menu' : 'القائمة' }}</span>
        <button type="button" class="portal-drawer-close" aria-label="{{ app()->getLocale() === 'en' ? 'Close menu' : 'إغلاق القائمة' }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="portal-sidebar-profile user-head">
        <a href="{{ route('settings', ['locale' => $locale]) }}" class="portal-sidebar-profile__settings" title="الإعدادات" aria-label="الإعدادات">
            <i class="fa-solid fa-gear"></i>
        </a>
        <div class="portal-sidebar-profile__avatar-wrap">
            <span class="portal-avatar portal-avatar--lg portal-avatar--circle">{{ $user?->initials() }}</span>
        </div>
        <div class="portal-sidebar-profile__info user-information">
            <h6 class="portal-sidebar-profile__name">{{ $user?->displayName() }}</h6>
            <p class="portal-sidebar-profile__email" dir="ltr" title="{{ $user?->email }}">{{ $user?->email }}</p>
            <span class="portal-sidebar-profile__role">متدرب</span>
        </div>
    </div>

    <nav class="portal-sidebar-nav user-body" aria-label="قائمة لوحة التحكم">
        <ul class="portal-sidebar-nav__list">
            @foreach (PortalNavigation::items() as $item)
                <li>
                    <a href="{{ $item['route'] }}" @class(['portal-sidebar-nav__link', 'active' => PortalNavigation::isActive($item['key'], $portalActive)])>
                        <i class="fa-solid {{ $item['icon'] ?? 'fa-circle' }} portal-sidebar-nav__icon" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="portal-sidebar-nav__footer">
            <a href="#" class="portal-sidebar-nav__link portal-sidebar-nav__link--logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-right-from-bracket portal-sidebar-nav__icon" aria-hidden="true"></i>
                <span>تسجيل الخروج</span>
            </a>
        </div>
    </nav>
</aside>
