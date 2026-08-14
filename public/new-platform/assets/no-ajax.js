/**
 * Static demo: block jQuery AJAX, fetch, and XHR network calls.
 */
(function () {
    function noopDeferred() {
        var o = {
            done: function () { return o; },
            fail: function () { return o; },
            always: function () { return o; },
            then: function () { return o; },
            catch: function () { return o; },
            abort: function () { return o; },
        };
        return o;
    }

    if (window.fetch) {
        window.fetch = function () {
            return Promise.resolve({
                ok: false,
                status: 0,
                json: function () { return Promise.resolve({}); },
                text: function () { return Promise.resolve(""); },
            });
        };
    }

    var XHR = window.XMLHttpRequest;
    if (XHR && XHR.prototype) {
        XHR.prototype.open = function () {};
        XHR.prototype.send = function () {};
    }

    function patchJq($) {
        if (!$ || $.ajax && $.__noAjaxPatched) return;
        $.ajax = function () { return noopDeferred(); };
        $.post = function () { return noopDeferred(); };
        $.get = function () { return noopDeferred(); };
        $.getJSON = function () { return noopDeferred(); };
        $.ajaxSetup = function () { return $; };
        $.__noAjaxPatched = true;
    }

    if (window.jQuery) patchJq(window.jQuery);
    if (window.$) patchJq(window.$);

    var jqCheck = setInterval(function () {
        if (window.jQuery) {
            patchJq(window.jQuery);
            clearInterval(jqCheck);
        }
    }, 10);
    setTimeout(function () { clearInterval(jqCheck); }, 10000);
})();
