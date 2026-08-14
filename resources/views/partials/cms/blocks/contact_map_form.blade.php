@php
    use App\Support\CmsBlockLink;

    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $maxLength = (int) ($data['message_max_length'] ?? 150);
    $anchorId = $data['form_anchor_id'] ?? 'contact-us-Form';
    $complainRoute = $data['complain_redirect_route'] ?? 'support.ticket.new';

    try {
        $ticketUrl = route($complainRoute, ['locale' => $locale]);
    } catch (\Throwable) {
        $ticketUrl = '#';
    }
@endphp

<section class="contact-section">
    <div class="contact-top">
        <div class="container">
            <div class="row g-4">
                @if ($data['show_map'] ?? true)
                    <div class="col-lg-6 col-md-12 d-flex">
                        <div class="contact-map w-100">
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
                    </div>
                @endif

                <div @class(['col-lg-6 col-md-12 d-flex', 'col-lg-12' => ! ($data['show_map'] ?? true)])>
                    <div class="team-form w-100" id="{{ $anchorId }}">
                        @if (filled($data['form_title'] ?? null))
                            <div class="team-form-heading">
                                <h3>{{ $data['form_title'] }}</h3>
                            </div>
                        @endif
                        <form
                            action="#"
                            id="contact-form"
                            class="cms-contact-form"
                            method="post"
                            novalidate
                            data-support-email="{{ $data['support_email'] ?? '' }}"
                            data-ticket-url="{{ $ticketUrl }}"
                            data-complain-value="{{ $data['complain_reason_value'] ?? 'complain' }}"
                            data-max-length="{{ $maxLength }}"
                        >
                            @csrf
                            <div class="form-group">
                                <label class="form-label" for="contact-name">
                                    {{ $data['field_name_label'] ?? 'الاسم' }}<span class="text-danger">*</span>
                                </label>
                                <input class="form-control" name="name" id="contact-name" type="text" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-email">
                                    {{ $data['field_email_label'] ?? 'البريد الإلكتروني' }}<span class="text-danger">*</span>
                                </label>
                                <input class="form-control" name="email" id="contact-email" type="email" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-phone">
                                    {{ $data['field_phone_label'] ?? 'رقم الجوال' }}<span class="text-danger">*</span>
                                </label>
                                <input
                                    class="form-control"
                                    name="phone"
                                    id="contact-phone"
                                    type="tel"
                                    dir="ltr"
                                    placeholder="{{ $data['field_phone_placeholder'] ?? '+9665XXXXXXXX' }}"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-reason">
                                    {{ $data['field_reason_label'] ?? 'سبب التواصل' }}<span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="reason_for_connect" id="contact-reason" required>
                                    @foreach ($data['reasons'] ?? [] as $reason)
                                        <option value="{{ $reason['value'] ?? '' }}">{{ $reason['label'] ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-message">
                                    {{ $data['field_message_label'] ?? 'رسالة' }}<span class="text-danger">*</span>
                                    @if (filled($data['field_message_hint'] ?? null))
                                        <span class="alert-span">{{ $data['field_message_hint'] }}</span>
                                    @endif
                                </label>
                                <textarea
                                    name="message"
                                    class="form-control"
                                    id="contact-message"
                                    cols="30"
                                    rows="4"
                                    maxlength="{{ $maxLength }}"
                                    required
                                ></textarea>
                                <div class="char-counter">
                                    <span id="char-count">0</span>/{{ $maxLength }} {{ $locale === 'ar' ? 'حرف' : 'chars' }}
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                                    {{ $data['submit_label'] ?? 'إرسال' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
