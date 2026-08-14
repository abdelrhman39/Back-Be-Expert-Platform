/**
 * تبديل لغة القائمة (عربي ↔ إنجليزي) من مكان واحد.
 *
 * التعديل هنا فقط:
 * - mode: 'auto' يختار المحلي على localhost/file، وإلا الإنتاج.
 * - arFileToEnFile / enFileToArFile: عندما لا يوجد نفس اسم الملف في النسخة الأخرى.
 *
 * يُحدَّث تلقائياً كل رابط: a.lang-btn[hreflang="en"] و a.lang-btn[hreflang="ar"]
 */
(function () {
    var CFG = {
        mode: "auto",
        production: {
            origin: "./",
            enPrefix: "/en",
            arPrefix: "/ar",
        },
        local: {
            enFolder: "../en-version",
            arFolder: ".",
        },
        /** صفحة عربية في جذر New-Platform → ملف HTML داخل mirror/en */
        arFileToEnFile: {
            "index.html": "index.html",
            "courses.html": "courses.html",
            "profile.html": "profile.html",
        },
        /** صفحة إنجليزية في mirror/en → ملف عربي في جذر المنصة */
        enFileToArFile: {
            "index.html": "index.html",
            "profile.html": "profile.html",
        },
    };

    function useLocalMirror() {
        if (CFG.mode === "local") return true;
        if (CFG.mode === "production") return false;
        var h = window.location.hostname;
        return !h || h === "localhost" || h === "127.0.0.1";
    }

    function pathFilename(path) {
        var parts = path.replace(/\\/g, "/").split("/").filter(Boolean);
        return parts.length ? parts[parts.length - 1] : "index.html";
    }

    function upsLevels(n) {
        var s = "";
        for (var i = 0; i < n; i++) s += "../";
        return s;
    }

    /** بعد mirror/en/ أو mirror/ar/ */
    function tailAfterMirror(path, which) {
        var re = which === "en" ? /\/mirror\/en\/(.*)$/i : /\/mirror\/ar\/(.*)$/i;
        var m = path.replace(/\\/g, "/").match(re);
        return m ? m[1] : "";
    }

    function localHrefToEn(path, file) {
        var p = path.replace(/\\/g, "/");
        var enFolder = CFG.local.enFolder;
        if (/\/mirror\/en\//i.test(p)) {
            return "#";
        }
        if (/\/mirror\/ar\//i.test(p)) {
            var tailAr = tailAfterMirror(p, "ar");
            var depthAr = tailAr.split("/").filter(Boolean).length;
            return upsLevels(depthAr + 1) + enFolder + "/" + tailAr;
        }
        var mapped = CFG.arFileToEnFile[file] || CFG.arFileToEnFile[file.replace(/\.html$/i, "")];
        var enFile = mapped != null ? mapped : file;
        return "./" + enFolder + "/" + enFile;
    }

    function localHrefToAr(path, file) {
        var p = path.replace(/\\/g, "/");
        if (/\/mirror\/ar\//i.test(p)) {
            return "#";
        }
        if (/\/mirror\/en\//i.test(p)) {
            var tail = tailAfterMirror(p, "en");
            var depth = tail.split("/").filter(Boolean).length;
            var ups = upsLevels(depth + 1);
            var leaf = tail.split("/").pop() || file;
            var mapped = CFG.enFileToArFile[leaf];
            if (mapped) return ups + mapped;
            return ups + CFG.local.arFolder + "/" + tail;
        }
        return "#";
    }

    function slugFromTail(tail) {
        return tail.replace(/\.html$/i, "").replace(/\/index$/i, "");
    }

    function productionHrefToEn(path, file) {
        var o = CFG.production.origin;
        var pref = CFG.production.enPrefix || "/en";
        var p = path.replace(/\\/g, "/");
        if (/\/mirror\/en\//i.test(p)) {
            var tail = tailAfterMirror(p, "en");
            var slug = slugFromTail(tail);
            return slug ? o + pref + "/" + slug : o + pref;
        }
        if (/\/mirror\/ar\//i.test(p)) {
            var tailAr = tailAfterMirror(p, "ar");
            var slugAr = slugFromTail(tailAr);
            return slugAr ? o + pref + "/" + slugAr : o + pref;
        }
        /** صفحات عربية في الجذر: الرابط النظيف /en و/en/courses … يطابق الإنتاج */
        if (file === "index.html") return o + pref;
        return o + pref + "/" + file.replace(/\.html$/i, "");
    }

    function productionHrefToAr(path, file) {
        var o = CFG.production.origin;
        var pref = CFG.production.arPrefix || "/ar";
        var p = path.replace(/\\/g, "/");
        if (/\/mirror\/en\//i.test(p)) {
            var tail = tailAfterMirror(p, "en");
            var slug = slugFromTail(tail);
            return slug ? o + pref + "/" + slug : o + pref;
        }
        if (/\/mirror\/ar\//i.test(p)) {
            var tailAr = tailAfterMirror(p, "ar");
            var slugAr = slugFromTail(tailAr);
            return slugAr ? o + pref + "/" + slugAr : o + pref;
        }
        var leaf = file.replace(/\.html$/i, "");
        if (leaf === "index") return o + pref;
        return o + pref + "/" + leaf;
    }

    function apply() {
        var path = window.location.pathname;
        var file = pathFilename(path);
        var local = useLocalMirror();

        document.querySelectorAll('a.lang-btn[hreflang="en"]').forEach(function (a) {
            var href = local ? localHrefToEn(path, file) : productionHrefToEn(path, file);
            if (href && href !== "#") a.setAttribute("href", href);
        });

        document.querySelectorAll('a.lang-btn[hreflang="ar"]').forEach(function (a) {
            var href = local ? localHrefToAr(path, file) : productionHrefToAr(path, file);
            if (href && href !== "#") a.setAttribute("href", href);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", apply);
    } else {
        apply();
    }

    window.BExpertLangNav = CFG;
})();
