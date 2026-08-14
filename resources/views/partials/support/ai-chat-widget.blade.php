@php
    $locale = app()->getLocale();
    $aiEnabled = \App\Support\OpenAiSettings::supportEnabled();
    $assistantName = \App\Support\OpenAiSettings::assistantName($locale);
    $mountClass = $mountClass ?? '';
@endphp

@if ($aiEnabled)
<div
    id="ai-support-root"
    class="{{ $mountClass }}"
    data-locale="{{ $locale }}"
    data-bootstrap-url="{{ route('support.chat.bootstrap', ['locale' => $locale]) }}"
    data-chat-url="{{ route('support.chat', ['locale' => $locale]) }}"
    data-feedback-url="{{ route('support.chat.feedback', ['locale' => $locale]) }}"
>
    <button
        type="button"
        class="ai-chat-fab"
        data-ai-fab
        aria-expanded="false"
        aria-controls="ai-support-panel"
        aria-label="{{ $locale === 'ar' ? 'فتح مساعد الدعم الذكي' : 'Open AI support assistant' }}"
        title="{{ $assistantName }}"
    >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 3a7 7 0 0 0-7 7v1.2c0 .9-.3 1.7-.9 2.4A3.2 3.2 0 0 0 7.2 19h9.6a3.2 3.2 0 0 0 3.1-5.4 3.8 3.8 0 0 1-.9-2.4V10a7 7 0 0 0-7-7Z" stroke="#fff" stroke-width="1.6"/>
            <circle cx="9.2" cy="11" r="1" fill="#fff"/>
            <circle cx="14.8" cy="11" r="1" fill="#fff"/>
            <path d="M9 14.2c.8.7 1.8 1.1 3 1.1s2.2-.4 3-1.1" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <span class="ai-chat-fab__badge" data-ai-badge aria-hidden="true"></span>
    </button>

    <section
        id="ai-support-panel"
        class="ai-chat-panel"
        data-ai-panel
        role="dialog"
        aria-modal="false"
        aria-label="{{ $assistantName }}"
    >
        <header class="ai-chat-panel__header">
            <div class="ai-chat-panel__avatar" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3a7 7 0 0 0-7 7v1.2c0 .9-.3 1.7-.9 2.4A3.2 3.2 0 0 0 7.2 19h9.6a3.2 3.2 0 0 0 3.1-5.4 3.8 3.8 0 0 1-.9-2.4V10a7 7 0 0 0-7-7Z" stroke="#fff" stroke-width="1.6"/>
                </svg>
            </div>
            <div class="ai-chat-panel__meta">
                <h2 class="ai-chat-panel__title" data-ai-title>{{ $assistantName }}</h2>
                <p class="ai-chat-panel__subtitle" data-ai-subtitle>
                    {{ $locale === 'ar' ? 'مساعد دعم فوري للمنصة' : 'Online support assistant' }}
                </p>
            </div>
            <button type="button" class="ai-chat-panel__close" data-ai-close aria-label="{{ $locale === 'ar' ? 'إغلاق' : 'Close' }}">×</button>
        </header>

        <div class="ai-chat-panel__body" data-ai-body>
            <div class="ai-chat-suggestions" data-ai-suggestions></div>
            <div class="ai-chat-typing" data-ai-typing aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
        </div>

        <footer class="ai-chat-panel__footer">
            <div class="ai-chat-panel__links" data-ai-links></div>
            <form class="ai-chat-panel__form" data-ai-form>
                <textarea
                    data-ai-input
                    rows="1"
                    maxlength="4000"
                    placeholder="{{ $locale === 'ar' ? 'اكتب سؤالك عن المنصة...' : 'Ask about the platform...' }}"
                    aria-label="{{ $locale === 'ar' ? 'رسالة المساعد' : 'Assistant message' }}"
                ></textarea>
                <button type="submit" data-ai-send aria-label="{{ $locale === 'ar' ? 'إرسال' : 'Send' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 12h14M13 6l6 6-6 6" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
            <p class="ai-chat-panel__disclaimer">
                {{ $locale === 'ar'
                    ? 'الإجابات مبنية على معلومات منصة كن خبيراً. للمسائل الحساسة افتح تذكرة دعم.'
                    : 'Answers are grounded in Be Expert platform knowledge. For sensitive issues, open a support ticket.' }}
            </p>
        </footer>
    </section>
</div>

@once
    <link rel="stylesheet" href="{{ asset('css/ai-support-chat.css') }}?v=1">
    <script src="{{ asset('js/ai-support-chat.js') }}?v=1" defer></script>
@endonce
@endif
