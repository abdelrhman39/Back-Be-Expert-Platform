(function () {
    function isRtl(el) {
        return window.getComputedStyle(el).direction === "rtl";
    }

    function bindTabBar(root) {
        if (!root || root.dataset.courseShowTabsReady === "1") {
            return;
        }

        var track = root.querySelector(".listing-slider");
        var btnStart = root.querySelector(".listing-tab-scroll-btn--inline-start");
        var btnEnd = root.querySelector(".listing-tab-scroll-btn--inline-end");
        if (!track || !btnStart || !btnEnd) {
            return;
        }

        root.dataset.courseShowTabsReady = "1";

        function step() {
            return Math.max(180, Math.round(track.clientWidth * 0.55));
        }

        function scrollByDir(sign) {
            var amount = step() * sign;
            track.scrollBy({
                left: isRtl(track) ? -amount : amount,
                behavior: "smooth",
            });
        }

        btnStart.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            scrollByDir(-1);
        });

        btnEnd.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            scrollByDir(1);
        });

        track.addEventListener(
            "wheel",
            function (event) {
                if (track.scrollWidth <= track.clientWidth + 8) {
                    return;
                }
                if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) {
                    return;
                }
                event.preventDefault();
                track.scrollLeft += isRtl(track) ? -event.deltaY : event.deltaY;
            },
            { passive: false }
        );
    }

    function init() {
        document.querySelectorAll(".course-show-tabbar").forEach(bindTabBar);
        if (typeof window.initListingTabScrollControls === "function") {
            window.initListingTabScrollControls();
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    document.addEventListener("livewire:navigated", init);
})();
