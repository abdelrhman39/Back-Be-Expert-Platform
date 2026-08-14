/**
 * Laravel admin — مسارات حقيقية للقائمة الجانبية والتبويبات
 */
(function (global) {
    if (typeof AdminShell === 'undefined') return;

    function replaceMenuItems(target, source) {
        if (!target || !source) return;
        target.splice(0, target.length);
        source.forEach(function (item) {
            target.push(item);
        });
    }

    function patchMenus() {
        var nav = global.domainAdminNav;
        if (!nav) return;

        if (nav.sidebar) {
            replaceMenuItems(AdminShell.SIDEBAR_MENU, nav.sidebar);
        }

        if (nav.subnav) {
            replaceMenuItems(AdminShell.SUBNAV, nav.subnav);
        }
    }

    function readSubnavId() {
        var fromContent = document.querySelector('[data-admin-subnav]');
        if (fromContent) {
            return fromContent.getAttribute('data-admin-subnav') || 'home';
        }

        return document.body.getAttribute('data-admin-subnav') || 'home';
    }

    function patchRenderSubnav() {
        var original = AdminShell.renderSubnav;

        AdminShell.renderSubnav = function (activeId) {
            var list = document.querySelector('.admin-subnav__list[data-server-rendered]');
            if (list) {
                list.querySelectorAll('a').forEach(function (link) {
                    link.classList.toggle('is-active', link.getAttribute('data-subnav-id') === activeId);
                });
                return;
            }

            original(activeId);
        };
    }

    function refreshSidebarIfNeeded() {
        if (typeof AdminShell === 'undefined' || !AdminShell.renderSidebar) {
            return;
        }

        var nav = document.querySelector('.admin-app--dashboard .admin-side-nav');
        if (nav && nav.children.length === 0) {
            AdminShell.renderSidebar();
        }
    }

    function bindTableActions(root) {
        if (typeof global.AdminTableActions !== 'undefined') {
            global.AdminTableActions.bind(root || document);
        }
    }

    function init() {
        patchMenus();
        patchRenderSubnav();

        var activeEl = document.querySelector('[data-admin-sidebar-active]');
        if (activeEl) {
            document.body.setAttribute('data-admin-sidebar-active', activeEl.getAttribute('data-admin-sidebar-active'));
        }

        AdminShell.init(readSubnavId());
        bindTableActions(document);
    }

    function bindLivewireHooks() {
        document.addEventListener('livewire:init', function () {
            if (!global.Livewire || typeof global.Livewire.hook !== 'function') {
                return;
            }

            global.Livewire.hook('commit', function (_ref) {
                var succeed = _ref.succeed;
                succeed(function () {
                    if (typeof global.AdminTableActions !== 'undefined') {
                        global.AdminTableActions.close();
                    }
                    refreshSidebarIfNeeded();
                    bindTableActions(document);
                });
            });
        });
    }

    bindLivewireHooks();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(typeof window !== 'undefined' ? window : this);
