(function () {
    'use strict';

    var root = document.getElementById('ai-support-root');
    if (!root) return;

    var config = {
        locale: root.dataset.locale || 'ar',
        bootstrapUrl: root.dataset.bootstrapUrl,
        chatUrl: root.dataset.chatUrl,
        feedbackUrl: root.dataset.feedbackUrl,
        storageKey: 'beexpert.ai.support.conversation.' + (root.dataset.locale || 'ar'),
    };

    var els = {
        fab: root.querySelector('[data-ai-fab]'),
        panel: root.querySelector('[data-ai-panel]'),
        close: root.querySelector('[data-ai-close]'),
        body: root.querySelector('[data-ai-body]'),
        form: root.querySelector('[data-ai-form]'),
        input: root.querySelector('[data-ai-input]'),
        send: root.querySelector('[data-ai-send]'),
        typing: root.querySelector('[data-ai-typing]'),
        title: root.querySelector('[data-ai-title]'),
        subtitle: root.querySelector('[data-ai-subtitle]'),
        links: root.querySelector('[data-ai-links]'),
        badge: root.querySelector('[data-ai-badge]'),
        suggestions: root.querySelector('[data-ai-suggestions]'),
    };

    var state = {
        open: false,
        busy: false,
        conversationUuid: localStorage.getItem(config.storageKey) || null,
        links: {},
        bootstrapped: false,
    };

    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function toggle(force) {
        state.open = typeof force === 'boolean' ? force : !state.open;
        els.panel.classList.toggle('is-open', state.open);
        els.fab.classList.toggle('is-open', state.open);
        els.fab.setAttribute('aria-expanded', state.open ? 'true' : 'false');
        if (state.open) {
            if (els.badge) els.badge.classList.remove('is-visible');
            if (!state.bootstrapped) bootstrap();
            setTimeout(function () { els.input && els.input.focus(); }, 50);
        }
    }

    function appendMessage(role, text, opts) {
        opts = opts || {};
        var div = document.createElement('div');
        div.className = 'ai-chat-msg ai-chat-msg--' + (role === 'user' ? 'user' : 'bot');
        div.textContent = text;

        if (role === 'assistant' && opts.messageId) {
            var actions = document.createElement('div');
            actions.className = 'ai-chat-msg__actions';
            actions.innerHTML =
                '<button type="button" data-fb="1" aria-label="مفيد">مفيد</button>' +
                '<button type="button" data-fb="-1" aria-label="غير مفيد">غير مفيد</button>';
            actions.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-fb]');
                if (!btn) return;
                actions.querySelectorAll('button').forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');
                sendFeedback(opts.messageId, parseInt(btn.getAttribute('data-fb'), 10));
            });
            div.appendChild(actions);
        }

        if (opts.needsHuman && state.links.ticket_new) {
            var esc = document.createElement('div');
            esc.className = 'ai-chat-escalate';
            esc.innerHTML = (config.locale === 'en'
                ? 'Need a human? <a href="' + state.links.ticket_new + '">Open a support ticket</a>'
                : 'تحتاج مساعدة بشرية؟ <a href="' + state.links.ticket_new + '">افتح تذكرة دعم</a>');
            div.appendChild(esc);
        }

        els.body.appendChild(div);
        els.body.scrollTop = els.body.scrollHeight;
        return div;
    }

    function setTyping(on) {
        els.typing.classList.toggle('is-visible', !!on);
        if (on) els.body.scrollTop = els.body.scrollHeight;
    }

    function setBusy(on) {
        state.busy = on;
        if (els.send) els.send.disabled = on;
        if (els.input) els.input.disabled = on;
    }

    function renderSuggestions(items) {
        if (!els.suggestions) return;
        els.suggestions.innerHTML = '';
        (items || []).forEach(function (label) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            b.addEventListener('click', function () {
                els.input.value = label;
                sendMessage();
            });
            els.suggestions.appendChild(b);
        });
    }

    function renderLinks(links) {
        state.links = links || {};
        if (!els.links) return;
        var map = [
            ['faq', config.locale === 'en' ? 'FAQ' : 'الأسئلة الشائعة'],
            ['ticket_new', config.locale === 'en' ? 'New ticket' : 'تذكرة جديدة'],
            ['ticket_search', config.locale === 'en' ? 'Track ticket' : 'متابعة تذكرة'],
            ['contact', config.locale === 'en' ? 'Contact' : 'تواصل'],
        ];
        els.links.innerHTML = map
            .filter(function (row) { return links[row[0]]; })
            .map(function (row) {
                return '<a href="' + links[row[0]] + '">' + row[1] + '</a>';
            })
            .join('');
    }

    function bootstrap() {
        state.bootstrapped = true;
        fetch(config.bootstrapUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.enabled) {
                    appendMessage('assistant', config.locale === 'en'
                        ? 'The AI assistant is currently unavailable. Please use support tickets or WhatsApp.'
                        : 'المساعد الذكي غير مفعّل حالياً. يرجى استخدام تذاكر الدعم أو واتساب.');
                    setBusy(true);
                    return;
                }
                if (els.title && data.assistant_name) els.title.textContent = data.assistant_name;
                if (els.subtitle) {
                    els.subtitle.textContent = config.locale === 'en' ? 'Online support assistant' : 'مساعد دعم فوري للمنصة';
                }
                appendMessage('assistant', data.welcome || '');
                renderSuggestions(data.suggestions || []);
                renderLinks(data.links || {});
            })
            .catch(function () {
                appendMessage('assistant', config.locale === 'en'
                    ? 'Could not initialize the assistant.'
                    : 'تعذر تهيئة المساعد حالياً.');
            });
    }

    function sendFeedback(messageId, value) {
        fetch(config.feedbackUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                message_id: messageId,
                feedback: value,
                approve_training: value === 1,
            }),
        }).catch(function () {});
    }

    function sendMessage() {
        if (state.busy) return;
        var text = (els.input.value || '').trim();
        if (text.length < 2) return;

        els.input.value = '';
        if (els.suggestions) els.suggestions.innerHTML = '';
        appendMessage('user', text);
        setBusy(true);
        setTyping(true);

        fetch(config.chatUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                message: text,
                conversation_uuid: state.conversationUuid,
                page_url: window.location.href,
            }),
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; }); })
            .then(function (res) {
                setTyping(false);
                setBusy(false);
                var body = res.body || {};
                if (body.conversation_uuid) {
                    state.conversationUuid = body.conversation_uuid;
                    localStorage.setItem(config.storageKey, body.conversation_uuid);
                }
                appendMessage('assistant', body.reply || (config.locale === 'en' ? 'No response.' : 'لا توجد استجابة.'), {
                    messageId: body.message_id,
                    needsHuman: !!body.needs_human || !body.ok,
                });
            })
            .catch(function () {
                setTyping(false);
                setBusy(false);
                appendMessage('assistant', config.locale === 'en'
                    ? 'Connection error. Please try again or open a support ticket.'
                    : 'خطأ في الاتصال. حاول مجدداً أو افتح تذكرة دعم.', {
                    needsHuman: true,
                });
            });
    }

    if (els.fab) els.fab.addEventListener('click', function () { toggle(); });
    if (els.close) els.close.addEventListener('click', function () { toggle(false); });
    if (els.form) {
        els.form.addEventListener('submit', function (e) {
            e.preventDefault();
            sendMessage();
        });
    }
    if (els.input) {
        els.input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && state.open) toggle(false);
    });
})();
