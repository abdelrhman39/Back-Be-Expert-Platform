@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $maxLength = (int) ($data['message_max_length'] ?? 150);
    $anchorId = $data['form_anchor_id'] ?? 'contact-us-Form';
    $complainRoute = $data['complain_redirect_route'] ?? 'support.ticket.new';
    $showMap = (bool) ($data['show_map'] ?? true);
    $formEyebrow = $data['form_eyebrow'] ?? ($isEn ? 'Get in touch' : 'راسلنا مباشرة');
    $privacyNote = $data['privacy_note'] ?? ($isEn
        ? 'We are committed to protecting your privacy and will only use your details to respond to this request.'
        : 'نلتزم بحماية خصوصيتكم، ولن نستخدم بياناتكم إلا للرد على طلبكم.');

    try {
        $ticketUrl = route($complainRoute, ['locale' => $locale]);
    } catch (\Throwable) {
        $ticketUrl = '#';
    }
@endphp

<section class="contact-stage{{ $showMap ? '' : ' contact-stage--form-only' }}">
    @if ($showMap)
        <div class="contact-stage__map contact-map">
            @if (filled($data['map_embed_url'] ?? null))
                <iframe
                    src="{{ $data['map_embed_url'] }}"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="{{ $data['map_iframe_title'] ?? '' }}"
                ></iframe>
            @endif
        </div>
    @endif

    <div class="container contact-stage__wrap">
        <div class="contact-panel" id="{{ $anchorId }}">
            <div class="contact-panel__head">
                @if (filled($formEyebrow))
                    <span class="contact-eyebrow contact-eyebrow--center">{{ $formEyebrow }}</span>
                @endif
                @if (filled($data['form_title'] ?? null))
                    <h3 class="contact-panel__title">{{ $data['form_title'] }}</h3>
                @endif
            </div>

            <form
                action="#"
                id="contact-form"
                class="cms-contact-form contact-form-grid"
                method="post"
                novalidate
                data-support-email="{{ $data['support_email'] ?? '' }}"
                data-ticket-url="{{ $ticketUrl }}"
                data-complain-value="{{ $data['complain_reason_value'] ?? 'complain' }}"
                data-max-length="{{ $maxLength }}"
            >
                @csrf
                <div class="contact-field">
                    <label class="form-label" for="contact-name">
                        {{ $data['field_name_label'] ?? ($isEn ? 'Name' : 'الاسم') }}<span class="text-danger">*</span>
                    </label>
                    <div class="contact-input">
                        <span class="contact-input__icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span>
                        <input
                            class="form-control"
                            name="name"
                            id="contact-name"
                            type="text"
                            autocomplete="name"
                            placeholder="{{ $isEn ? 'Full name' : 'الاسم الكامل' }}"
                            required
                        >
                    </div>
                </div>
                <div class="contact-field">
                    <label class="form-label" for="contact-email">
                        {{ $data['field_email_label'] ?? ($isEn ? 'Email' : 'البريد الإلكتروني') }}<span class="text-danger">*</span>
                    </label>
                    <div class="contact-input">
                        <span class="contact-input__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <input
                            class="form-control"
                            name="email"
                            id="contact-email"
                            type="email"
                            autocomplete="email"
                            dir="ltr"
                            placeholder="name@example.com"
                            required
                        >
                    </div>
                </div>
                <div class="contact-field">
                    <label class="form-label" for="contact-phone">
                        {{ $data['field_phone_label'] ?? ($isEn ? 'Phone' : 'رقم الجوال') }}<span class="text-danger">*</span>
                    </label>
                    <div class="contact-input">
                        <span class="contact-input__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                        <input
                            class="form-control"
                            name="phone"
                            id="contact-phone"
                            type="tel"
                            autocomplete="tel"
                            dir="ltr"
                            inputmode="tel"
                            placeholder="{{ $data['field_phone_placeholder'] ?? '+9665XXXXXXXX' }}"
                            required
                        >
                    </div>
                </div>
                <div class="contact-field">
                    <label class="form-label" for="contact-reason">
                        {{ $data['field_reason_label'] ?? ($isEn ? 'Reason' : 'سبب التواصل') }}<span class="text-danger">*</span>
                    </label>
                    <div class="contact-input contact-input--select">
                        <span class="contact-input__icon" aria-hidden="true"><i class="fa-solid fa-list"></i></span>
                        <select class="form-control" name="reason_for_connect" id="contact-reason" required>
                            @foreach ($data['reasons'] ?? [] as $reason)
                                <option value="{{ $reason['value'] ?? '' }}">{{ $reason['label'] ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="contact-field contact-field--full">
                    <label class="form-label" for="contact-message">
                        {{ $data['field_message_label'] ?? ($isEn ? 'Message' : 'رسالة') }}<span class="text-danger">*</span>
                        @if (filled($data['field_message_hint'] ?? null))
                            <span class="alert-span">{{ $data['field_message_hint'] }}</span>
                        @endif
                    </label>
                    <div class="contact-input contact-input--area">
                        <span class="contact-input__icon" aria-hidden="true"><i class="fa-regular fa-comment-dots"></i></span>
                        <textarea
                            name="message"
                            class="form-control"
                            id="contact-message"
                            cols="30"
                            rows="4"
                            maxlength="{{ $maxLength }}"
                            placeholder="{{ $isEn ? 'Write your message here' : 'اكتب رسالتك هنا' }}"
                            required
                        ></textarea>
                    </div>
                    <div class="char-counter">
                        <span id="char-count">0</span>/{{ $maxLength }} {{ $isEn ? 'chars' : 'حرف' }}
                    </div>
                </div>
                <div class="contact-form-footer">
                    <p class="contact-form-privacy">{{ $privacyNote }}</p>
                    <button type="submit" class="contact-submit">
                        <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>
                        <span>{{ $data['submit_label'] ?? ($isEn ? 'Send' : 'إرسال') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
