/********************* Course detail — section tabs (scroll navigation) ************************ */

(function initFellowshipCourseTabsModule() {
    var scrollSpyBound = false;

    function getCourseTabRoot() {
        return (
            document.querySelector(".fellowship .course-show-tabs")
            || document.querySelector(".fellowship .col-lg-8")
        );
    }

    function getTabBar(root) {
        return root ? root.querySelector(".listing-tab--with-scroll-controls") : null;
    }

    function getScrollOffset(root) {
        var header = parseInt(
            getComputedStyle(document.documentElement).getPropertyValue("--header-height"),
            10,
        ) || 85;
        var tabBar = getTabBar(root);
        var tabHeight = tabBar ? tabBar.offsetHeight : 56;
        return header + tabHeight + 12;
    }

    function resolveTarget(link) {
        var selector = link.getAttribute("data-bs-target") || link.getAttribute("href");
        if (!selector || selector === "javascript:void(0);") {
            return null;
        }
        return document.querySelector(selector);
    }

    function getNavLinks(root) {
        return root ? root.querySelectorAll(".listing-tab .nav-link") : [];
    }

    function scrollToSection(section, root) {
        if (!section) {
            return;
        }
        var top = section.getBoundingClientRect().top + window.scrollY - getScrollOffset(root);
        window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
    }

    function setActiveLink(activeLink, root) {
        getNavLinks(root).forEach(function (link) {
            var isActive = link === activeLink;
            link.classList.toggle("active", isActive);
            link.setAttribute("aria-selected", isActive ? "true" : "false");
            if (isActive) {
                link.removeAttribute("tabindex");
            } else {
                link.setAttribute("tabindex", "-1");
            }
        });
    }

    function scrollTabIntoView(link) {
        var track = link.closest(".listing-slider");
        if (!track) {
            link.scrollIntoView({ behavior: "smooth", inline: "center", block: "nearest" });
            return;
        }

        var trackRect = track.getBoundingClientRect();
        var linkRect = link.getBoundingClientRect();
        var delta = (linkRect.left + linkRect.width / 2) - (trackRect.left + trackRect.width / 2);

        track.scrollBy({ left: delta, behavior: "smooth" });
    }

    function bindClickDelegation() {
        if (scrollSpyBound) {
            return;
        }
        scrollSpyBound = true;

        document.addEventListener("click", function (e) {
            var link = e.target.closest(".fellowship .listing-tab .nav-link");
            if (!link) {
                return;
            }

            var root = getCourseTabRoot();
            if (!root || !root.contains(link)) {
                return;
            }

            var target = resolveTarget(link);
            if (!target) {
                return;
            }

            e.preventDefault();
            scrollToSection(target, root);
            setActiveLink(link, root);
            scrollTabIntoView(link);
        });
    }

    function setupScrollSpy() {
        var root = getCourseTabRoot();
        if (!root) {
            return;
        }

        var sections = [];
        getNavLinks(root).forEach(function (link) {
            var section = resolveTarget(link);
            if (section) {
                sections.push({ section: section, link: link });
            }
        });

        if (!sections.length) {
            return;
        }

        function updateActiveFromScroll() {
            var offset = getScrollOffset(root) + 40;
            var current = sections[0];

            sections.forEach(function (item) {
                if (item.section.getBoundingClientRect().top - offset <= 0) {
                    current = item;
                }
            });

            if (current) {
                setActiveLink(current.link, root);
            }
        }

        if (root.__courseTabScrollHandler) {
            window.removeEventListener("scroll", root.__courseTabScrollHandler);
        }

        var scrollTicking = false;
        root.__courseTabScrollHandler = function () {
            if (scrollTicking) {
                return;
            }
            scrollTicking = true;
            requestAnimationFrame(function () {
                scrollTicking = false;
                updateActiveFromScroll();
            });
        };

        window.addEventListener("scroll", root.__courseTabScrollHandler, { passive: true });
        updateActiveFromScroll();
    }

    function init() {
        bindClickDelegation();
        setupScrollSpy();
        if (typeof window.initListingTabScrollControls === "function") {
            window.initListingTabScrollControls();
        }
    }

    window.initCourseSectionTabs = init;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    document.addEventListener("livewire:navigated", init);
})();

/********************* tooltips ************************ */
document.querySelectorAll(".trainingCard").forEach((card) => {
    card.addEventListener("click", function (e) {
        // prevent links/buttons from triggering overlay
        if (
            e.target.closest("a") ||
            e.target.closest("button") ||
            e.target.closest("input")
        )
            return;

        // remove from all
        document
            .querySelectorAll(".trainingCard")
            .forEach((c) => c.classList.remove("active-tooltip"));

        // add to clicked
        this.classList.add("active-tooltip");

        e.stopPropagation();
    });
});

// click outside closes
document.addEventListener("click", function () {
    document
        .querySelectorAll(".trainingCard")
        .forEach((c) => c.classList.remove("active-tooltip"));
});
/********************* tooltip ************************ */

document.querySelectorAll(".trainingCard").forEach(card => {

    card.addEventListener("click", function (e) {

        // prevent links/buttons from triggering overlay
        if (
            e.target.closest("a") ||
            e.target.closest("button") ||
            e.target.closest("input")
        ) return;

        // remove from all
        document.querySelectorAll(".trainingCard")
            .forEach(c => c.classList.remove("active-tooltip"));

        // add to clicked
        this.classList.add("active-tooltip");

        e.stopPropagation();
    });

});

// click outside closes
document.addEventListener("click", function () {
    document.querySelectorAll(".trainingCard")
        .forEach(c => c.classList.remove("active-tooltip"));
});

document.addEventListener("click", function (e) {
    var toggle = e.target.closest(".bn-toggle-password");
    if (!toggle) return;
    var wrapper = toggle.closest(".input-wrapper");
    if (!wrapper) return;
    var input = wrapper.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;

    var show = input.type === "password";
    input.type = show ? "text" : "password";

    var icon = toggle.querySelector("i");
    if (icon) {
        icon.classList.remove(show ? "feather-eye-off" : "feather-eye");
        icon.classList.add(show ? "feather-eye" : "feather-eye-off");
    }
});
/************************** End tooltip ****************/


/*********************************** Adel ***********************************/

/* ================================================================
  CUSTOM DATEPICKER COMPONENT
================================================================ */
window.initCustomDatepickers = function (container = document) {
    const wrappers = (container instanceof HTMLElement && container.classList.contains("custom-datepicker-wrap"))
        ? [container]
        : container.querySelectorAll(".custom-datepicker-wrap");

    wrappers.forEach(wrapper => {
        if (wrapper.dataset.dpInitialized) return;
        wrapper.dataset.dpInitialized = "true";

        const trigger = wrapper.querySelector('.custom-datepicker-trigger');
        const dropdown = wrapper.querySelector('.custom-datepicker-dropdown');
        const display = wrapper.querySelector('.datepicker-display-value');

        // Robust finding of hidden input (either inside wrapper or a close sibling)
        let hiddenInput = wrapper.querySelector('input[type="hidden"]');
        if (!hiddenInput && wrapper.parentElement) {
            hiddenInput = wrapper.parentElement.querySelector('input[type="hidden"]');
        }

        if (!trigger || !dropdown || !display || !hiddenInput) {
            console.warn("Datepicker initialization failed: missing required elements in wrapper", wrapper);
            return;
        }

        const monthSelect = wrapper.querySelector('.datepicker-month-select');
        const yearSelect = wrapper.querySelector('.datepicker-year-select');
        const weekdaysWrap = wrapper.querySelector('.datepicker-weekdays');
        const daysGrid = wrapper.querySelector('.datepicker-days-grid');

        const prevBtn = wrapper.querySelector('.datepicker-nav-btn:first-child') || wrapper.querySelector('#dp-prev-month');
        const nextBtn = wrapper.querySelector('.datepicker-nav-btn:last-child') || wrapper.querySelector('#dp-next-month');
        const todayBtn = wrapper.querySelector('.datepicker-today-btn') || wrapper.querySelector('#dp-today-btn');
        const clearBtn = wrapper.querySelector('.datepicker-clear-btn') || wrapper.querySelector('#dp-clear-btn');

        const MONTHS = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        const WEEKDAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

        let dpYear = null;
        let dpMonth = null;
        let dpSelected = null;

        // Init from hidden input
        function syncFromInput() {
            const val = hiddenInput.value;
            if (val) {
                const d = new Date(val);
                if (!isNaN(d)) {
                    dpSelected = { year: d.getFullYear(), month: d.getMonth(), day: d.getDate() };
                    dpYear = dpSelected.year;
                    dpMonth = dpSelected.month;
                }
            } else {
                dpSelected = null;
                const now = new Date();
                dpYear = now.getFullYear();
                dpMonth = now.getMonth();
            }
        }
        syncFromInput();

        function padZ(n) { return String(n).padStart(2, '0'); }
        function formatDisplay(y, m, d) { return d + ' ' + MONTHS[m] + ' ' + y; }
        function formatISO(y, m, d) { return y + '-' + padZ(m + 1) + '-' + padZ(d); }

        function renderCalendar() {
            if (monthSelect) {
                monthSelect.innerHTML = '';
                MONTHS.forEach((name, i) => {
                    const opt = document.createElement('option');
                    opt.value = i;
                    opt.textContent = name;
                    if (i === dpMonth) opt.selected = true;
                    monthSelect.appendChild(opt);
                });
            }

            if (yearSelect) {
                yearSelect.innerHTML = '';
                const nowYear = new Date().getFullYear();
                const startYear = parseInt(wrapper.dataset.startYear) || (nowYear - 100);
                const endYear = parseInt(wrapper.dataset.endYear) || (nowYear + 10);
                for (let y = startYear; y <= endYear; y++) {
                    const opt = document.createElement('option');
                    opt.value = y;
                    opt.textContent = y;
                    if (y === dpYear) opt.selected = true;
                    yearSelect.appendChild(opt);
                }
            }

            if (weekdaysWrap) {
                weekdaysWrap.innerHTML = '';
                WEEKDAYS.forEach(d => {
                    const el = document.createElement('div');
                    el.className = 'datepicker-weekday';
                    el.textContent = d;
                    weekdaysWrap.appendChild(el);
                });
            }

            if (daysGrid) {
                daysGrid.innerHTML = '';
                const today = new Date();
                const firstDay = new Date(dpYear, dpMonth, 1).getDay();
                const daysInMonth = new Date(dpYear, dpMonth + 1, 0).getDate();

                for (let i = 0; i < firstDay; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'dp-day dp-day-empty';
                    daysGrid.appendChild(cell);
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    cell.className = 'dp-day';
                    cell.textContent = d;

                    const isToday = (d === today.getDate() && dpMonth === today.getMonth() && dpYear === today.getFullYear());
                    const isSelected = dpSelected && (d === dpSelected.day && dpMonth === dpSelected.month && dpYear === dpSelected.year);

                    if (isToday) cell.classList.add('dp-day-today');
                    if (isSelected) cell.classList.add('dp-day-selected');

                    cell.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dpSelected = { year: dpYear, month: dpMonth, day: d };
                        const iso = formatISO(dpYear, dpMonth, d);
                        const disp = formatDisplay(dpYear, dpMonth, d);

                        hiddenInput.value = iso;
                        $(hiddenInput).trigger('change').removeClass('is-invalid');
                        display.textContent = disp;
                        display.classList.remove('is-placeholder');
                        trigger.classList.remove('is-invalid');
                        close();
                    });
                    daysGrid.appendChild(cell);
                }
            }
        }

        function open() {
            syncFromInput();
            renderCalendar();
            dropdown.style.display = 'block';
            trigger.setAttribute('aria-expanded', 'true');
            wrapper.classList.add('is-open');
        }

        function close() {
            dropdown.style.display = 'none';
            trigger.setAttribute('aria-expanded', 'false');
            wrapper.classList.remove('is-open');
        }

        function isOpen() { return dropdown.style.display === 'block'; }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen() ? close() : open();
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dpMonth--;
                if (dpMonth < 0) { dpMonth = 11; dpYear--; }
                renderCalendar();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dpMonth++;
                if (dpMonth > 11) { dpMonth = 0; dpYear++; }
                renderCalendar();
            });
        }

        if (monthSelect) {
            monthSelect.addEventListener('change', (e) => {
                dpMonth = parseInt(e.target.value);
                renderCalendar();
            });
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', (e) => {
                dpYear = parseInt(e.target.value);
                renderCalendar();
            });
        }

        if (todayBtn) {
            todayBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const now = new Date();
                dpYear = now.getFullYear();
                dpMonth = now.getMonth();
                dpSelected = { year: dpYear, month: dpMonth, day: now.getDate() };

                const iso = formatISO(dpYear, dpMonth, now.getDate());
                hiddenInput.value = iso;
                $(hiddenInput).trigger('change').removeClass('is-invalid');
                display.textContent = formatDisplay(dpYear, dpMonth, now.getDate());
                display.classList.remove('is-placeholder');
                trigger.classList.remove('is-invalid');
                close();
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dpSelected = null;
                hiddenInput.value = '';
                $(hiddenInput).trigger('change');
                display.textContent = wrapper.querySelector('.custom-datepicker-trigger').dataset.placeholder || 'Select Date';
                display.classList.add('is-placeholder');
                close();
            });
        }

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) close();
        });
    });
};

if ($("#viewMails").length > 0) {
    
    document.onreadystatechange = function() {
        // Open modal after 30 seconds
        setTimeout(function() {
            var myModal = new bootstrap.Modal(document.getElementById('viewMails'));
            myModal.show();
        }, 3000); // 30 seconds
    };
}

/*********************************** Adel ***********************************/


/*********************************** mostafa ***********************************/
const header = document.querySelector("header");

document.documentElement.style.setProperty(
  "--header-height",
  header.offsetHeight + "px"
);
const footer = document.querySelector("footer");

document.documentElement.style.setProperty(
  "--footer-height",
  footer.offsetHeight + "px"
);

/*********************************** mostafa ***********************************/


