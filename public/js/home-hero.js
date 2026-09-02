(function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  var timers = [];

  function clearTimers() {
    timers.forEach(function (id) {
      window.clearInterval(id);
    });
    timers = [];
  }

  function slidesOf(root) {
    return Array.prototype.slice.call(root.querySelectorAll('[data-hero-slide]'));
  }

  function showSlide(slides, index) {
    slides.forEach(function (slide, i) {
      slide.classList.toggle('is-active', i === index);
    });
  }

  function setupSlider(root) {
    var slides = slidesOf(root);
    if (slides.length < 2) {
      return;
    }

    showSlide(slides, 0);

    if (reduceMotion.matches) {
      return;
    }

    var interval = parseInt(root.getAttribute('data-hero-interval') || '7000', 10);
    if (!interval || interval < 2500) {
      interval = 7000;
    }

    var index = 0;
    var timer = window.setInterval(function () {
      if (document.hidden) {
        return;
      }
      index = (index + 1) % slides.length;
      showSlide(slides, index);
    }, interval);

    timers.push(timer);
  }

  function setupVideos() {
    document.querySelectorAll('[data-hero-video]').forEach(function (video) {
      if (reduceMotion.matches) {
        video.pause();
        video.removeAttribute('autoplay');
        return;
      }

      var play = video.play();
      if (play && typeof play.catch === 'function') {
        play.catch(function () {});
      }
    });
  }

  function setupAll() {
    clearTimers();
    document.querySelectorAll('[data-hero-slider]').forEach(setupSlider);
    setupVideos();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAll);
  } else {
    setupAll();
  }

  if (reduceMotion.addEventListener) {
    reduceMotion.addEventListener('change', setupAll);
  }

  document.addEventListener('livewire:navigated', setupAll);
})();
