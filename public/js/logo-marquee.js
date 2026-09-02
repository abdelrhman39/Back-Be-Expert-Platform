(function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  function sourceItems(group) {
    return Array.prototype.filter.call(group.children, function (item) {
      return item.getAttribute('data-marquee-clone') !== 'true';
    });
  }

  function resetTrack(track, group) {
    Array.prototype.slice.call(track.children).forEach(function (node) {
      if (node !== group) {
        node.remove();
      }
    });

    Array.prototype.slice.call(group.children).forEach(function (item) {
      if (item.getAttribute('data-marquee-clone') === 'true') {
        item.remove();
      }
    });
  }

  function fillGroup(group, minWidth) {
    var originals = sourceItems(group);
    if (!originals.length) {
      return;
    }

    var guard = 0;
    while (group.scrollWidth < minWidth && guard < 24) {
      originals.forEach(function (node) {
        var clone = node.cloneNode(true);
        clone.setAttribute('data-marquee-clone', 'true');
        clone.setAttribute('aria-hidden', 'true');
        var img = clone.querySelector('img');
        if (img) {
          img.setAttribute('alt', '');
        }
        group.appendChild(clone);
      });
      guard += 1;
    }
  }

  function duplicateGroup(track, group) {
    var copy = group.cloneNode(true);
    copy.setAttribute('aria-hidden', 'true');
    copy.removeAttribute('data-logo-marquee-group');
    copy.querySelectorAll('img').forEach(function (img) {
      img.setAttribute('alt', '');
    });
    track.appendChild(copy);
  }

  function setup(root) {
    var viewport = root.querySelector('.lg-logo-marquee__viewport');
    var track = root.querySelector('[data-logo-marquee-track]');
    var group = root.querySelector('[data-logo-marquee-group]');

    if (!viewport || !track || !group || !group.children.length) {
      return;
    }

    resetTrack(track, group);
    fillGroup(group, Math.max(viewport.offsetWidth, 1) * 1.2);
    duplicateGroup(track, group);

    var distance = group.getBoundingClientRect().width;
    var pixelsPerSecond = parseFloat(root.getAttribute('data-marquee-speed') || '40') || 40;
    var duration = Math.max(14, distance / pixelsPerSecond);

    track.style.setProperty('--marquee-distance', distance + 'px');
    track.style.setProperty('--marquee-duration', duration + 's');
    root.classList.toggle('is-static', reduceMotion.matches);
    root.classList.add('is-ready');
  }

  function setupAll() {
    document.querySelectorAll('[data-logo-marquee]').forEach(setup);
  }

  var resizeTimer;
  window.addEventListener('resize', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(setupAll, 180);
  });

  if (reduceMotion.addEventListener) {
    reduceMotion.addEventListener('change', setupAll);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAll);
  } else {
    setupAll();
  }

  window.addEventListener('load', setupAll);
  document.addEventListener('livewire:navigated', setupAll);
  window.setTimeout(setupAll, 400);
})();
