@php
    use App\Support\ThemeSettings;

    $cssVariables = ThemeSettings::cssVariables();
@endphp
<style id="platform-theme">
:root {
@foreach ($cssVariables as $var => $value)
    {{ $var }}: {{ $value }};
@endforeach
}

/* الهيدر والنافبار */
.header.new-header,
.header.profile-header {
    background-color: var(--platform-header-bg, transparent) !important;
}

.header.fixed,
.header.site-header--auth.fixed,
.header.profile-header.fixed,
.header.not-home {
    background-color: var(--platform-header-bg-fixed, #ffffff) !important;
    border-bottom: 1px solid var(--platform-header-border, #eceff1);
}

.header.new-header:not(.not-home):not(.site-header--auth):not(.profile-header) .main-nav li a,
.header.new-header:not(.not-home):not(.site-header--auth):not(.profile-header) .header-navbar-rht > li > a:not(.btn-primary) {
    color: var(--platform-header-nav-color, #ffffff) !important;
}

.header.not-home .main-nav li a,
.header.site-header--auth .main-nav li a,
.header.profile-header .main-nav li a,
.header.fixed .main-nav li a {
    color: var(--platform-header-nav-color-inner, #494949) !important;
}

.header .main-nav li a:hover,
.header .main-nav > li.active > a {
    color: var(--platform-header-nav-hover, var(--primary, #1b8354)) !important;
}

.header .site-header-actions__icon,
.header .site-header-actions__lang,
.header .site-header-actions__profile-text,
.header .site-header-actions__chevron {
    color: var(--platform-header-toolbar-color, #515151) !important;
}

.profile-header .header-nav {
    background-color: var(--platform-header-bg-fixed, #ffffff);
}

.main-menu-wrapper {
    background: var(--platform-header-bg-fixed, #ffffff);
}

/* الفوتر */
.footer {
    background-color: var(--platform-footer-bg, #ffffff) !important;
}

.footer-widget,
.footer-widget .menu-items li a,
.footer-mini {
    color: var(--platform-footer-text, #414040);
}

body {
    color: var(--sa-ink, #1a1a1a);
    background-color: var(--sa-mist, #f7faf8);
}
</style>
