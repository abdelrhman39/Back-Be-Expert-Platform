/**
 * بوابة: تبويب تسجيل الدخول، إخفاء كلمة المرور، بانر الكوكيز
 */
(function () {
  "use strict";

  var CONSENT_KEY = "domainPortalCookieConsent";

  function initCookieBanner() {
    var banner = document.getElementById("portal-cookie");
    if (!banner) return;
    try {
      if (localStorage.getItem(CONSENT_KEY) !== null) {
        banner.hidden = true;
      }
    } catch (e) {
      /* ignore */
    }

    var accept = document.getElementById("portal-cookie-accept");
    var reject = document.getElementById("portal-cookie-reject");
    if (accept) {
      accept.addEventListener("click", function () {
        try {
          localStorage.setItem(CONSENT_KEY, "accept");
        } catch (err) {}
        banner.hidden = true;
      });
    }
    if (reject) {
      reject.addEventListener("click", function () {
        try {
          localStorage.setItem(CONSENT_KEY, "reject");
        } catch (err) {}
        banner.hidden = true;
      });
    }
  }

  function initLoginTabs() {
    var tabs = document.querySelectorAll("[data-portal-tab]");
    if (!tabs.length) return;
    tabs.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var name = btn.getAttribute("data-portal-tab");
        if (!name) return;
        tabs.forEach(function (b) {
          b.classList.toggle("is-active", b === btn);
          b.setAttribute("aria-selected", b === btn ? "true" : "false");
        });
        document.querySelectorAll(".portal-panel").forEach(function (panel) {
          var match = panel.getAttribute("data-portal-panel") === name;
          panel.hidden = !match;
          if (match) {
            var first = panel.querySelector("input, select, textarea, button");
            if (first) first.focus({ preventScroll: true });
          }
        });
      });
    });
  }

  function initPasswordToggle() {
    document.querySelectorAll("[data-password-toggle]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-password-toggle");
        var input = id && document.getElementById(id);
        if (!input) return;
        var show = input.type === "password";
        input.type = show ? "text" : "password";
        btn.setAttribute("aria-pressed", show ? "true" : "false");
        var label = show ? "إخفاء" : "إظهار";
        btn.setAttribute("aria-label", label + " كلمة المرور");
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      initCookieBanner();
      initLoginTabs();
      initPasswordToggle();
    });
  } else {
    initCookieBanner();
    initLoginTabs();
    initPasswordToggle();
  }
})();
