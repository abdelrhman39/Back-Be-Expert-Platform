@php($locale = app()->getLocale())
<footer class="portal-footer">
        <div class="container" style="max-width:1100px;">
            <div class="row g-4">
                <div class="col-md-3">
                    <h6>الروابط الرئيسية</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a></li>
                        <li><a href="{{ route('courses.index', ['locale' => $locale]) }}">البرامج المعتمدة</a></li>
                        <li><a href="{{ legacy_page('ar/register/index.html') }}">التسجيل</a></li>
                        <li><a href="{{ legacy_page('ar/support/contact/index.html') }}">اتصل بنا</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>السياسات</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="{{ route('home', ['locale' => $locale]) }}">سياسة الخصوصية</a></li>
                        <li><a href="{{ route('home', ['locale' => $locale]) }}">الشروط والأحكام</a></li>
                        <li><a href="{{ legacy_page('ar/support/faq/index.html') }}">الأسئلة الشائعة</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>خدمة العملاء</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="{{ legacy_page('ar/support/faq/index.html') }}">الأسئلة الشائعة</a></li>
                        <li><a href="{{ legacy_page('ar/support/contact/index.html') }}">قنوات الدعم</a></li>
                        <li><a href="{{ legacy_page('ar/support/ticket/new/index.html') }}">فتح تذكرة</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>تابعنا</h6>
                    <div class="portal-social">
                        <a href="#" aria-label="يوتيوب"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="إكس"><i class="fab fa-x-twitter"></i></a>
                        <a href="#" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="لنكد إن"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="portal-footer-bottom text-center">
                جميع الحقوق محفوظة © {{ date('Y') }} — {{ platform_name() }} · <a href="{{ route('home', ['locale' => $locale]) }}">خريطة الموقع</a>
            </div>
        </div>
    </footer>

    <div id="portal-cookie" class="portal-cookie" role="dialog" aria-label="ملفات تعريف الارتباط">
        <strong class="d-block mb-2">We use cookies</strong>
        <p class="mb-3 text-muted small">يستخدم هذا الموقع ملفات تعريف ارتباط ضرورية لتشغيله بشكل صحيح، ويمكن اختيار ملفات تتبع لفهم تفاعلك مع الموقع.</p>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" id="portal-cookie-accept" class="btn btn-sm btn-primary text-white">قبول الكل</button>
            <button type="button" id="portal-cookie-reject" class="btn btn-sm btn-light border">رفض الكل</button>
        </div>
    </div>

    <a class="portal-fab" href="{{ legacy_page('ar/support/ticket/new/index.html') }}"><i class="fa-solid fa-headset"></i> الدعم الفني والاستفسارات</a>