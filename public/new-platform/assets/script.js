(function ($) {
    "use strict";

    var $slimScrolls = $(".slimscroll");

    // Stick Sidebar — فقط العناصر الخارجية (تجنّب تهيئة مضاعفة للغلاف الداخلي الذي يضيفه Theia)
    function initTheiaStickySidebars() {
        if ($(window).width() <= 767) {
            return;
        }
        var $roots = $(".theiaStickySidebar").filter(function () {
            return $(this).parents(".theiaStickySidebar").length === 0;
        });
        if ($roots.length === 0) {
            return;
        }
        $roots.theiaStickySidebar({
            additionalMarginTop: 30,
        });
        // إعادة حساب الموضع بعد اكتمال التخطيط والصور (يصلح انزياح البطاقة قبل أول تمرير)
        function nudgeStickyRecalc() {
            $(window).trigger("resize");
        }
        requestAnimationFrame(function () {
            requestAnimationFrame(nudgeStickyRecalc);
        });
        $(window).on("load", nudgeStickyRecalc);
    }

    initTheiaStickySidebars();

    function initListingTabScrollControls() {
        $(".listing-tab--with-scroll-controls").each(function () {
            var root = this;
            if (root.dataset.tabsScrollInit === "1") {
                return;
            }
            root.dataset.tabsScrollInit = "1";

            var track = root.querySelector(".listing-slider");
            var btnStart = root.querySelector(".listing-tab-scroll-btn--inline-start");
            var btnEnd = root.querySelector(".listing-tab-scroll-btn--inline-end");
            if (!track || !btnStart || !btnEnd) {
                return;
            }

            function stepAmount() {
                return Math.max(160, Math.round(track.clientWidth * 0.42));
            }

            function applyScrollState() {
                var max = track.scrollWidth - track.clientWidth;
                var eps = 10;
                if (max <= eps) {
                    root.classList.remove("listing-tab--scroll-overflow");
                    root.classList.remove("listing-tab--at-inline-start");
                    root.classList.remove("listing-tab--at-inline-end");
                    btnStart.disabled = true;
                    btnEnd.disabled = true;
                    return;
                }
                root.classList.add("listing-tab--scroll-overflow");
                var sl = track.scrollLeft;
                var rtl = window.getComputedStyle(track).direction === "rtl";
                var atInlineStart;
                var atInlineEnd;
                if (!rtl || sl >= 0) {
                    atInlineStart = sl <= eps;
                    atInlineEnd = sl >= max - eps;
                } else {
                    atInlineStart = sl >= -eps;
                    atInlineEnd = sl <= -(max - eps);
                }
                root.classList.toggle("listing-tab--at-inline-start", atInlineStart);
                root.classList.toggle("listing-tab--at-inline-end", atInlineEnd);
                btnStart.disabled = atInlineStart;
                btnEnd.disabled = atInlineEnd;
            }

            btnStart.addEventListener("click", function () {
                track.scrollBy({ inline: -stepAmount(), behavior: "smooth" });
            });
            btnEnd.addEventListener("click", function () {
                track.scrollBy({ inline: stepAmount(), behavior: "smooth" });
            });

            track.addEventListener("scroll", applyScrollState, { passive: true });
            $(window).on("resize", applyScrollState);

            var ro =
                typeof ResizeObserver !== "undefined"
                    ? new ResizeObserver(function () {
                          applyScrollState();
                      })
                    : null;
            if (ro) {
                ro.observe(track);
            }

            $(window).on("load", applyScrollState);
            applyScrollState();
            requestAnimationFrame(function () {
                requestAnimationFrame(applyScrollState);
            });
        });
    }

    initListingTabScrollControls();
    window.initListingTabScrollControls = initListingTabScrollControls;

    document.addEventListener("livewire:navigated", initListingTabScrollControls);

    var $wrapper = $(".main-wrapper");

    function isMobileMainNav() {
        return window.matchMedia("(max-width: 1199.98px)").matches;
    }

    function closeMobileMainNav() {
        $("html").removeClass("menu-opened");
        $(".sidebar-overlay").removeClass("opened");
        $wrapper.removeClass("slide-nav");
        $(".main-nav li.has-submenu.active").removeClass("active");
        $("#mobile_btn").attr("aria-expanded", "false");
    }

    // Mobile submenu — delegated so it survives Livewire DOM updates
    $(document).on("click", ".main-nav li.has-submenu > a", function (e) {
        if (!isMobileMainNav()) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        var $item = $(this).parent("li.has-submenu");
        var willOpen = !$item.hasClass("active");

        $item.siblings("li.has-submenu.active").each(function () {
            $(this).removeClass("active").children("a").attr("aria-expanded", "false");
        });

        if (willOpen) {
            $item.addClass("active");
            $(this).attr("aria-expanded", "true");
        } else {
            $item.removeClass("active");
            $(this).attr("aria-expanded", "false");
        }
    });

    $(document).on("click", ".main-nav li:not(.has-submenu) > a", function () {
        if (!isMobileMainNav()) {
            return;
        }
        closeMobileMainNav();
    });

    // Sticky Header — keep public site headers visually stable while scrolling
    $(window).scroll(function () {
        var sticky = $(".header"),
            scroll = $(window).scrollTop();
        if (scroll > 0) {
            sticky.addClass("fixed");
        } else {
            sticky
                .not(".new-header, .profile-header")
                .removeClass("fixed");
        }
    });

    // Mobile menu sidebar overlay — keep the markup overlay; do not duplicate it.
    if (!document.querySelector(".sidebar-overlay")) {
        $("header.header, body").first().append('<div class="sidebar-overlay"></div>');
    }
    $(document).on("click", "#mobile_btn", function () {
        $wrapper.toggleClass("slide-nav");
        $(".sidebar-overlay").toggleClass("opened");
        $("html").toggleClass("menu-opened");
        $(this).attr("aria-expanded", $("html").hasClass("menu-opened") ? "true" : "false");
        return false;
    });

    $(document).on("click", ".sidebar-overlay", function () {
        closeMobileMainNav();
        $("#task_window").removeClass("opened");
    });

    $(document).on("click", "#menu_close", function () {
        closeMobileMainNav();
    });

    // Small Sidebar

    $(document).on("click", "#toggle_btn", function () {
        if ($("body").hasClass("mini-sidebar")) {
            $("body").removeClass("mini-sidebar");
            $(".subdrop + ul").slideDown();
        } else {
            $("body").addClass("mini-sidebar");
            $(".subdrop + ul").slideUp();
        }
        return false;
    });

    $(document).on("mouseover", function (e) {
        e.stopPropagation();
        if (
            $("body").hasClass("mini-sidebar") &&
            $("#toggle_btn").is(":visible")
        ) {
            var targ = $(e.target).closest(".sidebar").length;
            if (targ) {
                $("body").addClass("expand-menu");
                $(".subdrop + ul").slideDown();
            } else {
                $("body").removeClass("expand-menu");
                $(".subdrop + ul").slideUp();
            }
            return false;
        }
    });

    // fade in scroll

    if ($(".main-wrapper .aos").length > 0) {
        AOS.init({
            duration: 1200,
            once: true,
        });
    }

    // Mobile menu sidebar overlay

    if (!document.querySelector(".sidebar-overlay")) {
        $("body").append('<div class="sidebar-overlay"></div>');
    }
    $(document).on("click", "#mobile_btns", function () {
        $wrapper.toggleClass("slide-nav");
        $(".sidebar-overlay").toggleClass("opened");
        $("html").toggleClass("menu-opened");
        return false;
    });

    // Sidebar

    var Sidemenu = function () {
        this.$menuItem = $("#sidebar-menu a");
    };

    function initi() {
        var $this = Sidemenu;
        $("#sidebar-menu a").on("click", function (e) {
            if ($(this).parent().hasClass("submenu")) {
                e.preventDefault();
            }
            if (!$(this).hasClass("subdrop")) {
                $("ul", $(this).parents("ul:first")).slideUp(350);
                $("a", $(this).parents("ul:first")).removeClass("subdrop");
                $(this).next("ul").slideDown(350);
                $(this).addClass("subdrop");
            } else if ($(this).hasClass("subdrop")) {
                $(this).removeClass("subdrop");
                $(this).next("ul").slideUp(350);
            }
        });
        $("#sidebar-menu ul li.submenu a.active")
            .parents("li:last")
            .children("a:first")
            .addClass("active")
            .trigger("click");
    }

    // Sidebar Initiate
    initi();

    // Sidebar Slimscroll

    if ($slimScrolls.length > 0) {
        $slimScrolls.slimScroll({
            height: "auto",
            width: "100%",
            position: "right",
            size: "7px",
            color: "#ccc",
            wheelStep: 10,
            touchScrollStep: 100,
        });
        var wHeight = $(window).height();
        $slimScrolls.height(wHeight);
        $(
            ".left-sidebar .slimScrollDiv, .sidebar-menu .slimScrollDiv, .sidebar-menu .slimScrollDiv"
        ).height(wHeight);
        $(".right-sidebar .slimScrollDiv").height(wHeight - 30);
        $(".chat .slimScrollDiv").height(wHeight - 70);
        $(".chat.settings-main .slimScrollDiv").height(wHeight);
        $(".right-sidebar.video-right-sidebar .slimScrollDiv").height(
            wHeight - 90
        );
        $(window).resize(function () {
            var rHeight = $(window).height();
            $slimScrolls.height(rHeight);
            $(
                ".left-sidebar .slimScrollDiv, .sidebar-menu .slimScrollDiv, .sidebar-menu .slimScrollDiv"
            ).height(rHeight);
            $(".right-sidebar .slimScrollDiv").height(wHeight - 30);
            $(".chat .slimScrollDiv").height(rHeight - 70);
            $(".chat.settings-main .slimScrollDiv").height(wHeight);
            $(".right-sidebar.video-right-sidebar .slimScrollDiv").height(
                wHeight - 90
            );
        });
    }

    //Gigs Card Carousel

    if ($(".gigs-slider").length > 0) {
        $(".gigs-slider").owlCarousel({
            rtl: true,
            loop: false,
            margin: 24,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            navContainer: ".worknav",
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1000: {
                    items: 3,
                },
            },
        });
    }

    //Gigs Card Carousel

    //Gigs Card Carousel

    if ($('.gigs-card-cat').length > 0) {
        $('.gigs-card-cat').owlCarousel({
            rtl: true,
            loop: false,
            margin: 20,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>'
            ],
            responsive: {
                0: {
                    items: 1
                },
                420: {
                    items: 2
                },
                500: {
                    items: 2
                },
                768: {
                    items: 3
                },
                1000: {
                    items: 5
                }
            }
        })
    }


    if ($(".gigs-card-slider").length > 0) {
        $(".gigs-card-slider").owlCarousel({
            rtl: true,
            loop: false,
            margin: 24,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 1
                },
                500: {
                    items: 2
                },
                768: {
                    items: 3
                },
                1000: {
                    items: 3
                }
            },
        });
    }

    //Card Image Carousel

    if ($(".img-slider").length > 0) {
        $(".img-slider").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: false,
            dots: true,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 1,
                },
                768: {
                    items: 1,
                },
                1000: {
                    items: 1,
                },
            },
        });
    }

    // Clients Logo Carousel

    if ($(".clients-slider").length > 0) {
        $(".clients-slider").owlCarousel({
            rtl: true,
            loop: false,
            margin: 24,
            nav: false,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 2,
                },
                550: {
                    items: 2,
                },
                768: {
                    items: 2,
                },
                1000: {
                    items: 2,
                },
            },
        });
    }

    // Popular Category Carousel

    if ($(".popular-category-slider").length > 0) {
        $(".popular-category-slider").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: false,
            dots: true,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 2,
                },
                768: {
                    items: 3,
                },
                1000: {
                    items: 4,
                },
                1200: {
                    items: 5,
                },
            },
        });
    }

    // Review Carousel

    if ($(".review-slider").length > 0) {
        $(".review-slider").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1000: {
                    items: 2,
                },
                1200: {
                    items: 3,
                },
            },
        });
    }

    // Blog Carousel

    if ($(".blog-carousel").length > 0) {
        $(".blog-carousel").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: false,
            dots: true,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1000: {
                    items: 2,
                },
                1200: {
                    items: 3,
                },
            },
        });
    }

    // Team Carousel

    if ($(".team-slider").length > 0) {
        $(".team-slider").owlCarousel({
            rtl: true,
            loop: false,
            margin: 24,
            nav: false,
            dots: true,
            smartSpeed: 2000,
            autoplay: false,
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1000: {
                    items: 3,
                },
            },
        });
    }

    if ($(".home-slider").length > 0) {
        $(".home-slider").owlCarousel({
            rtl: true,
            loop: false,
            margin: 22,
            nav: false,
            dots: false,
            smartSpeed: 2000,
            autoplay: true,
            navText: false,
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 1,
                },
                1200: {
                    items: 1,
                },
            },
        });
    }


    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        if (scroll >= 500) {
            $(".back-to-top-icon").addClass("show");
        } else {
            $(".back-to-top-icon").removeClass("show");
        }
    });

    // JQuery counterUp

    if ($(".counter").length > 0) {
        $(".counter").counterUp({
            delay: 10,
            time: 2000,
        });
        $(".counter").addClass("animated fadeInDownBig");
    }

    // Banner

    var TxtRotate = function (el, toRotate, period) {
        this.toRotate = toRotate;
        this.el = el;
        this.loopNum = 0;
        this.period = parseInt(period, 10) || 2000;
        this.txt = "";
        this.tick();
        this.isDeleting = false;
    };
    TxtRotate.prototype.tick = function () {
        var i = this.loopNum % this.toRotate.length;
        var fullTxt = this.toRotate[i];
        if (this.isDeleting) {
            this.txt = fullTxt.substring(0, this.txt.length - 1);
        } else {
            this.txt = fullTxt.substring(0, this.txt.length + 1);
        }
        this.el.innerHTML = ' <span class = "wrap"> ' + this.txt + " </span>";
        var that = this;
        var delta = 300 - Math.random() * 100;
        if (this.isDeleting) {
            delta /= 2;
        }
        if (!this.isDeleting && this.txt === fullTxt) {
            delta = this.period;
            this.isDeleting = true;
        } else if (this.isDeleting && this.txt === "") {
            this.isDeleting = false;
            this.loopNum++;
            delta = 500;
        }
        setTimeout(function () {
            that.tick();
        }, delta);
    };
    window.onload = function () {
        var elements = document.getElementsByClassName("txt-rotate");
        for (var i = 0; i < elements.length; i++) {
            var toRotate = elements[i].getAttribute("data-rotate");
            var period = elements[i].getAttribute("data-period");
            if (toRotate) {
                new TxtRotate(elements[i], JSON.parse(toRotate), period);
            }
        }
        // INJECT CSS
        var css = document.createElement("style");
        css.type = "text/css";
        css.innerHTML = ".txt-rotate > .wrap { border-right: 0 }";
        document.body.appendChild(css);
    };

    // loader
    feather.replace();



    // Select Favourite

    $(".fav-icon").on("click", function () {
        $(this).toggleClass("favourite");
    });

    // Request Mail

    function emailcreate() {
        /* ajax removed */
        return false;
    }
    $("#phone-num").keyup(function () {
        if (this.value.match(/[^0-9]/g)) {
            this.value = this.value.replace(/[^0-9^-]/g, "");
        }
    });

    // Select 2

    if ($(".select").length > 0) {
        $(".select").select2({
            minimumResultsForSearch: -1,
            width: "100%",
        });
    }

    // Add

    if ($(".view").length > 0) {
        $(".view .btn").on("click", function (e) {
            $(this).addClass("active");
        });
    }

    // Slick Testimonial Two

    if ($(".service-slider").length > 0) {
        $(".service-slider").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            fade: true,
            asNavFor: ".slider-nav-thumbnails",
        });
    }

    if ($(".slider-nav-thumbnails").length > 0) {
        $(".slider-nav-thumbnails").slick({
            slidesToShow: 5,
            slidesToScroll: 1,
            asNavFor: ".service-slider",
            dots: false,
            arrows: false,
            centerMode: false,
            focusOnSelect: true,
        });
    }

    // Read More & Less

    if ($(".more-content").length > 0) {
        $(".more-content").hide();
        $(".read-more").on("click", function () {
            $(this).text(
                $(this).text() === "Read Less" ? "Read More" : "Read Less"
            );
            $(".more-content").toggle(900);
        });
    }

    // recent Carousel

    if ($(".recent-carousel").length > 0) {
        $(".recent-carousel").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            navContainer: ".mynav1",
            responsive: {
                0: {
                    items: 1,
                },
                550: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                1200: {
                    items: 3,
                },
            },
        });
    }

    // recent Carousel

    if ($(".service-sliders").length > 0) {
        $(".service-sliders").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            navContainer: ".service-nav",
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 2,
                },
                992: {
                    items: 3,
                },
                1200: {
                    items: 4,
                },
            },
        });
    }

    // Trending Carousel

    if ($(".trend-items").length > 0) {
        $(".trend-items").owlCarousel({
            rtl: true,
            loop: true,
            margin: 22,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            navContainer: ".trend-nav",
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 2,
                },
                992: {
                    items: 3,
                },
                1200: {
                    items: 4,
                },
            },
        });
    }

    // Relate Carousel

    if ($(".relate-slider").length > 0) {
        $(".relate-slider").owlCarousel({
            rtl: true,
            loop: true,
            margin: 22,
            nav: false,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 2,
                },
                1200: {
                    items: 3,
                },
            },
        });
    }

    // Testimonial Carousel

    if ($(".testimonial-slider").length > 0) {
        $(".testimonial-slider").owlCarousel({
            rtl: true,
            loop: true,
            margin: 22,
            nav: true,
            dots: false,
            smartSpeed: 2000,
            autoplay: false,
            navText: [
                '<i class="fa-solid fa-chevron-left"></i>',
                '<i class="fa-solid fa-chevron-right"></i>',
            ],
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 2,
                },
                1200: {
                    items: 3,
                },
            },
        });
    }

    // Login Carousel

    if ($(".login-carousel").length > 0) {
        $(".login-carousel").owlCarousel({
            rtl: true,
            loop: true,
            margin: 24,
            nav: false,
            dots: true,
            smartSpeed: 2000,
            autoplay: false,
            responsive: {
                0: {
                    items: 1,
                },
            },
        });
    }

    // View all Show hide One

    if ($(".viewall-one").length > 0) {
        $(".viewall-one").hide();
        $(".viewall-button-one").on("click", function () {
            $(this).text(
                $(this).text() === "Less Categories"
                    ? "More 20+ Categories"
                    : "Less Categories"
            );
            $(".viewall-one").slideToggle(900);
        });
    }

    // View all Show hide One

    if ($(".viewall-location").length > 0) {
        $(".viewall-location").hide();
        $(".viewall-btn-location").on("click", function () {
            $(this).text(
                $(this).text() === "Less Locations"
                    ? "More 20+ Locations"
                    : "Less Locations"
            );
            $(".viewall-location").slideToggle(900);
        });
    }

    // Filter Select

    if ($(".filters-wrap").length > 0) {
        var show = true;
        $(".filter-header a").on("click", function () {
            if (show) {
                $(this)
                    .closest(".collapse-card")
                    .children(".collapse-body")
                    .css("display", "block");
                $(this).closest(".collapse-card").addClass("active");
                show = false;
            } else {
                $(".collapse-body").css("display", "none");
                $(this).closest(".collapse-card").removeClass("active");
                show = true;
            }
        });
    }

    // More & Less

    if ($(".more-content").length > 0) {
        $(".more-content").hide();
        $(".show-more").on("click", function () {
            $(this).text(
                $(this).text() === "Show Less" ? "Show More" : "Show Less"
            );
            $(".more-content").toggle(900);
        });
    }

    // Password Eye

    if ($(".toggle-password").length > 0) {
        $(document).on("click", ".toggle-password", function () {
            $(this).toggleClass("feather-eye feather-eye-off");
            var input = $(".pass-input");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }

    if ($(".toggle-password-confirm").length > 0) {
        $(document).on("click", ".toggle-password-confirm", function () {
            $(this).toggleClass("feather-eye feather-eye-off");
            var input = $(".pass-confirm");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }

    // Floating Label

    if ($(".floating").length > 0) {
        $(".floating")
            .on("focus blur", function (e) {
                $(this)
                    .parents(".form-focus")
                    .toggleClass(
                        "focused",
                        e.type === "focus" || this.value.length > 0
                    );
            })
            .trigger("blur");
    }

    // Tooltip

    if ($('[data-bs-toggle="tooltip"]').length > 0) {
        var tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Input Enable & Disable

    $(".extra-serv .checkmark").on("click", function () {
        var $listSort = $(".exta-label");
        if ($listSort.attr("disabled")) {
            $listSort.removeAttr("disabled");
        } else {
            $listSort.attr("disabled", "disabled");
        }
    });

    // Coming Soon

    if ($(".days-count").length > 0) {
        // Get html elements
        let day = document.querySelector(".days");
        let hour = document.querySelector(".hours");
        let minute = document.querySelector(".minutes");
        let second = document.querySelector(".seconds");

        function setCountdown() {
            // Set countdown date
            let countdownDate = new Date("sep 27, 2025 16:00:00").getTime();

            // Update countdown every second
            let updateCount = setInterval(function () {
                // Get today's date and time
                let todayDate = new Date().getTime();

                // Get distance between now and countdown date
                let distance = countdownDate - todayDate;

                let days = Math.floor(distance / (1000 * 60 * 60 * 24));

                let hours = Math.floor(
                    (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
                );

                let minutes = Math.floor(
                    (distance % (1000 * 60 * 60)) / (1000 * 60)
                );

                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display values in html elements
                day.textContent = days;
                hour.textContent = hours;
                minute.textContent = minutes;
                second.textContent = seconds;

                // if countdown expires
                if (distance < 0) {
                    clearInterval(updateCount);
                    document.querySelector(".days-count").innerHTML =
                        "<h1>EXPIRED</h1>";
                }
            }, 1000);
        }

        setCountdown();
    }

    // Add Sign
    $(document).on("click", ".trash-sign", function () {
        $(this).closest(".sign-cont").remove();
        return false;
    });
    $(document).on("click", ".amount-add", function () {
        var signcontent =
            '<div class="row sign-cont">' +
            '<div class="col-md-4">' +
            '<div class="form-wrap">' +
            '<input type="text" class="form-control" placeholder="I Can">' +
            "</div>" +
            "</div>" +
            '<div class="col-md-4">' +
            '<div class="form-wrap">' +
            '<input type="text" class="form-control" placeholder="For ($)">' +
            "</div>" +
            "</div>" +
            '<div class="col-md-4">' +
            '<div class="form-wrap d-flex align-items-center">' +
            '<input type="text" class="form-control" placeholder="In (Day)">' +
            '<a href="javascript:void(0);" class="trash-sign ms-2 text-danger"><i class="feather-trash-2"></i></a>' +
            "</div>" +
            "</div>" +
            "</div>";
        $(".add-content").append(signcontent);
        return false;
    });

    // Datatable

    if ($(".datatable").length > 0) {
        $(".datatable").DataTable({
            bFilter: true,
            bLengthChange: false,
            bInfo: true,
            ordering: false,
            language: {
                search: " ",
                searchPlaceholder: "Search",
                paginate: {
                    next: ' <i class=" fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i> ',
                },
            },
            initComplete: (settings, json) => {
                $(".dataTables_paginate").appendTo("#tablepage");
                $(".dataTables_filter").appendTo("#tablefilter");
                $(".dataTables_info").appendTo("#tableinfo");
            },
        });
    }

    // Date Time Picker

    if ($(".datetimepicker").length > 0) {
        $(".datetimepicker").datetimepicker({
            format: "DD-MM-YYYY",
            icons: {
                up: "fa fa-angle-up",
                down: "fa-solid fa-angle-down",
                next: "fa-solid fa-angle-right",
                previous: "fa-solid fa-angle-left",
            },
        });
    }

    //Top Online Contacts
    if ($(".top-online-contacts .swiper-container").length > 0) {
        var swiper = new Swiper(".top-online-contacts .swiper-container", {
            slidesPerView: 5,
            spaceBetween: 15,
        });
    }

    // Chat Search Visible

    $(".user-chat-search-btn").on("click", function () {
        $(".user-chat-search").addClass("visible-chat");
    });
    $(".user-close-btn-chat").on("click", function () {
        $(".user-chat-search").removeClass("visible-chat");
    });

    // Chat Search Visible

    $(".chat-search-btn").on("click", function () {
        $(".chat-search").addClass("visible-chat");
    });
    $(".close-btn-chat").on("click", function () {
        $(".chat-search").removeClass("visible-chat");
    });
    $(".chat-search .form-control").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $(".chat .chat-body .messages .chats").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $(
        ".user-list-item:not(body.status-page .user-list-item, body.voice-call-page .user-list-item)"
    ).on("click", function () {
        if ($(window).width() < 992) {
            $(".left-sidebar").addClass("hide-left-sidebar");
            $(".chat").addClass("show-chatbar");
        }
    });

    $(".left_sides").on("click", function () {
        if ($(window).width() <= 991) {
            $(".sidebar-group").removeClass("hide-left-sidebar");
            $(".sidebar-menu").removeClass("d-none");
        }
    });
    $(".left_sides").on("click", function () {
        if ($(window).width() <= 991) {
            $(".chat-messages").removeClass("show-chatbar");
        }
    });
    $(".user-list li a").on("click", function () {
        if ($(window).width() <= 767) {
            $(".left-sidebar").addClass("hide-left-sidebar");
            $(".sidebar-menu").addClass("d-none");
        }
    });

    const $menu = $(".dropdowns");

    const onMouseUp = (e) => {
        if (
            !$menu.is(e.target) && // If the target of the click isn't the container...
            $menu.has(e.target).length === 0
        ) {
            // ... or a descendant of the container.
            $menu.removeClass("is-active");
        }
    };

    $(".toggle").on("click", () => {
        $menu
            .toggleClass("is-active")
            .promise()
            .done(() => {
                if ($menu.hasClass("is-active")) {
                    $(document).on("mouseup", onMouseUp); // Only listen for mouseup when menu is active...
                } else {
                    $(document).off("mouseup", onMouseUp); // else remove listener.
                }
            });
    });
})(jQuery);


if ($("#openVideoBtn").length > 0) {
    document.addEventListener("DOMContentLoaded", function () {
        const openVideoBtn = document.getElementById("openVideoBtn");
        const videoModal = document.getElementById("videoModal");
        const closeModal = document.getElementById("closeModal");
        const youtubeIframe = document.getElementById("youtubeIframe");
        const youtubeVideoUrl = openVideoBtn.getAttribute("data-video-url");

        // Ensure all necessary elements are present
        if (openVideoBtn && videoModal && closeModal && youtubeIframe) {
            // YouTube video URL with an actual video ID
            // const youtubeVideoUrl = "https://www.youtube.com/embed/1trvO6dqQUI";

            openVideoBtn.addEventListener("click", (e) => {
                e.preventDefault();
                youtubeIframe.src = youtubeVideoUrl;
                videoModal.style.display = "flex";
            });

            closeModal.addEventListener("click", () => {
                videoModal.style.display = "none";
                youtubeIframe.src = ""; // Reset the iframe to stop the video
            });
        }
    });
}
// setCollapseState
function setCollapseState() {
    const sections = ["categories", "levels", "budget", "types", "blogs", "field"];

    sections.forEach((id) => {
        const link = document.querySelector(
            `.collapse-card h4.card-title a[href="#${id}"]`
        );
        const content = document.querySelector(`#${id}`);
        if (!link || !content) return;

        if (window.innerWidth > 992) {
            // Desktop: expand
            link.classList.remove("collapsed");
            link.setAttribute("aria-expanded", "true");
            content.classList.add("show");
        } else {
            // Mobile/tablet: collapse
            link.classList.add("collapsed");
            link.setAttribute("aria-expanded", "false");
            content.classList.remove("show");
        }
    });
}

// Run on load
setCollapseState();

// Run again when window is resized
window.addEventListener("resize", setCollapseState);

// Run on page load
setCollapseState();


/* ================================================================
  START OF CUSTOM CMS-SELECT COMPONENT
================================================================ */
window.initCmsSelects = function(container = document) {
    const wrappers = (container instanceof HTMLElement && container.classList.contains("cms-wrapper"))
        ? [container]
        : container.querySelectorAll(".cms-wrapper");

    wrappers.forEach(function (wrapper) {
        if (wrapper.dataset.cmsInitialized) {
            if (wrapper.refreshOptions) wrapper.refreshOptions();
            return;
        }

        var selectId  = wrapper.dataset.for;
        var nativeEl  = document.getElementById(selectId);
        if (!nativeEl) return;

        var isMulti   = nativeEl.multiple;
        var trigger   = wrapper.querySelector(".cms-trigger");
        var search    = wrapper.querySelector(".cms-search");
        var optList   = wrapper.querySelector(".cms-options");
        var placeholder = wrapper.querySelector(".cms-placeholder");
        var countEl   = wrapper.querySelector(".cms-footer-count");
        var clearBtn  = wrapper.querySelector(".cms-footer-clear");
        var emptyMsg  = wrapper.querySelector(".cms-empty");
        var isDisabled = wrapper.classList.contains("is-disabled");

        if (isDisabled) return;
        wrapper.dataset.cmsInitialized = "true";

        var selected = {};

        function syncSelectedFromNative() {
            selected = {};
            Array.from(nativeEl.options).forEach(function(opt) {
                if (opt.selected && opt.value) {
                    selected[opt.value] = opt.text;
                }
            });
        }

        wrapper.refreshOptions = function() {
            optList.innerHTML = "";
            Array.from(nativeEl.options).forEach(function(opt) {
                if (!opt.value && !isMulti) return;
                var optDiv = document.createElement("div");
                optDiv.className = "cms-option" + (opt.selected ? " is-selected" : "");
                optDiv.dataset.value = opt.value;
                optDiv.dataset.label = opt.text;
                optDiv.setAttribute("role", "option");
                optDiv.setAttribute("aria-selected", opt.selected ? "true" : "false");
                optDiv.innerHTML = `
                    <span class="cms-option-check"><i class="bi bi-check2"></i></span>
                    <span class="cms-option-label">${opt.text}</span>
                `;
                optList.appendChild(optDiv);
            });
            syncSelectedFromNative();
            renderUI();
        };

        function renderUI() {
            trigger.querySelectorAll(".cms-tag, .cms-single-label").forEach(function (t) { t.remove(); });
            var values = Object.keys(selected);
            if (values.length === 0) {
                placeholder.classList.add("is-visible");
            } else {
                placeholder.classList.remove("is-visible");
                if (isMulti) {
                    values.forEach(function (val) {
                        var tag = document.createElement("span");
                        tag.className = "cms-tag";
                        tag.innerHTML =
                            "<span class=\"cms-tag-label\">" + escHtml(selected[val]) + "</span>" +
                            "<button type=\"button\" class=\"cms-tag-remove\" data-val=\"" + escHtml(val) + "\" tabindex=\"-1\">&#215;</button>";
                        trigger.insertBefore(tag, placeholder);
                    });
                } else {
                    var label = document.createElement("span");
                    label.className = "cms-single-label";
                    label.textContent = selected[values[0]];
                    trigger.insertBefore(label, placeholder);
                }
            }
            if (countEl) {
                var total = values.length;
                countEl.textContent = total > 0 ? total + " Selected" : "";
            }
            if (clearBtn) clearBtn.disabled = values.length === 0;
            syncNative();
        }

        function syncNative() {
            Array.from(nativeEl.options).forEach(function (opt) {
                opt.selected = selected.hasOwnProperty(opt.value);
            });
            nativeEl.dispatchEvent(new Event("change", { bubbles: true }));
        }

        function renderOptions() {
            wrapper.querySelectorAll(".cms-option").forEach(function (opt) {
                var isSelected = selected.hasOwnProperty(opt.dataset.value);
                opt.classList.toggle("is-selected", isSelected);
                opt.setAttribute("aria-selected", isSelected ? "true" : "false");
            });
        }

        function toggleOption(value, label) {
            if (isMulti) {
                if (selected.hasOwnProperty(value)) { delete selected[value]; } else { selected[value] = label; }
            } else {
                selected = {}; selected[value] = label; close();
            }
            renderOptions(); renderUI();
        }

        function open() {
            document.querySelectorAll(".cms-wrapper.is-open").forEach(w => { if(w !== wrapper) w.classList.remove("is-open")});
            wrapper.classList.add("is-open");
            wrapper.setAttribute("aria-expanded", "true");
            if (search) {
                search.value = "";
                filterOptions("");
                setTimeout(function () { search.focus(); }, 30);
            }
        }

        function close() {
            wrapper.classList.remove("is-open");
            wrapper.setAttribute("aria-expanded", "false");
        }

        function isOpen() { return wrapper.classList.contains("is-open"); }

        function filterOptions(query) {
            var q = query.toLowerCase().trim();
            var visibleCount = 0;
            wrapper.querySelectorAll(".cms-option").forEach(function (opt) {
                var label = opt.dataset.label.toLowerCase();
                var show = label.includes(q);
                opt.style.display = show ? "" : "none";
                if (show) visibleCount++;
            });
            emptyMsg.style.display = visibleCount === 0 ? "block" : "none";
        }

        function escHtml(str) {
            return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        trigger.addEventListener("click", function (e) {
            if (e.target.closest(".cms-tag-remove")) return;
            isOpen() ? close() : open();
        });

        wrapper.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") { if (!isOpen()) { e.preventDefault(); open(); } }
            if (e.key === "Escape") { close(); wrapper.focus(); }
        });

        trigger.addEventListener("click", function (e) {
            var removeBtn = e.target.closest(".cms-tag-remove");
            if (!removeBtn) return;
            e.stopPropagation();
            delete selected[removeBtn.dataset.val];
            renderOptions(); renderUI();
        });

        optList.addEventListener("click", function (e) {
            var opt = e.target.closest(".cms-option");
            if (!opt || opt.style.display === "none") return;
            toggleOption(opt.dataset.value, opt.dataset.label);
        });

        if (search) {
            search.addEventListener("input", function () { filterOptions(search.value); });
            search.addEventListener("click", function (e) { e.stopPropagation(); });
        }

        if (clearBtn) {
            clearBtn.addEventListener("click", function () {
                selected = {}; renderOptions(); renderUI();
            });
        }

        document.addEventListener("click", function (e) { if (!wrapper.contains(e.target)) close(); });

        syncSelectedFromNative();
        renderUI();
        renderOptions();
    });
};

/* ================================================================
  END OF CUSTOM CMS-SELECT COMPONENT
================================================================ */

/* ================================================================
  WIZARD FORM COMPONENT
================================================================ */
window.initWizardForm = function(formId, totalSteps) {
    const form = document.getElementById(formId);
    if (!form) return;

    function updateSteps(step) {
        // Update indicators
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById('step-indicator-' + i);
            if (indicator) {
                indicator.classList.remove('active', 'completed');
                if (i < step) indicator.classList.add('completed');
                else if (i === step) indicator.classList.add('active');
            }

            const line = document.getElementById('step-line-' + i);
            if (line) {
                line.classList.toggle('completed', i < step);
            }
        }

        // Update panels
        document.querySelectorAll('.wizard-step-panel').forEach(p => p.classList.remove('active'));
        const activePanel = document.getElementById('step-panel-' + step);
        if (activePanel) {
            activePanel.classList.add('active');
            // Re-init custom selects/datepickers in the new panel if needed
            if (window.initCmsSelects) window.initCmsSelects(activePanel);
            if (window.initCustomDatepickers) window.initCustomDatepickers(activePanel);
        }

        // Scroll to top of form
        const blogForm = form.closest('.blog-form') || form;
        window.scrollTo({ top: blogForm.offsetTop - 80, behavior: 'smooth' });
    }

    function validateStep(step) {
        const panel = document.getElementById('step-panel-' + step);
        if (!panel) return true;

        let isValid = true;
        const requireds = panel.querySelectorAll('[required]');

        requireds.forEach(input => {
            let fieldValid = true;
            if (input.type === 'radio') {
                const name = input.name;
                fieldValid = panel.querySelector(`input[name="${name}"]:checked`) !== null;
            } else if (input.tagName === 'SELECT') {
                fieldValid = input.value !== '';
                const wrapper = input.parentElement.querySelector('.cms-wrapper');
                if (wrapper) wrapper.classList.toggle('is-invalid-cms', !fieldValid);
            } else if (input.type === 'hidden' && input.parentElement.querySelector('.custom-datepicker-wrap')) {
                fieldValid = input.value !== '';
                const dpTrigger = input.parentElement.querySelector('.custom-datepicker-trigger');
                if (dpTrigger) dpTrigger.classList.toggle('is-invalid', !fieldValid);
            } else {
                fieldValid = input.value.trim() !== '';
            }

            if (!fieldValid) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!isValid && window.toastr) {
            toastr.error('Please fill all required fields');
        }
        return isValid;
    }

    // Next Button Click
    form.addEventListener('click', (e) => {
        const nextBtn = e.target.closest('.wizard-next-btn');
        if (nextBtn) {
            const current = parseInt(nextBtn.dataset.current);
            const next = parseInt(nextBtn.dataset.next);
            if (validateStep(current)) {
                updateSteps(next);
            }
        }

        const prevBtn = e.target.closest('.wizard-prev-btn');
        if (prevBtn) {
            const prev = parseInt(prevBtn.dataset.prev);
            updateSteps(prev);
        }
    });

    // Remove error highlights on input
    form.addEventListener('input', (e) => {
        e.target.classList.remove('is-invalid');
        const wrapper = e.target.parentElement.querySelector('.cms-wrapper');
        if (wrapper) wrapper.classList.remove('is-invalid-cms');
    });
};

$(document).ready(function() {
    if (window.initCustomDatepickers) window.initCustomDatepickers();
});

/**
 * Muneer sidebar fallback toggler:
 * - Open on trigger click.
 * - Close on close button, outside click, or Escape.
 */
(function () {
    if (document.documentElement.classList.contains("use-domain-a11y")) return;

    function initMuneerSidebarFallback() {
        var sidebar = document.getElementById("muneer-sidebar");
        var trigger = document.getElementById("muneer-trigger-button");
        var closeBtn = document.getElementById("muneer-popup-close");
        if (!sidebar || !trigger) return;

        function isOpen() {
            return sidebar.classList.contains("muneer-sidebar--open");
        }

        function openSidebar() {
            sidebar.classList.add("muneer-sidebar--open");
            sidebar.setAttribute("aria-hidden", "false");
        }

        function closeSidebar() {
            sidebar.classList.remove("muneer-sidebar--open");
            sidebar.setAttribute("aria-hidden", "true");
        }

        trigger.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (isOpen()) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        }

        document.addEventListener("click", function (e) {
            if (!isOpen()) return;
            var clickedInsideSidebar = sidebar.contains(e.target);
            var clickedTrigger = trigger.contains(e.target);
            if (!clickedInsideSidebar && !clickedTrigger) {
                closeSidebar();
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && isOpen()) {
                closeSidebar();
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initMuneerSidebarFallback);
    } else {
        initMuneerSidebarFallback();
    }
})();

/**
 * Legacy HTML: group scattered float buttons into one vertical rail.
 */
(function () {
    function initFloatActionsRail() {
        if (document.querySelector(".float-actions-rail")) {
            return;
        }

        var order = [".back-to-top-icon", ".float-whts", ".float-call", ".float-mail"];
        var items = [];

        order.forEach(function (selector) {
            var el = document.querySelector(selector);
            if (el) {
                items.push(el);
            }
        });

        if (items.length < 2) {
            return;
        }

        var rail = document.createElement("aside");
        var isAr = (document.documentElement.getAttribute("lang") || "ar").indexOf("en") !== 0;
        rail.className = "float-actions-rail";
        rail.setAttribute("aria-label", isAr ? "روابط سريعة" : "Quick links");

        document.body.appendChild(rail);

        items.forEach(function (el) {
            if (el.parentElement && el.parentElement.classList.contains("back-to-top")) {
                el.parentElement.remove();
            }
            rail.appendChild(el);

            if (el.classList.contains("back-to-top-icon") && !document.getElementById("domain-a11y-fab-slot")) {
                var slot = document.createElement("div");
                slot.id = "domain-a11y-fab-slot";
                slot.className = "float-actions-rail__a11y-slot";
                rail.appendChild(slot);
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initFloatActionsRail);
    } else {
        initFloatActionsRail();
    }
})();
