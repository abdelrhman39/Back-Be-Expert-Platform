@php
    $locale = app()->getLocale();
@endphp

<aside class="apply-sidebar">
    <div class="apply-sidebar-card">
        <h3>كيف تتم المعالجة؟</h3>
        <ol class="apply-steps">
            <li>
                <span class="apply-steps__icon">1</span>
                <span>تعبئة النموذج وإرسال الطلب</span>
            </li>
            <li>
                <span class="apply-steps__icon">2</span>
                <span>مراجعة الطلب من فريق التسجيل</span>
            </li>
            <li>
                <span class="apply-steps__icon">3</span>
                <span>التواصل معك لإتمام التسجيل أو توجيهك للبرنامج المناسب</span>
            </li>
        </ol>
    </div>

    <div class="apply-sidebar-card">
        <h3>روابط مفيدة</h3>
        <div class="apply-sidebar-links">
            <a href="{{ route('apply.track', ['locale' => $locale]) }}">
                <span aria-hidden="true">↗</span>
                متابعة طلب سابق
            </a>
            <a href="{{ route('courses.index', ['locale' => $locale]) }}">
                <span aria-hidden="true">↗</span>
                تصفّح البرامج التدريبية
            </a>
            <a href="{{ route('contact', ['locale' => $locale]) }}">
                <span aria-hidden="true">↗</span>
                تواصل معنا
            </a>
            <a href="{{ route('support.faq', ['locale' => $locale]) }}">
                <span aria-hidden="true">↗</span>
                الأسئلة الشائعة
            </a>
        </div>
    </div>

    @auth
        <div class="apply-sidebar-card">
            <h3>حسابك</h3>
            <p class="small text-muted mb-2">أنت مسجّل الدخول كـ <strong>{{ auth()->user()->displayName() }}</strong>. تم تعبئة بعض الحقول تلقائياً.</p>
            <a href="{{ route('profile', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm w-100">الملف الشخصي</a>
        </div>
    @endauth
</aside>
