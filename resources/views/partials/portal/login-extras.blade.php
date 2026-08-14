@php($locale = app()->getLocale())
<div id="portal-cookie" class="portal-cookie" role="dialog" aria-label="{{ $locale === 'en' ? 'Cookies' : 'ملفات تعريف الارتباط' }}">
    <strong class="d-block mb-2">{{ $locale === 'en' ? 'We use cookies' : 'نستخدم ملفات تعريف الارتباط' }}</strong>
    <p class="mb-3 text-muted small">
        {{ $locale === 'en'
            ? 'This site uses essential cookies to operate correctly. You may accept optional tracking cookies.'
            : 'يستخدم هذا الموقع ملفات تعريف ارتباط ضرورية لتشغيله بشكل صحيح، ويمكن اختيار ملفات تتبع لفهم تفاعلك مع الموقع.' }}
    </p>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" id="portal-cookie-accept" class="btn btn-sm btn-primary text-white">{{ $locale === 'en' ? 'Accept all' : 'قبول الكل' }}</button>
        <button type="button" id="portal-cookie-reject" class="btn btn-sm btn-light border">{{ $locale === 'en' ? 'Reject all' : 'رفض الكل' }}</button>
    </div>
</div>

<a class="portal-fab" href="{{ route('support.ticket.new', ['locale' => $locale]) }}">
    <i class="fa-solid fa-headset"></i>
    {{ $locale === 'en' ? 'Support' : 'الدعم الفني والاستفسارات' }}
</a>
