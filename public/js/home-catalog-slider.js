(function () {
  function initHomeCatalogSliders() {
    if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.owlCarousel) {
      return;
    }

    var $ = window.jQuery;

    $('.js-home-catalog-slider').each(function () {
      var $el = $(this);

      if ($el.hasClass('owl-loaded')) {
        return;
      }

      var maxSlides = parseInt($el.attr('data-slides') || '3', 10);
      if (!maxSlides || maxSlides < 1) {
        maxSlides = 3;
      }

      var slideCount = $el.children().length;
      var desktopItems = Math.min(3, maxSlides);
      var tabletItems = Math.min(2, maxSlides);
      var showNav = slideCount > 1;
      var showDots = slideCount > 1;

      $el.owlCarousel({
        rtl: document.documentElement.getAttribute('dir') !== 'ltr',
        loop: false,
        rewind: false,
        margin: 16,
        nav: showNav,
        dots: showDots,
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
            stagePadding: 12,
            dots: slideCount > 1,
          },
          576: {
            items: 1,
            margin: 14,
            stagePadding: 28,
            dots: slideCount > 1,
          },
          768: {
            items: tabletItems,
            margin: 16,
            stagePadding: 0,
            dots: slideCount > tabletItems,
          },
          992: {
            items: desktopItems,
            margin: 20,
            stagePadding: 0,
            dots: slideCount > desktopItems,
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
