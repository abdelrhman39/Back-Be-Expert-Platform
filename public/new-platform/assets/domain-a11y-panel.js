/**
 * domain — لوحة وصول مخصّصة (محلية، بدون اشتراك)
 * يتطلب: document.documentElement.classList.contains("use-domain-a11y")
 */
(function () {
    if (!document.documentElement.classList.contains("use-domain-a11y")) return;

    var STORAGE_KEY = "domain-a11y:v1";
    var rootId = "domain-a11y-root";

    var STR = {
        ar: {
            fab: "فتح أدوات سهولة الوصول",
            title: "سهولة الوصول",
            close: "إغلاق",
            lang: "اللغة",
            font: "حجم النص",
            fontSm: "أصغر",
            fontLg: "أكبر",
            reset: "إعادة الضبط",
            display: "العرض",
            highContrast: "تباين أعلى",
            reduceMotion: "تقليل الحركة",
            highlightLinks: "تمييز الروابط",
            readableFont: "خط أوضح للقراءة",
        },
        en: {
            fab: "Open accessibility tools",
            title: "Accessibility",
            close: "Close",
            lang: "Language",
            font: "Text size",
            fontSm: "Smaller",
            fontLg: "Larger",
            reset: "Reset all",
            display: "Display",
            highContrast: "Higher contrast",
            reduceMotion: "Reduce motion",
            highlightLinks: "Highlight links",
            readableFont: "Readable system font",
        },
    };

    function loadState() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return defaults();
            var o = JSON.parse(raw);
            return {
                uiLang: o.uiLang === "en" ? "en" : "ar",
                fontScale: clampNum(o.fontScale, 0.85, 1.5, 1),
                highContrast: !!o.highContrast,
                reduceMotion: !!o.reduceMotion,
                highlightLinks: !!o.highlightLinks,
                readableFont: !!o.readableFont,
            };
        } catch (e) {
            return defaults();
        }
    }

    function defaults() {
        return {
            uiLang: document.documentElement.getAttribute("lang") === "en" ? "en" : "ar",
            fontScale: 1,
            highContrast: false,
            reduceMotion: false,
            highlightLinks: false,
            readableFont: false,
        };
    }

    function clampNum(n, min, max, fallback) {
        var x = parseFloat(n);
        if (isNaN(x)) return fallback;
        return Math.min(max, Math.max(min, x));
    }

    function saveState(s) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
        } catch (e) {}
    }

    function t(s, key) {
        return STR[s.uiLang][key] || STR.ar[key] || key;
    }

    function applyToDocument(s) {
        document.documentElement.style.setProperty(
            "--domain-a11y-font-scale",
            String(s.fontScale),
        );
        document.documentElement.classList.toggle("domain-a11y-high-contrast", s.highContrast);
        document.documentElement.classList.toggle("domain-a11y-reduce-motion", s.reduceMotion);
        document.documentElement.classList.toggle("domain-a11y-highlight-links", s.highlightLinks);
        document.documentElement.classList.toggle("domain-a11y-readable-font", s.readableFont);
    }

    function getPanelEls() {
        return {
            fab: document.getElementById("domain-a11y-fab"),
            panel: document.getElementById("domain-a11y-panel"),
            overlay: document.getElementById("domain-a11y-overlay"),
            closeBtn: document.querySelector("#" + rootId + " .domain-a11y-panel__close"),
        };
    }

    function setPanelOpen(wantOpen) {
        var el = getPanelEls();
        if (!el.fab || !el.panel || !el.overlay) return;
        el.fab.setAttribute("aria-expanded", wantOpen ? "true" : "false");
        el.panel.classList.toggle("is-open", wantOpen);
        el.overlay.classList.toggle("is-visible", wantOpen);
        el.panel.hidden = !wantOpen;
        el.overlay.hidden = !wantOpen;
        el.panel.setAttribute("aria-hidden", wantOpen ? "false" : "true");
        if (wantOpen && el.closeBtn) {
            el.closeBtn.focus();
        } else {
            el.fab.focus();
        }
    }

    function isPanelOpen() {
        var p = document.getElementById("domain-a11y-panel");
        return !!(p && p.classList.contains("is-open"));
    }

    function refreshPanelStrings(s) {
        var fab = document.getElementById("domain-a11y-fab");
        if (fab) fab.setAttribute("aria-label", t(s, "fab"));
        var tit = document.getElementById("domain-a11y-title");
        if (tit) tit.textContent = t(s, "title");
        var langLab = document.getElementById("domain-a11y-lang-label");
        if (langLab) langLab.textContent = t(s, "lang");
        var closeBtn = document.querySelector("#" + rootId + " .domain-a11y-panel__close");
        if (closeBtn) closeBtn.setAttribute("aria-label", t(s, "close"));

        var sections = document.querySelectorAll("#" + rootId + " .domain-a11y-section");
        if (sections[0]) {
            var h0 = sections[0].querySelector("h4");
            if (h0) h0.textContent = t(s, "font");
        }
        if (sections[1]) {
            var h1 = sections[1].querySelector("h4");
            if (h1) h1.textContent = t(s, "display");
        }

        var fontRow = document.querySelector(
            "#" + rootId + " .domain-a11y-panel__body > .domain-a11y-section:first-child > .domain-a11y-row",
        );
        var fontBtns = fontRow ? fontRow.querySelectorAll("button") : [];
        if (fontBtns[0]) fontBtns[0].textContent = t(s, "fontSm");
        if (fontBtns[1]) fontBtns[1].textContent = t(s, "fontLg");
        if (fontBtns[2]) fontBtns[2].textContent = t(s, "reset");

        function setToggleLabel(prop, labelKey) {
            var lab = document.querySelector('label[for="domain-a11y-' + prop + '"]');
            if (lab) lab.textContent = t(s, labelKey);
        }
        setToggleLabel("highContrast", "highContrast");
        setToggleLabel("reduceMotion", "reduceMotion");
        setToggleLabel("highlightLinks", "highlightLinks");
        setToggleLabel("readableFont", "readableFont");
    }

    var escapeBound = false;
    function bindEscapeOnce() {
        if (escapeBound) return;
        escapeBound = true;
        document.addEventListener("keydown", function (e) {
            if (e.key !== "Escape" || !isPanelOpen()) return;
            setPanelOpen(false);
        });
    }

    function buildUI(s) {
        var existing = document.getElementById(rootId);
        if (existing) existing.remove();

        var root = document.createElement("div");
        root.id = rootId;

        var fab = document.createElement("button");
        fab.type = "button";
        fab.className = "domain-a11y-fab";
        fab.id = "domain-a11y-fab";
        fab.setAttribute("aria-label", t(s, "fab"));
        fab.setAttribute("aria-expanded", "false");
        fab.setAttribute("aria-controls", "domain-a11y-panel");
        fab.innerHTML =
            '<svg viewBox="0 0 512 512" aria-hidden="true"><path d="M256 112c30.9 0 56-25.1 56-56S286.9 0 256 0s-56 25.1-56 56 25.1 56 56 56z"/><path d="M432 112.8l-.5.1-.4.1c-1 .3-2 .6-3 .9-18.6 5.5-108.9 30.9-172.6 30.9-59.1 0-141.3-22-167.6-29.5-2.6-1-5.3-1.9-8-2.6-19-5-32 14.3-32 31.9 0 17.5 15.7 25.8 31.5 31.8v.3l95.2 29.7c9.7 3.7 12.3 7.5 13.6 10.8 4.1 10.6.8 31.6-.3 38.9l-5.8 45-32.1 176.3c-.1.5-.2 1-.3 1.5l-.2 1.3c-2.3 16.1 9.5 31.8 32 31.8 19.6 0 28.3-13.5 32-31.9 0 0 28-157.6 42-157.6s42.8 157.6 42.8 157.6c3.8 18.4 12.4 31.9 32 31.9 22.5 0 34.4-15.7 32-31.9-.2-1.4-.5-2.7-.8-4.1l-32.5-174.7-5.8-45c-4.2-26.2-.8-34.9.3-36.9 0 0 .1-.1.1-.2 1.1-2 6-6.5 17.5-10.8l89.3-31.2c.5-.1 1.1-.3 1.6-.5 16-6 32-14.3 32-31.9s-13-37-32-32z"/></svg>';

        var overlay = document.createElement("div");
        overlay.className = "domain-a11y-overlay";
        overlay.id = "domain-a11y-overlay";
        overlay.hidden = true;

        var panel = document.createElement("div");
        panel.className = "domain-a11y-panel";
        panel.id = "domain-a11y-panel";
        panel.setAttribute("role", "dialog");
        panel.setAttribute("aria-modal", "true");
        panel.setAttribute("aria-labelledby", "domain-a11y-title");
        panel.hidden = true;
        panel.setAttribute("aria-hidden", "true");

        var header = document.createElement("div");
        header.className = "domain-a11y-panel__header";

        var headText = document.createElement("div");
        var title = document.createElement("h2");
        title.className = "domain-a11y-panel__title";
        title.id = "domain-a11y-title";
        title.textContent = t(s, "title");

        var langRow = document.createElement("div");
        langRow.className = "domain-a11y-panel__lang";
        var langLabel = document.createElement("span");
        langLabel.className = "visually-hidden";
        langLabel.id = "domain-a11y-lang-label";
        langLabel.textContent = t(s, "lang");
        var btnAr = document.createElement("button");
        btnAr.type = "button";
        btnAr.textContent = "عربي";
        btnAr.setAttribute("aria-pressed", s.uiLang === "ar" ? "true" : "false");
        var btnEn = document.createElement("button");
        btnEn.type = "button";
        btnEn.textContent = "EN";
        btnEn.setAttribute("aria-pressed", s.uiLang === "en" ? "true" : "false");
        langRow.appendChild(langLabel);
        langRow.appendChild(btnAr);
        langRow.appendChild(btnEn);

        headText.appendChild(title);
        headText.appendChild(langRow);

        var closeBtn = document.createElement("button");
        closeBtn.type = "button";
        closeBtn.className = "domain-a11y-panel__close";
        closeBtn.setAttribute("aria-label", t(s, "close"));
        closeBtn.innerHTML =
            '<svg width="20" height="20" viewBox="0 0 384 512" aria-hidden="true"><path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>';

        header.appendChild(headText);
        header.appendChild(closeBtn);

        var body = document.createElement("div");
        body.className = "domain-a11y-panel__body";

        function section(titleKey) {
            var sec = document.createElement("div");
            sec.className = "domain-a11y-section";
            var h = document.createElement("h4");
            h.textContent = t(s, titleKey);
            sec.appendChild(h);
            return sec;
        }

        var secFont = section("font");
        var rowFont = document.createElement("div");
        rowFont.className = "domain-a11y-row";
        var bSm = document.createElement("button");
        bSm.type = "button";
        bSm.className = "domain-a11y-btn";
        bSm.textContent = t(s, "fontSm");
        var bLg = document.createElement("button");
        bLg.type = "button";
        bLg.className = "domain-a11y-btn";
        bLg.textContent = t(s, "fontLg");
        var bReset = document.createElement("button");
        bReset.type = "button";
        bReset.className = "domain-a11y-btn domain-a11y-btn--primary";
        bReset.textContent = t(s, "reset");
        rowFont.appendChild(bSm);
        rowFont.appendChild(bLg);
        rowFont.appendChild(bReset);
        secFont.appendChild(rowFont);

        var secDisp = section("display");

        function toggleRow(labelKey, prop) {
            var row = document.createElement("div");
            row.className = "domain-a11y-row";
            var lab = document.createElement("label");
            lab.setAttribute("for", "domain-a11y-" + prop);
            lab.textContent = t(s, labelKey);
            var sw = document.createElement("div");
            sw.className = "domain-a11y-switch";
            var inp = document.createElement("input");
            inp.type = "checkbox";
            inp.id = "domain-a11y-" + prop;
            inp.checked = !!s[prop];
            var span = document.createElement("span");
            sw.appendChild(inp);
            sw.appendChild(span);
            row.appendChild(lab);
            row.appendChild(sw);
            return { row: row, input: inp };
        }

        var hc = toggleRow("highContrast", "highContrast");
        var rm = toggleRow("reduceMotion", "reduceMotion");
        var hl = toggleRow("highlightLinks", "highlightLinks");
        var rf = toggleRow("readableFont", "readableFont");

        secDisp.appendChild(hc.row);
        secDisp.appendChild(rm.row);
        secDisp.appendChild(hl.row);
        secDisp.appendChild(rf.row);

        body.appendChild(secFont);
        body.appendChild(secDisp);

        panel.appendChild(header);
        panel.appendChild(body);

        root.appendChild(overlay);
        root.appendChild(panel);

        var railSlot = document.getElementById("domain-a11y-fab-slot");
        if (railSlot) {
            railSlot.appendChild(fab);
        } else {
            root.appendChild(fab);
        }

        document.body.appendChild(root);

        bindEscapeOnce();

        fab.addEventListener("click", function (e) {
            e.stopPropagation();
            setPanelOpen(!isPanelOpen());
        });

        closeBtn.addEventListener("click", function () {
            setPanelOpen(false);
        });

        overlay.addEventListener("click", function () {
            setPanelOpen(false);
        });

        bSm.addEventListener("click", function () {
            s.fontScale = clampNum(s.fontScale - 0.1, 0.85, 1.5, 1);
            applyToDocument(s);
            saveState(s);
        });

        bLg.addEventListener("click", function () {
            s.fontScale = clampNum(s.fontScale + 0.1, 0.85, 1.5, 1);
            applyToDocument(s);
            saveState(s);
        });

        bReset.addEventListener("click", function () {
            s.fontScale = 1;
            s.highContrast = false;
            s.reduceMotion = false;
            s.highlightLinks = false;
            s.readableFont = false;
            hc.input.checked = false;
            rm.input.checked = false;
            hl.input.checked = false;
            rf.input.checked = false;
            applyToDocument(s);
            saveState(s);
        });

        function bindToggle(inp, prop) {
            inp.addEventListener("change", function () {
                s[prop] = inp.checked;
                applyToDocument(s);
                saveState(s);
            });
        }

        bindToggle(hc.input, "highContrast");
        bindToggle(rm.input, "reduceMotion");
        bindToggle(hl.input, "highlightLinks");
        bindToggle(rf.input, "readableFont");

        function setUiLang(lang) {
            s.uiLang = lang === "en" ? "en" : "ar";
            btnAr.setAttribute("aria-pressed", s.uiLang === "ar" ? "true" : "false");
            btnEn.setAttribute("aria-pressed", s.uiLang === "en" ? "true" : "false");
            saveState(s);
            refreshPanelStrings(s);
        }

        btnAr.addEventListener("click", function () {
            setUiLang("ar");
        });
        btnEn.addEventListener("click", function () {
            setUiLang("en");
        });
    }

    var state = loadState();
    applyToDocument(state);

    function mount() {
        buildUI(state);
        window.domainA11y = { state: state };
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", mount);
    } else {
        mount();
    }
})();
