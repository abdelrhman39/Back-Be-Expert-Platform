            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.querySelector('.portal-root');
    if (!root) {
        return;
    }

    var toggle = root.querySelector('.portal-drawer-toggle');
    var overlay = root.querySelector('.portal-drawer-overlay');
    var closeBtn = root.querySelector('.portal-drawer-close');
    var drawer = root.querySelector('#portal-drawer');
    var mobileQuery = window.matchMedia('(max-width: 991.98px)');

    function isMobile() {
        return mobileQuery.matches;
    }

    function openDrawer() {
        if (!isMobile()) {
            return;
        }
        root.classList.add('is-drawer-open');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'true');
            var icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            }
        }
        document.body.classList.add('portal-drawer-open');
    }

    function closeDrawer() {
        root.classList.remove('is-drawer-open');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
            var icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
        document.body.classList.remove('portal-drawer-open');
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (root.classList.contains('is-drawer-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeDrawer);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeDrawer);
    }

    if (drawer) {
        drawer.querySelectorAll('.portal-sidebar-nav__link[href]').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobile()) {
                    closeDrawer();
                }
            });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });

    mobileQuery.addEventListener('change', function () {
        if (!isMobile()) {
            closeDrawer();
        }
    });
})();
</script>
