/**
 * تخصيص نصوص قائمة منير (عربي / إنجليزي)
 *
 * عدّل الكائنات window.BEXPERT_MUNEER_LOCALE_OVERRIDES.ar و .en
 * المفتاح يطابق data-muneer-t في index.html (وملفات الصفحات الأخرى).
 *
 * إذا تركت مفتاحاً غير معرّف هنا، يبقى النص الذي يضعه منير أو النص الافتراضي في HTML.
 */
(function () {
    window.BEXPERT_MUNEER_LOCALE_OVERRIDES = window.BEXPERT_MUNEER_LOCALE_OVERRIDES || {
        ar: {
            /* مثال:
            "popup-title-whitelabel": "أدوات الوصول — مركز التعلم المستمر",
            */
        },
        en: {
            "popup-title-whitelabel": "Accessibility tools",
            oversize: "Enlarge toolbar buttons",
            "section-accessibility-modes": "Assistance modes",
            "mode-epilepsy-title": "Seizure-safe mode",
            "mode-epilepsy-short": "Reduce motion & flashing",
            "mode-epilepsy-description":
                "Lowers color intensity and removes flashing effects for users with photosensitive epilepsy.",
            "mode-visually-impaired-title": "Visually impaired",
            "mode-visually-impaired-short": "Improve contrast & readability",
            "mode-visually-impaired-description":
                "Enhances contrast and readability for users with low vision.",
            "mode-cognitive-disability-title": "Cognitive disability",
            "mode-cognitive-disability-short": "Simpler layout & focus",
            "mode-cognitive-disability-description":
                "Helps users focus on essential content and reduces distractions.",
            "mode-motor-impaired-title": "Motor impairment",
            "mode-motor-impaired-short": "Easier navigation",
            "mode-motor-impaired-description":
                "Improves navigation for users who rely on keyboard or assistive pointers.",
            "mode-colorblind-title": "Color-blind mode",
            "mode-colorblind-short": "Adjusted palette",
            "mode-colorblind-description":
                "Adjusts colors to improve distinction for common types of color blindness.",
            "mode-dyslexia-friendly-title": "Dyslexia-friendly",
            "mode-dyslexia-friendly-short": "Dedicated fonts",
            "mode-dyslexia-friendly-description":
                "Uses dyslexia-friendly typography and spacing where supported.",
            "mode-adhd-friendly-title": "ADHD-friendly",
            "mode-adhd-friendly-short": "Reduce distraction",
            "mode-adhd-friendly-description":
                "Reduces clutter and motion to support sustained attention.",
            "mode-blind-users-title": "Blind users",
            "mode-blind-users-short": "Screen-reader oriented",
            "mode-blind-users-description":
                "Optimizes structure and semantics for screen reader use.",
            "section-readable-experience": "Comfortable reading",
            "content-scaling": "Content size",
            "text-magnifier": "Text magnifier",
            "readable-font": "Readable font",
            "dyslexia-font": "Dyslexia font",
            "highlight-titles": "Highlight headings",
            "highlight-links": "Highlight links",
            "font-sizing": "Font size",
            "text-alignment": "Text alignment",
            "line-height": "Line spacing",
            "letter-spacing": "Letter spacing",
            "word-spacing": "Word spacing",
            "voice-navigation": "Voice commands",
            "text-to-speech": "Text to speech",
            "section-visually-pleasing-experience": "Visual comfort",
            "smart-contrast": "Smart contrast",
            monochrome: "Monochrome",
            "high-contrast": "High contrast",
            "high-saturation": "High saturation",
            "low-saturation": "Low saturation",
            "colors-settings": "Color settings",
            "text-colors": "Text colors",
            "title-colors": "Heading colors",
            "background-colors": "Background colors",
            "section-easy-orientation": "Navigation & orientation",
            "mute-sounds": "Mute sounds",
            "hide-images": "Hide images",
            "image-alt": "Image hints",
            "virtual-keyboard": "Virtual keyboard",
            "reading-guide": "Reading guide",
            "reading-mask": "Reading mask",
            "stop-animations": "Stop animations",
            "highlight-hover": "Highlight hover",
            "highlight-focus": "Highlight focus",
            "big-black-cursor": "Large dark cursor",
            "big-white-cursor": "Large light cursor",
            "keyboard-navigation": "Keyboard shortcuts",
            "google-translate": "Translate this page",
            "online-dictionary": "Online dictionary",
            "search-placeholder-wikipedia": "Search the online dictionary…",
            "useful-links": "Link explorer",
            "reset-button": "Reset settings",
            "hide-button": "Hide Muneer widget",
            "accessibility-statement": "Muneer accessibility statement",
        },
    };

    function detectLang() {
        var sel = document.querySelector("#muneer-language-switcher--select");
        if (sel && sel.value) {
            return sel.value === "en" ? "en" : "ar";
        }
        var htmlLang = (document.documentElement.getAttribute("lang") || "ar").toLowerCase();
        return htmlLang.indexOf("en") === 0 ? "en" : "ar";
    }

    function applyMuneerLocaleOverrides() {
        var packs = window.BEXPERT_MUNEER_LOCALE_OVERRIDES;
        if (!packs) return;
        var lang = detectLang();
        var map = packs[lang];
        if (!map || typeof map !== "object") return;
        var sidebar = document.getElementById("muneer-sidebar");
        if (!sidebar) return;

        sidebar.querySelectorAll("[data-muneer-t]").forEach(function (el) {
            var key = el.getAttribute("data-muneer-t");
            if (!key || map[key] === undefined || map[key] === null || map[key] === "") return;
            var text = String(map[key]);
            if (el.tagName === "INPUT" && el.hasAttribute("placeholder")) {
                el.setAttribute("placeholder", text);
                return;
            }
            if (el.childElementCount === 0) {
                if (el.textContent.trim() !== text) el.textContent = text;
                return;
            }
            if (el.tagName === "SPAN" || el.tagName === "LABEL" || el.tagName === "OPTION") {
                if (el.textContent.trim() !== text) el.textContent = text;
                return;
            }
            if (el.tagName === "A" || el.tagName === "H3" || el.tagName === "H4") {
                if (el.textContent.trim() !== text) el.textContent = text;
            }
        });
    }

    function scheduleApply() {
        window.requestAnimationFrame(function () {
            window.setTimeout(applyMuneerLocaleOverrides, 0);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        var delays = [120, 450, 1200, 2500];
        delays.forEach(function (ms) {
            window.setTimeout(applyMuneerLocaleOverrides, ms);
        });

        var sidebar = document.getElementById("muneer-sidebar");
        if (sidebar && typeof MutationObserver !== "undefined") {
            var t;
            var mo = new MutationObserver(function () {
                window.clearTimeout(t);
                t = window.setTimeout(applyMuneerLocaleOverrides, 160);
            });
            mo.observe(sidebar, {
                attributes: true,
                attributeFilter: ["aria-hidden"],
            });
        }

        var sel = document.querySelector("#muneer-language-switcher--select");
        if (sel) {
            sel.addEventListener("change", scheduleApply);
        }
        window.addEventListener("load", function () {
            applyMuneerLocaleOverrides();
        });
    });
})();
