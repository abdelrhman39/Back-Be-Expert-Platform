(function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  function cardStep(track) {
    var slide = track.querySelector('.home-catalog-slide');
    if (!slide) {
      return Math.max(track.clientWidth / 3, 240);
    }

    var styles = window.getComputedStyle(track);
    var gap = parseFloat(styles.columnGap || styles.gap || '19') || 19;

    return slide.getBoundingClientRect().width + gap;
  }

  function maxScroll(track) {
    return Math.max(0, track.scrollWidth - track.clientWidth);
  }

  function canScroll(track) {
    return maxScroll(track) > 12;
  }

  function isNearEnd(track) {
    return Math.abs(track.scrollLeft) >= maxScroll(track) - 10;
  }

  function isNearStart(track) {
    return Math.abs(track.scrollLeft) <= 10;
  }

  function setupProgramSlider(root) {
    var track = root.querySelector('[data-program-track]');
    var prev = root.querySelector('[data-program-prev]');
    var next = root.querySelector('[data-program-next]');

    if (!track || root.getAttribute('data-ready') === 'true') {
      return;
    }

    root.setAttribute('data-ready', 'true');

    var dragging = false;
    var startX = 0;
    var startScroll = 0;
    var moved = 0;
    var autoTimer = null;
    var resumeTimer = null;

    function scrollByDir(direction) {
      var rtl = document.documentElement.getAttribute('dir') !== 'ltr';
      var amount = cardStep(track) * direction;
      track.scrollBy({ left: rtl ? -amount : amount, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
    }

    function goToStart() {
      track.scrollTo({ left: 0, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
    }

    function stopAuto() {
      window.clearInterval(autoTimer);
      autoTimer = null;
      window.clearTimeout(resumeTimer);
      resumeTimer = null;
    }

    function startAuto() {
      stopAuto();
      if (reduceMotion.matches || !canScroll(track)) {
        return;
      }

      autoTimer = window.setInterval(function () {
        if (document.hidden || dragging || root.matches(':hover')) {
          return;
        }

        if (isNearEnd(track)) {
          goToStart();
          return;
        }

        scrollByDir(1);
      }, 3200);
    }

    function pauseThenResume() {
      stopAuto();
      resumeTimer = window.setTimeout(startAuto, 4200);
    }

    if (prev) {
      prev.addEventListener('click', function () {
        if (isNearStart(track)) {
          track.scrollTo({
            left: document.documentElement.getAttribute('dir') === 'ltr' ? maxScroll(track) : -maxScroll(track),
            behavior: reduceMotion.matches ? 'auto' : 'smooth',
          });
        } else {
          scrollByDir(-1);
        }
        pauseThenResume();
      });
    }

    if (next) {
      next.addEventListener('click', function () {
        if (isNearEnd(track)) {
          goToStart();
        } else {
          scrollByDir(1);
        }
        pauseThenResume();
      });
    }

    function ignoreDragFrom(target) {
      return Boolean(target.closest('button, a.btn, input, textarea, select'));
    }

    track.addEventListener('pointerdown', function (event) {
      if (event.pointerType === 'mouse' && event.button !== 0) {
        return;
      }
      if (ignoreDragFrom(event.target)) {
        return;
      }

      dragging = true;
      moved = 0;
      startX = event.clientX;
      startScroll = track.scrollLeft;
      track.classList.add('is-dragging');
      track.setPointerCapture(event.pointerId);
      stopAuto();
    });

    track.addEventListener('pointermove', function (event) {
      if (!dragging) {
        return;
      }

      var dx = event.clientX - startX;
      moved = Math.max(moved, Math.abs(dx));
      track.scrollLeft = startScroll - dx;
    });

    function endDrag(event) {
      if (!dragging) {
        return;
      }

      dragging = false;
      track.classList.remove('is-dragging');
      if (track.hasPointerCapture(event.pointerId)) {
        track.releasePointerCapture(event.pointerId);
      }

      var step = cardStep(track);
      if (step > 0) {
        var snapped = Math.round(track.scrollLeft / step) * step;
        track.scrollTo({ left: snapped, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
      }

      pauseThenResume();
    }

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    track.addEventListener('click', function (event) {
      if (moved > 10) {
        event.preventDefault();
        event.stopPropagation();
      }
    }, true);

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', function () {
      if (!dragging) {
        startAuto();
      }
    });

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        stopAuto();
      } else {
        startAuto();
      }
    });

    startAuto();
  }

  function initHomeCatalogSliders() {
    document.querySelectorAll('[data-program-slider]').forEach(setupProgramSlider);

    if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.owlCarousel) {
      return;
    }

    var $ = window.jQuery;
    var isLtr = document.documentElement.getAttribute('dir') === 'ltr';

    $('.js-home-catalog-slider').each(function () {
      var $el = $(this);

      if ($el.hasClass('owl-loaded') || $el.closest('[data-program-slider]').length) {
        return;
      }

      var maxSlides = parseInt($el.attr('data-slides') || '3', 10);
      if (!maxSlides || maxSlides < 1) {
        maxSlides = 3;
      }

      var slideCount = $el.children().length;
      var desktopItems = Math.min(3, maxSlides);
      var tabletItems = Math.min(2, maxSlides);

      $el.owlCarousel({
        rtl: !isLtr,
        loop: false,
        rewind: slideCount > desktopItems,
        margin: 16,
        nav: slideCount > desktopItems,
        dots: false,
        smartSpeed: 450,
        autoplay: false,
        mouseDrag: true,
        touchDrag: true,
        pullDrag: true,
        navText: [
          '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>',
          '<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>',
        ],
        responsive: {
          0: {
            items: 1,
            margin: 12,
            nav: slideCount > 1,
          },
          768: {
            items: tabletItems,
            margin: 16,
            nav: slideCount > tabletItems,
          },
          1200: {
            items: desktopItems,
            margin: 20,
            nav: slideCount > desktopItems,
          },
        },
      });
    });
  }

  function boot() {
    initHomeCatalogSliders();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  document.addEventListener('livewire:navigated', boot);
  window.addEventListener('load', boot);
})();
