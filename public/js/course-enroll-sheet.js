/**
 * Mobile enroll bottom-sheet for course show pages.
 */
(function () {
    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function initRoot(root) {
        if (!root || root.dataset.enrollSheetBound === '1') {
            return;
        }
        root.dataset.enrollSheetBound = '1';

        var sheet = root.querySelector('[data-course-enroll-sheet]');
        var backdrop = root.querySelector('.course-enroll-sheet-backdrop');
        var openBtns = root.querySelectorAll('[data-course-enroll-open]');
        var closeBtns = root.querySelectorAll('[data-course-enroll-close]');

        if (!sheet) {
            return;
        }

        function isMobile() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        function setOpen(open) {
            root.classList.toggle('is-enroll-open', open);
            document.documentElement.classList.toggle('course-enroll-sheet-open', open);
            if (backdrop) {
                backdrop.hidden = !open;
            }
            sheet.setAttribute('aria-modal', open ? 'true' : 'false');
            if (open) {
                sheet.setAttribute('role', 'dialog');
            } else {
                sheet.removeAttribute('role');
            }
        }

        function openSheet() {
            if (!isMobile()) {
                return;
            }
            setOpen(true);
            window.requestAnimationFrame(function () {
                var focusable = sheet.querySelector('input, select, textarea, button');
                if (focusable) {
                    focusable.focus({ preventScroll: true });
                }
            });
        }

        function closeSheet() {
            setOpen(false);
        }

        openBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openSheet();
            });
        });

        closeBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                closeSheet();
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && root.classList.contains('is-enroll-open')) {
                closeSheet();
            }
        });

        window.addEventListener('resize', function () {
            if (!isMobile() && root.classList.contains('is-enroll-open')) {
                closeSheet();
            }
        });

        function maybeOpenFromHash() {
            if (!isMobile()) {
                return;
            }
            if ((window.location.hash || '') === '#course-enroll') {
                openSheet();
            }
        }

        maybeOpenFromHash();
        window.addEventListener('hashchange', maybeOpenFromHash);
    }

    function boot() {
        document.querySelectorAll('[data-course-enroll-root]').forEach(initRoot);
    }

    ready(boot);
    document.addEventListener('livewire:navigated', boot);
})();
