@php
    $supportPhone = \App\Models\PlatformSetting::get('support_phone', '966543406744') ?? '966543406744';
    $whatsappNumber = \App\Models\PlatformSetting::get('whatsapp_number', $supportPhone) ?? $supportPhone;
    $phoneDigits = preg_replace('/\D+/', '', $supportPhone);
    $whatsappDigits = preg_replace('/\D+/', '', $whatsappNumber);
@endphp

<div class="course-contact-actions" dir="rtl">
    <a class="course-contact-actions__btn course-contact-actions__btn--phone"
       href="tel:+{{ $phoneDigits }}"
       rel="noopener">
        <span class="course-contact-actions__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                <path d="m6.987 2.066-.717.216a3.5 3.5 0 0 0-2.454 2.854c-.297 2.068.367 4.486 1.968 7.259 1.597 2.766 3.355 4.548 5.29 5.328a3.5 3.5 0 0 0 3.715-.705l.542-.514a2 2 0 0 0 .247-2.623l-1.356-1.88a1.5 1.5 0 0 0-1.655-.556l-2.051.627-.053.01c-.226.033-.748-.456-1.398-1.582-.68-1.178-.82-1.867-.633-2.045l1.043-.973a2.497 2.497 0 0 0 .575-2.85l-.662-1.471a2 2 0 0 0-2.4-1.095Zm1.49 1.505.66 1.471a1.497 1.497 0 0 1-.344 1.71l-1.046.974C7.078 8.36 7.3 9.442 8.2 11c.846 1.466 1.618 2.19 2.448 2.064l.124-.026 2.088-.637a.5.5 0 0 1 .552.185l1.356 1.88a1 1 0 0 1-.123 1.312l-.543.514a2.5 2.5 0 0 1-2.653.503c-1.698-.684-3.303-2.311-4.798-4.9C5.152 9.3 4.545 7.093 4.806 5.278a2.5 2.5 0 0 1 1.753-2.039l.717-.216a1 1 0 0 1 1.2.548Z"/>
            </svg>
        </span>
        <span class="course-contact-actions__text">
            <span class="course-contact-actions__label">اتصل بنا</span>
            <span class="course-contact-actions__hint" dir="ltr">+{{ $phoneDigits }}</span>
        </span>
    </a>

    <a class="course-contact-actions__btn course-contact-actions__btn--whatsapp"
       href="https://wa.me/{{ $whatsappDigits }}"
       target="_blank"
       rel="noopener noreferrer">
        <span class="course-contact-actions__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16.6 14c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.2-.6.8-.8 1-.1.2-.3.2-.5.1-.7-.3-1.4-.7-2-1.2-.5-.5-1-1.1-1.4-1.7-.1-.2 0-.4.1-.5.1-.1.2-.3.4-.4.1-.1.2-.3.2-.4.1-.1.1-.3 0-.4-.1-.1-.6-1.3-.8-1.8-.1-.7-.3-.7-.5-.7h-.5c-.2 0-.5.2-.6.3-.6.6-.9 1.3-.9 2.1.1.9.4 1.8 1 2.6 1.1 1.6 2.5 2.9 4.2 3.7.5.2.9.4 1.4.5.5.2 1 .2 1.6.1.7-.1 1.3-.6 1.7-1.2.2-.4.2-.8.1-1.2l-.4-.2m2.5-9.1C15.2 1 8.9 1 5 4.9c-3.2 3.2-3.8 8.1-1.6 12L2 22l5.3-1.4c1.5.8 3.1 1.2 4.7 1.2c5.5 0 9.9-4.4 9.9-9.9c.1-2.6-1-5.1-2.8-7m-2.7 14c-1.3.8-2.8 1.3-4.4 1.3-1.5 0-2.9-.4-4.2-1.1l-.3-.2-3.1.8.8-3-.2-.3c-2.4-4-1.2-9 2.7-11.5S16.6 3.7 19 7.5c2.4 3.9 1.3 9-2.6 11.4"/>
            </svg>
        </span>
        <span class="course-contact-actions__text">
            <span class="course-contact-actions__label">واتساب</span>
            <span class="course-contact-actions__hint">رد سريع</span>
        </span>
    </a>
</div>
