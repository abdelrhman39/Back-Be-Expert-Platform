/**
 * قوائم إجراءات الجداول — فتح بـ position:fixed دون قطع ارتباط Livewire
 */
(function (global) {
    var openMenu = null;
    var listenersReady = false;

    function isInsideLivewire(el) {
        return !!(el && el.closest && el.closest('[wire\\:id], [wire\\:snapshot], [x-data]'));
    }

    function positionDropdown(panel, btn) {
        var rect = btn.getBoundingClientRect();
        var gap = 6;
        var isRtl = document.documentElement.getAttribute('dir') === 'rtl';
        var spaceBelow = window.innerHeight - rect.bottom;
        var openUp = spaceBelow < 120 && rect.top > spaceBelow;

        panel.classList.add('is-fixed', 'admin-actions-dropdown--portal');
        panel.style.top = '';
        panel.style.bottom = '';
        panel.style.left = '';
        panel.style.right = '';

        if (openUp) {
            panel.style.top = 'auto';
            panel.style.bottom = window.innerHeight - rect.top + gap + 'px';
        } else {
            panel.style.top = rect.bottom + gap + 'px';
            panel.style.bottom = 'auto';
        }

        if (isRtl) {
            panel.style.left = Math.max(8, rect.left) + 'px';
            panel.style.right = 'auto';
        } else {
            panel.style.left = 'auto';
            panel.style.right = Math.max(8, window.innerWidth - rect.right) + 'px';
        }

        requestAnimationFrame(function () {
            var pr = panel.getBoundingClientRect();
            if (pr.right > window.innerWidth - 8) {
                panel.style.left = 'auto';
                panel.style.right = '8px';
            }
            if (pr.left < 8) {
                panel.style.left = '8px';
                panel.style.right = 'auto';
            }
        });
    }

    function resetDropdownStyle(panel) {
        if (!panel) return;
        panel.classList.remove('is-fixed', 'admin-actions-dropdown--portal');
        panel.style.top = '';
        panel.style.bottom = '';
        panel.style.left = '';
        panel.style.right = '';
    }

    function restorePanel(wrap, panel) {
        if (!panel || !wrap._menuHome) return;
        wrap._menuHome.parent.insertBefore(panel, wrap._menuHome.next);
        wrap._menuHome = null;
        wrap._dropdownPanel = null;
    }

    function closeAll() {
        if (!openMenu) return;
        var wrap = openMenu;
        var panel = wrap._dropdownPanel || wrap.querySelector('.admin-actions-dropdown');
        var btn = wrap.querySelector('.admin-kebab');
        var row = wrap.closest('tr');

        wrap.classList.remove('is-open');
        if (row) row.classList.remove('is-row-actions-open');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (panel) {
            panel.hidden = true;
            resetDropdownStyle(panel);
            // Only restore if we previously moved it to body.
            if (wrap._menuHome) {
                restorePanel(wrap, panel);
            } else {
                wrap._dropdownPanel = null;
            }
        }
        openMenu = null;
    }

    function ensureListeners() {
        if (listenersReady) return;
        listenersReady = true;
        document.addEventListener('click', closeAll);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAll();
        });
        window.addEventListener('resize', closeAll);
        window.addEventListener('scroll', closeAll, true);
    }

    function bind(root) {
        ensureListeners();
        var container = root && root.querySelectorAll ? root : document.querySelector(root);
        if (!container) return;

        container.querySelectorAll('.admin-actions-menu').forEach(function (wrap) {
            var btn = wrap.querySelector('.admin-kebab');
            var panel = wrap.querySelector('.admin-actions-dropdown');
            if (!btn || !panel) return;

            // Re-bind after Livewire morphs (dataset flag gets wiped with HTML replace).
            if (wrap.dataset.actionsBound === '1' && btn._adminActionsBound) return;
            wrap.dataset.actionsBound = '1';
            btn._adminActionsBound = true;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = wrap.classList.contains('is-open');
                closeAll();
                if (!isOpen) {
                    var keepInPlace = isInsideLivewire(wrap);

                    if (!keepInPlace) {
                        wrap._menuHome = { parent: panel.parentNode, next: panel.nextSibling };
                        document.body.appendChild(panel);
                    }

                    wrap.classList.add('is-open');
                    wrap._dropdownPanel = panel;

                    var row = wrap.closest('tr');
                    if (row) row.classList.add('is-row-actions-open');

                    panel.hidden = false;
                    btn.setAttribute('aria-expanded', 'true');
                    positionDropdown(panel, btn);
                    openMenu = wrap;
                }
            });

            // Keep Livewire wire:click from being swallowed by document closeAll.
            panel.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });
    }

    global.AdminTableActions = {
        bind: bind,
        close: closeAll,
    };
})(typeof window !== 'undefined' ? window : this);
