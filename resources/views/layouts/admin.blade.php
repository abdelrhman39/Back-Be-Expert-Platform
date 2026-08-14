@php app()->setLocale('ar'); @endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? platform_title('لوحة التحكم') }}</title>
    <link rel="icon" href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" type="image/png">
    <link rel="stylesheet" href="{{ static_asset('assets/vendor/fonts/google-fonts-local.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/all.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/admin.css') }}?v=11">
    <link rel="stylesheet" href="{{ asset('css/admin-sidebar-mobile.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin-global-search.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin-modules.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin-status-badges.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin-toast.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/notification-bell.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v=9">
    <link rel="stylesheet" href="{{ asset('css/media-picker.css') }}?v=1">
    @include('partials.platform-theme')
    @livewireStyles
    @stack('styles')
    <style>
        .admin-header--app .admin-header__links--compact {
            display: flex;
            gap: 0.35rem;
            margin-inline: auto;
            flex-wrap: wrap;
        }
        .admin-header--app .admin-header__links--compact a {
            padding: 0.35rem 0.75rem;
            font-size: 0.82rem;
            color: var(--sa-muted);
            text-decoration: none;
            border-radius: 999px;
        }
        .admin-header--app .admin-header__links--compact a.is-active,
        .admin-header--app .admin-header__links--compact a:hover {
            color: var(--sa-green-dark);
            background: var(--sa-green-soft);
        }
        .admin-header--app .admin-header__logout-app {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        @media (max-width: 991.98px) {
            .admin-header--app .admin-header__links--compact { display: none !important; }
        }
        .admin-sidebar-collapse {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.35rem;
            height: 2.35rem;
            flex: 0 0 auto;
            border: 1px solid var(--sa-border);
            border-radius: 10px;
            background: var(--surface-card, #fff);
            color: var(--sa-green-dark);
            cursor: pointer;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .admin-sidebar-collapse:hover { background: var(--sa-green-soft); }
        .admin-sidebar-collapse svg { transition: transform .25s ease; }
        @media (min-width: 992px) {
            .admin-app--dashboard .admin-sidebar {
                transition: width .25s ease;
            }
            html.admin-sidebar-is-collapsed .admin-app--dashboard .admin-sidebar {
                width: 4.75rem;
            }
            html.admin-sidebar-is-collapsed .admin-sidebar__brand {
                padding-inline: .45rem;
            }
            html.admin-sidebar-is-collapsed .admin-sidebar__brand img {
                max-width: 2.7rem;
                max-height: 2.7rem;
                object-fit: contain;
            }
            html.admin-sidebar-is-collapsed .admin-sidebar__org,
            html.admin-sidebar-is-collapsed .admin-sidebar__org-sub,
            html.admin-sidebar-is-collapsed .admin-side-nav__label,
            html.admin-sidebar-is-collapsed .admin-side-nav__angle,
            html.admin-sidebar-is-collapsed .admin-side-nav__child-menu,
            html.admin-sidebar-is-collapsed .admin-sidebar__foot {
                display: none !important;
            }
            html.admin-sidebar-is-collapsed .admin-side-nav__link,
            html.admin-sidebar-is-collapsed .admin-side-nav__toggle {
                justify-content: center;
                padding-inline: .5rem;
            }
            html.admin-sidebar-is-collapsed .admin-side-nav__icon {
                margin: 0;
            }
            html.admin-sidebar-is-collapsed .admin-sidebar-collapse svg {
                transform: rotate(180deg);
            }
        }
        @media (max-width: 991.98px) {
            .admin-sidebar-collapse { display: none; }
        }
    </style>
    <script>
        try {
            if (window.matchMedia('(min-width: 992px)').matches
                && localStorage.getItem('domain.admin.sidebar.collapsed') === '1') {
                document.documentElement.classList.add('admin-sidebar-is-collapsed');
            }
        } catch (e) {}
    </script>
</head>
<body class="admin-dashboard-body"
    data-admin-title="{{ $adminPageTitle ?? 'مؤشرات الأداء الرئيسية' }}"
    data-admin-desc="{{ $adminPageDesc ?? 'نظرة شاملة على المتدربين والبرامج والأداء التشغيلي' }}"
    data-admin-layout="{{ $adminLayout ?? 'dashboard' }}"
    @if(!empty($adminBreadcrumb)) data-admin-breadcrumb='@json($adminBreadcrumb)' @endif
>
    <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    {{ $slot }}

    @php
        $sessionToastMessage = session('success')
            ?? session('status')
            ?? session('warning')
            ?? session('error')
            ?? session('admin_message')
            ?? session('admin_error');
        $sessionToastType = session('error') || session('admin_error')
            ? 'error'
            : (session('warning') ? 'warn' : (session('success') || session('status') || session('admin_message') ? 'success' : 'info'));
    @endphp
    @include('partials.admin.toast', [
        'message' => $sessionToastMessage,
        'type' => $sessionToastType,
        'key' => 'session-'.md5((string) $sessionToastMessage),
    ])

    @include('partials.page-help')

    <script>
        window.adminToastDismiss = function (el) {
            if (!el) return;
            el.classList.add('is-leaving');
            setTimeout(function () {
                var host = el.closest('[data-admin-toast-host]');
                if (host) host.remove();
                else el.remove();
            }, 200);
        };
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-admin-toast]').forEach(function (toast) {
                var duration = parseInt(toast.getAttribute('data-duration') || '6500', 10);
                if (!duration || duration < 1) return;
                setTimeout(function () {
                    if (document.body.contains(toast)) {
                        window.adminToastDismiss(toast);
                    }
                }, duration);
            });
        });
        document.addEventListener('livewire:init', function () {
            Livewire.hook('morph.updated', function () {
                document.querySelectorAll('[data-admin-toast]:not([data-toast-bound])').forEach(function (toast) {
                    toast.setAttribute('data-toast-bound', '1');
                    var duration = parseInt(toast.getAttribute('data-duration') || '6500', 10);
                    if (!duration || duration < 1) return;
                    setTimeout(function () {
                        if (!document.body.contains(toast)) return;
                        var closeBtn = toast.querySelector('[data-admin-toast-dismiss]');
                        if (closeBtn) {
                            closeBtn.click();
                        } else {
                            window.adminToastDismiss(toast);
                        }
                    }, duration);
                });
            });
        });
    </script>

    <script>
        window.domainAdminNav = {
            sidebar: @json(\App\Support\AdminNavigation::sidebarForJs()),
            subnav: @json(\App\Support\AdminNavigation::subnavForJs()),
        };
        window.domainAdmin = {
            isLoggedIn: function () { return true; },
            getSession: function () {
                return {
                    name: @json(auth()->user()?->displayName() ?? 'مسؤول المنصة'),
                    email: @json(auth()->user()?->email ?? ''),
                    role: 'super_admin',
                    token: 'laravel',
                    expires: Date.now() + (8 * 60 * 60 * 1000),
                };
            },
            requireAuth: function () { return true; },
            logout: function () {},
            login: function () { return { ok: false }; },
        };
    </script>
    <script src="{{ static_asset('admin/js/admin-shell.js') }}?v=3"></script>
    <script src="{{ static_asset('admin/js/admin-table-actions.js') }}?v=2"></script>
    <script src="{{ asset('js/admin-laravel.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ static_asset('admin/js/admin-dashboard.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var logoutBtn = document.getElementById('admin-logout');
            if (!logoutBtn) return;
            var replacement = logoutBtn.cloneNode(true);
            logoutBtn.replaceWith(replacement);
            replacement.addEventListener('click', function () {
                document.getElementById('admin-logout-form').submit();
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            var button = document.getElementById('admin-sidebar-collapse');
            if (!button) return;

            function sync() {
                var collapsed = document.documentElement.classList.contains('admin-sidebar-is-collapsed');
                button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                button.setAttribute('aria-label', collapsed ? 'فتح القائمة الجانبية' : 'طي القائمة الجانبية');
                button.setAttribute('title', collapsed ? 'فتح القائمة الجانبية' : 'طي القائمة الجانبية');

                document.querySelectorAll('.admin-side-nav__link, .admin-side-nav__toggle').forEach(function (item) {
                    var label = item.querySelector('.admin-side-nav__label');
                    if (label) item.setAttribute('title', label.textContent.trim());
                });
            }

            button.addEventListener('click', function () {
                var collapsed = document.documentElement.classList.toggle('admin-sidebar-is-collapsed');
                try {
                    localStorage.setItem('domain.admin.sidebar.collapsed', collapsed ? '1' : '0');
                } catch (e) {}
                sync();
            });

            sync();
        });
    </script>
    @livewireScripts
    @auth
        @if (auth()->user()?->canAdmin('media.view'))
            <livewire:admin.media-picker-modal />
        @endif
    @endauth
    @stack('scripts')
</body>
</html>
