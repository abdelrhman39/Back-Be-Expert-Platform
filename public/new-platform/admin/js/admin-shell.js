/**
 * تهيئة مشتركة — شريط جانبي احترافي (3 مستويات)، تنقل فرعي، مسار
 */
(function (global) {
    var SUBNAV = [
        { id: 'home', href: 'index.html', label: 'مؤشرات الأداء الرئيسية' },
        { id: 'financial', href: 'financial.html', label: 'التحليلات المالية' },
        { id: 'enrollment', href: 'enrollment.html', label: 'التسجيل والالتحاق' },
        { id: 'graduates', href: 'graduates.html', label: 'الخريجون' },
        { id: 'staff', href: 'staff.html', label: 'الكوادر الأكاديمية' },
    ];

  var SIDEBAR_MENU = [
        { type: 'link', href: 'index.html', label: 'الرئيسية', icon: 'home' },
        {
            type: 'group',
            id: 'academic-process',
            label: 'إدارة الخطة الأكاديمية',
            icon: 'grid',
            children: [
                {
                    type: 'section',
                    label: 'الإطار الأكاديمي',
                    items: [
                        { href: 'programs.html', label: 'البرامج الدراسية' },
                        { href: 'courses.html', label: 'المقررات الدراسية' },
                    ],
                },
                {
                    type: 'section',
                    label: 'الهيكل الأكاديمي',
                    items: [
                        { href: 'batches.html', label: 'الدفعات الدراسية' },
                        { href: 'sections.html', label: 'الشعب الدراسية' },
                        { href: 'schedules.html', label: 'الجداول الدراسية' },
                    ],
                },
                {
                    type: 'section',
                    label: 'الإعدادات الأكاديمية',
                    items: [{ href: 'levels.html', label: 'المستويات الأكاديمية' }],
                },
            ],
        },
        {
            type: 'group',
            id: 'academic-services',
            label: 'إدارة الخدمات الأكاديمية',
            icon: 'services',
            children: [
                {
                    type: 'section',
                    label: 'طلبات الطلاب',
                    items: [
                        { href: 'requests-deferral.html', label: 'طلبات التأجيل' },
                        { href: 'requests-withdrawal.html', label: 'طلبات الانسحاب' },
                        { href: 'requests-program-change.html', label: 'طلبات تغيير البرنامج' },
                        { href: 'requests-semester-excuse.html', label: 'اعتذار عن الفصل الدراسي' },
                        { href: '#', label: 'طلبات الإعادة', disabled: true },
                    ],
                },
                {
                    type: 'section',
                    label: 'طلبات الإفادة',
                    items: [
                        { href: '#', label: 'إفادة القيد', disabled: true },
                        { href: '#', label: 'إفادة التخرج', disabled: true },
                        { href: '#', label: 'إفادة القبول النهائي', disabled: true },
                    ],
                },
            ],
        },
        {
            type: 'group',
            id: 'academic-staff',
            label: 'إدارة الكوادر الأكاديمية',
            icon: 'staff',
            children: [
                {
                    type: 'section',
                    items: [
                        { href: 'staff.html', label: 'لوحة الكوادر' },
                        { href: '#', label: 'تعيين المدربين', disabled: true },
                    ],
                },
            ],
        },
        {
            type: 'group',
            id: 'trainees',
            label: 'إدارة شؤون الطلاب',
            icon: 'students',
            children: [
                {
                    type: 'section',
                    items: [
                        { href: 'students.html', label: 'الطلاب المشتركين' },
                        { href: 'enrollment.html', label: 'التسجيل والالتحاق' },
                        { href: 'graduates.html', label: 'الخريجون' },
                    ],
                },
            ],
        },
        {
            type: 'group',
            id: 'exams',
            label: 'إدارة شؤون الاختبارات',
            icon: 'exams',
            children: [],
        },
        {
            type: 'group',
            id: 'finance',
            label: 'إدارة الشؤون المالية',
            icon: 'finance',
            children: [
                {
                    type: 'section',
                    items: [{ href: 'financial.html', label: 'التحليلات المالية' }],
                },
            ],
        },
        { type: 'link', href: '#', label: 'التقارير ولوحات المتابعة', icon: 'reports', disabled: true },
        { type: 'link', href: '#', label: 'إعلانات الصفحة الرئيسية', icon: 'settings', disabled: true },
    ];

    var ICONS = {
        home: '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-9.5z"/>',
        grid: '<rect x="48" y="48" width="64" height="64" rx="8"/><rect x="144" y="48" width="64" height="64" rx="8"/><rect x="48" y="144" width="64" height="64" rx="8"/><rect x="144" y="144" width="64" height="64" rx="8"/>',
        services: '<rect x="3" y="4" width="18" height="16" rx="2"/>',
        staff: '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
        students: '<path d="M12 14l9-5-9-5-9 5 9 5z"/>',
        exams: '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/>',
        finance: '<path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>',
        reports: '<path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 5-6"/>',
        settings: '<circle cx="12" cy="12" r="3"/>',
        chevron: '<path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>',
    };

    function currentFile() {
        var path = window.location.pathname || '';
        var parts = path.split('/');
        return parts[parts.length - 1] || 'index.html';
    }

    function svgIcon(name, className) {
        var body = ICONS[name] || ICONS.home;
        var cls = className || 'admin-side-nav__icon';
        var isGrid = name === 'grid';
        var vb = isGrid ? '0 0 256 256' : '0 0 24 24';
        var sw = isGrid ? '16' : '1.75';
        return (
            '<svg class="' +
            cls +
            '" viewBox="' +
            vb +
            '" fill="none" stroke="currentColor" stroke-width="' +
            sw +
            '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            body +
            '</svg>'
        );
    }

    function linkMatches(href, file, override) {
        if (!href || href === '#') return false;

        var hrefPath = href.split('?')[0];
        var hrefQuery = href.indexOf('?') > -1 ? href.slice(href.indexOf('?') + 1) : '';

        if (override) {
            var overridePath = override.split('?')[0];
            if (href === override || hrefPath === overridePath) return true;
        }

        if (hrefPath !== file) return false;
        if (!hrefQuery) return true;

        var expected = new URLSearchParams(hrefQuery);
        var current = new URLSearchParams(window.location.search || '');
        var ok = true;
        expected.forEach(function (val, key) {
            if (current.get(key) !== val) ok = false;
        });
        return ok;
    }

    function sectionHasActive(section, file, override) {
        return (section.items || []).some(function (item) {
            return linkMatches(item.href, file, override);
        });
    }

    function groupHasActiveChild(children, file, override) {
        if (!children || !children.length) return false;
        return children.some(function (child) {
            if (child.type === 'section') return sectionHasActive(child, file, override);
            return linkMatches(child.href, file, override);
        });
    }

    function renderSectionItems(items, file, override) {
        var html = '';
        (items || []).forEach(function (item) {
            var active = linkMatches(item.href, file, override);
            var cls = 'admin-side-nav__child-link' + (active ? ' is-active' : '');
            var dis = item.disabled ? ' aria-disabled="true" tabindex="-1"' : '';
            html +=
                '<li class="admin-side-nav__child">' +
                '<a class="' +
                cls +
                '" href="' +
                (item.href || '#') +
                '"' +
                dis +
                '>' +
                item.label +
                '</a></li>';
        });
        return html;
    }

    function setGroupExpanded(group, btn, open) {
        if (!group) return;
        group.classList.toggle('is-open', open);
        group.classList.toggle('open', open);
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        var panel = group.querySelector('.admin-side-nav__child-menu');
        if (panel) panel.hidden = !open;
    }

    function bindSidebarGroups(nav) {
        nav.querySelectorAll('.admin-side-nav__group > .admin-side-nav__toggle').forEach(function (btn) {
            function onToggle(e) {
                if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
                if (e.type === 'keydown') e.preventDefault();
                var group = btn.closest('.admin-side-nav__group');
                if (!group) return;
                var id = group.getAttribute('data-group');
                var open = !group.classList.contains('is-open');
                setGroupExpanded(group, btn, open);
                try {
                    var state = JSON.parse(sessionStorage.getItem('adminSidebarOpen') || '{}');
                    state[id] = open;
                    sessionStorage.setItem('adminSidebarOpen', JSON.stringify(state));
                } catch (err) {
                    /* ignore */
                }
            }
            btn.addEventListener('click', onToggle);
            btn.addEventListener('keydown', onToggle);
        });
    }

    function renderGroupChildren(children, file, override) {
        var html = '';

        (children || []).forEach(function (child) {
            if (child.type === 'section') {
                if (child.label) {
                    html +=
                        '<li class="admin-side-nav__section-head">' +
                        '<div class="admin-side-nav__section-head-text">' +
                        child.label +
                        '</div></li>';
                }
                html += renderSectionItems(child.items, file, override);
                return;
            }

            html += renderSectionItems([child], file, override);
        });

        return html;
    }

    function renderSidebar() {
        var nav = document.querySelector('.admin-app--dashboard .admin-side-nav');
        if (!nav) return;

        var file = currentFile();
        var override = document.body.getAttribute('data-admin-sidebar-active');
        var openStored = {};
        try {
            openStored = JSON.parse(sessionStorage.getItem('adminSidebarOpen') || '{}');
        } catch (e) {
            openStored = {};
        }

        var html = '';
        SIDEBAR_MENU.forEach(function (item) {
            if (item.type === 'link') {
                var active = linkMatches(item.href, file, override);
                var cls = 'admin-side-nav__link side-menu__item' + (active ? ' is-active' : '');
                var dis = item.disabled ? ' aria-disabled="true" tabindex="-1"' : '';
                html += '<li class="admin-side-nav__item slide">';
                html += '<a class="' + cls + '" href="' + item.href + '"' + dis + '>';
                html += svgIcon(item.icon) + '<span class="admin-side-nav__label side-menu__label">' + item.label + '</span></a></li>';
                return;
            }

            if (item.type === 'group') {
                var hasActive = groupHasActiveChild(item.children, file, override);
                var stored = openStored[item.id];
                var isOpen = stored === true || (stored !== false && stored !== true && hasActive);
                var hasChildren = item.children && item.children.length > 0;

                if (!hasChildren) {
                    html += '<li class="admin-side-nav__item slide has-sub is-disabled">';
                    html += '<span class="admin-side-nav__toggle side-menu__item is-muted">';
                    html += svgIcon(item.icon) + '<span class="admin-side-nav__label side-menu__label">' + item.label + '</span>';
                    html += svgIcon('chevron', 'admin-side-nav__angle side-menu__angle');
                    html += '</span></li>';
                    return;
                }

                html +=
                    '<li class="admin-side-nav__item slide has-sub admin-side-nav__group' +
                    (isOpen ? ' is-open open' : '') +
                    '" data-group="' +
                    item.id +
                    '">';
                html += '<button type="button" class="admin-side-nav__toggle side-menu__item" aria-expanded="' + (isOpen ? 'true' : 'false') + '">';
                html += svgIcon(item.icon);
                html += '<span class="admin-side-nav__label side-menu__label">' + item.label + '</span>';
                html += svgIcon('chevron', 'admin-side-nav__angle side-menu__angle');
                html += '</button>';
                html += '<ul class="admin-side-nav__child-menu"' + (isOpen ? '' : ' hidden') + '>';
                html += '<li class="admin-side-nav__menu-title side-menu__label1"><span>' + item.label + '</span></li>';
                html += renderGroupChildren(item.children, file, override);
                html += '</ul></li>';
            }
        });

        nav.innerHTML = html;
        bindSidebarGroups(nav);
    }

    function subnavIcon(name) {
        var icons = {
            clock: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
            chart: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 6-8"/></svg>',
            user: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
            cap: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>',
            group: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
        };
        var map = { home: 'clock', financial: 'chart', enrollment: 'user', graduates: 'cap', staff: 'group' };
        return icons[map[name] || name] || icons.clock;
    }

    function renderSubnav(activeId) {
        var nav = document.querySelector('.admin-subnav__list');
        if (!nav) return;
        nav.innerHTML = SUBNAV.map(function (item) {
            var active = item.id === activeId ? ' is-active' : '';
            return '<li><a href="' + item.href + '" class="' + active.trim() + '">' + subnavIcon(item.id) + item.label + '</a></li>';
        }).join('');
    }

    function renderBreadcrumb() {
        var el = document.getElementById('admin-breadcrumb');
        if (!el) return;
        var raw = document.body.getAttribute('data-admin-breadcrumb');
        if (!raw) return;
        try {
            var items = JSON.parse(raw);
            el.innerHTML = items
                .map(function (item, i) {
                    var isLast = i === items.length - 1;
                    if (item.href && !isLast) {
                        return '<a href="' + item.href + '">' + item.label + '</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>';
                    }
                    return '<span class="admin-breadcrumb__current" aria-current="page">' + item.label + '</span>';
                })
                .join('');
        } catch (e) {
            /* ignore */
        }
    }

    function applyLayout() {
        var layout = document.body.getAttribute('data-admin-layout') || 'dashboard';
        var subnav = document.querySelector('.admin-subnav');
        var headerLinks = document.querySelector('.admin-header__links');
        if (layout === 'app') {
            document.body.classList.add('admin-layout-app');
            if (subnav) subnav.hidden = true;
            if (headerLinks) headerLinks.hidden = true;
            renderBreadcrumb();
        } else {
            document.body.classList.remove('admin-layout-app');
            if (subnav) subnav.hidden = false;
            if (headerLinks) headerLinks.hidden = false;
        }
    }

    function syncHeaderLinks() {
        if (document.body.getAttribute('data-admin-layout') === 'app') return;
        var file = currentFile();
        var statsPages = ['index.html', 'financial.html', 'enrollment.html', 'graduates.html', 'staff.html'];
        var homeLink = document.querySelector('.admin-header__links a[href="index.html"]:first-of-type');
        var statsLink = document.querySelector('.admin-header__links a[href="index.html"]:nth-of-type(2)');
        if (!homeLink || !statsLink) return;
        homeLink.classList.remove('is-active');
        statsLink.classList.remove('is-active');
        if (file === 'index.html') homeLink.classList.add('is-active');
        else if (statsPages.indexOf(file) !== -1) statsLink.classList.add('is-active');
    }

    function renderPageHead() {
        var title = document.body.getAttribute('data-admin-title');
        var desc = document.body.getAttribute('data-admin-desc');
        if (!title) return;
        var content = document.querySelector('.admin-content--dashboard');
        if (!content || content.querySelector('.admin-page-head')) return;
        var head = document.createElement('div');
        head.className = 'admin-page-head';
        head.innerHTML = '<h2>' + title + '</h2>' + (desc ? '<p>' + desc + '</p>' : '');
        content.insertBefore(head, content.firstChild);
    }

    function initAuth() {
        if (!global.domainAdmin || !global.domainAdmin.requireAuth('login.html')) {
            return false;
        }
        var session = global.domainAdmin.getSession();
        var nameEl = document.getElementById('admin-user-name');
        var avatarEl = document.getElementById('admin-avatar');
        if (session && nameEl) nameEl.textContent = session.name || 'مسؤول المنصة';
        if (session && avatarEl) avatarEl.textContent = (session.name || 'م').charAt(0);
        var logoutBtn = document.getElementById('admin-logout');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function () {
                global.domainAdmin.logout();
                window.location.href = 'login.html';
            });
        }
        return true;
    }

    function cssVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function initMobileSidebar() {
        var toggle = document.getElementById('admin-sidebar-toggle');
        var closeBtn = document.getElementById('admin-sidebar-close');
        var sidebar = document.getElementById('admin-sidebar') || document.querySelector('.admin-sidebar');
        var backdrop = document.getElementById('admin-sidebar-backdrop');

        if (!sidebar) return;

        function isMobileNav() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        function setOpen(open) {
            sidebar.classList.toggle('is-mobile-open', open);
            document.body.classList.toggle('admin-sidebar-mobile-open', open);

            if (backdrop) {
                if (open) {
                    backdrop.hidden = false;
                } else {
                    backdrop.hidden = true;
                }
            }

            if (toggle) {
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
            }

            if (!open && !isMobileNav()) {
                document.body.classList.remove('admin-sidebar-mobile-open');
            }
        }

        function close() {
            setOpen(false);
        }

        function open() {
            if (!isMobileNav()) return;
            setOpen(true);
        }

        function toggleOpen() {
            if (!isMobileNav()) return;
            setOpen(!sidebar.classList.contains('is-mobile-open'));
        }

        if (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                toggleOpen();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (event) {
                event.preventDefault();
                close();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', close);
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });

        sidebar.addEventListener('click', function (event) {
            var link = event.target.closest('a[href]');
            if (!link || !isMobileNav()) return;
            var href = link.getAttribute('href') || '';
            if (href && href !== '#' && href.indexOf('javascript:') !== 0) {
                close();
            }
        });

        window.addEventListener('resize', function () {
            if (!isMobileNav()) {
                close();
            }
        });

        // Start closed on mobile
        if (isMobileNav()) {
            close();
        }
    }

    global.AdminShell = {
        SUBNAV: SUBNAV,
        SIDEBAR_MENU: SIDEBAR_MENU,
        cssVar: cssVar,
        colors: function () {
            return {
                green: cssVar('--sa-green'),
                greenLight: cssVar('--sa-green-light'),
                greenSoft: cssVar('--sa-green-soft'),
                gold: cssVar('--sa-gold'),
                muted: cssVar('--sa-muted'),
                ink: cssVar('--sa-ink'),
                info: cssVar('--color-info'),
                neutral: cssVar('--color-neutral'),
                warning: cssVar('--color-warning-light'),
                track: cssVar('--surface-track'),
            };
        },
        renderSubnav: renderSubnav,
        renderSidebar: renderSidebar,
        init: function (activeSubnavId) {
            if (!initAuth()) return;
            applyLayout();
            renderSidebar();
            if (document.body.getAttribute('data-admin-layout') !== 'app') {
                renderSubnav(activeSubnavId);
                syncHeaderLinks();
            }
            renderPageHead();
            initMobileSidebar();
        },
    };
})(typeof window !== 'undefined' ? window : this);
