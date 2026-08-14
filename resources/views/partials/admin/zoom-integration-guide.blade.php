<div
    class="zoom-guide"
    x-data="{ open: false, section: 'setup' }"
    @open-zoom-guide.window="open = true; section = $event.detail?.section || 'setup'"
    @keydown.escape.window="open = false"
>
    <div x-show="open" x-cloak x-transition.opacity class="zoom-guide__backdrop" @click.self="open = false">
        <section class="zoom-guide__dialog" role="dialog" aria-modal="true" aria-labelledby="zoom-guide-title">
            <header>
                <div>
                    <span>دليل الربط والتشغيل</span>
                    <h2 id="zoom-guide-title">كيف يعمل تكامل Zoom؟</h2>
                </div>
                <button type="button" @click="open = false" aria-label="إغلاق"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <nav aria-label="أقسام الدليل">
                <button type="button" @click="section = 'setup'" :class="{ 'is-active': section === 'setup' }">الإعداد</button>
                <button type="button" @click="section = 'meeting'" :class="{ 'is-active': section === 'meeting' }">المحاضرة</button>
                <button type="button" @click="section = 'attendance'" :class="{ 'is-active': section === 'attendance' }">الحضور</button>
                <button type="button" @click="section = 'recording'" :class="{ 'is-active': section === 'recording' }">التسجيل</button>
                <button type="button" @click="section = 'limits'" :class="{ 'is-active': section === 'limits' }">القيود</button>
            </nav>

            <div class="zoom-guide__body">
                <div x-show="section === 'setup'">
                    <h3>إعداد Zoom Marketplace</h3>
                    <ol>
                        <li>أنشئ تطبيق <b>Server-to-Server OAuth</b> من حساب Zoom المؤسسي.</li>
                        <li>امنح التطبيق صلاحيات إدارة الاجتماعات والمستخدمين وقراءة التقارير والتسجيلات.</li>
                        <li>أدخل Account ID وClient ID وClient Secret في صفحة الإعدادات ثم اختبر الاتصال.</li>
                        <li>أضف عنوان Webhook الظاهر في الصفحة واشترك في حدث انتهاء الاجتماع واكتمال التسجيل.</li>
                        <li>زامن مستخدمي Zoom وحدد حساب المدرب أو مجموعة المضيفين الاحتياطية.</li>
                    </ol>
                </div>

                <div x-show="section === 'meeting'">
                    <h3>إنشاء المحاضرة والمضيف</h3>
                    <p>عند إنشاء حصة، تختار المنصة المضيف حسب الاستراتيجية المحددة: حساب مركزي، حساب المدرب، أو أقل حسابات مجموعة المضيفين انشغالاً.</p>
                    <ul>
                        <li>يُرسل للطالب رابط التسجيل الفردي إن كان التسجيل مطلوباً؛ وإلا يُستخدم رابط الاجتماع المحمي.</li>
                        <li>رابط بدء المضيف حساس ولا يظهر للطلاب، ويُطلب من Zoom عند استخدامه.</li>
                        <li>يمكن إضافة Alternative Hosts مرخّصين من الحساب نفسه قبل بدء الاجتماع.</li>
                    </ul>
                </div>

                <div x-show="section === 'attendance'">
                    <h3>الحضور والغياب</h3>
                    <p>بعد انتهاء الاجتماع يجلب النظام تقرير المشاركين ويجمع كل مرات الدخول والخروج للطالب.</p>
                    <ul>
                        <li>المطابقة الأساسية بمعرّف التسجيل الفردي، ثم البريد الإلكتروني كخيار احتياطي.</li>
                        <li>تُحسب حالة الحضور من مدة المشاركة ونسبة المحاضرة وحد التأخير المضبوط.</li>
                        <li>الطلاب الذين لم يظهروا في التقرير يسجلون غياباً، ولا تُستبدل تعديلات الإدارة اليدوية.</li>
                    </ul>
                </div>

                <div x-show="section === 'recording'">
                    <h3>التسجيل والنشر</h3>
                    <ul>
                        <li><b>تلقائي:</b> يبدأ التسجيل السحابي عند بدء المضيف للمحاضرة.</li>
                        <li><b>يدوي:</b> يقرر المضيف أو الـCo-host بدء التسجيل من تطبيق Zoom.</li>
                        <li><b>معطل:</b> لا تطلب المنصة تسجيلاً تلقائياً.</li>
                        <li>بعد المعالجة يرسل Zoom حدثاً، فتسحب المنصة بيانات التسجيل وتطبّق سياسة النشر والصلاحية.</li>
                        <li>يمكن إبقاؤه في Zoom أو نسخه إلى تخزين خاص عند تهيئة S3 أو Google Drive.</li>
                    </ul>
                </div>

                <div x-show="section === 'limits'">
                    <h3>قيود Zoom التي يجب معرفتها</h3>
                    <ul>
                        <li>لا يمكن تعيين Co-host مسبقاً؛ المضيف يرقّيه أثناء الاجتماع. البديل المسبق هو Alternative Host.</li>
                        <li>المضيف البديل يجب أن يكون مستخدماً مرخّصاً داخل حساب Zoom نفسه.</li>
                        <li>التسجيل السحابي والتقارير يتطلبان خطة مدفوعة وترخيصاً مناسباً للمضيف.</li>
                        <li>حماية رابط Zoom بكلمة مرور لا تمنع مشاركته خارج المنصة؛ التخزين الخاص هو الخيار الأقوى للصلاحيات الصارمة.</li>
                    </ul>
                </div>
            </div>

            <footer>
                <i class="fa-solid fa-shield-halved"></i>
                <span>الأسرار وروابط بدء المضيف محفوظة بصورة مشفرة، والعمليات الحساسة خاضعة للصلاحيات وسجل التدقيق.</span>
            </footer>
        </section>
    </div>
</div>

@once
    @push('styles')
        <style>
            .zoom-guide[x-cloak],.zoom-guide [x-cloak]{display:none!important}.zoom-guide__backdrop{position:fixed;z-index:10050;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(8,24,19,.72);backdrop-filter:blur(5px)}
            .zoom-guide__dialog{width:min(52rem,96vw);max-height:94vh;overflow:auto;border-radius:18px;background:#fff;box-shadow:0 28px 80px rgba(0,0,0,.32)}.zoom-guide__dialog>header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.2rem;background:linear-gradient(135deg,#123b2a,#1b684a);color:#fff}.zoom-guide__dialog>header span{color:#9ce5bc;font-size:.62rem;font-weight:900}.zoom-guide__dialog h2{margin:.2rem 0 0;color:#fff;font-size:1.05rem}.zoom-guide__dialog>header button{display:grid;place-items:center;width:2.2rem;height:2.2rem;border:1px solid rgba(255,255,255,.25);border-radius:10px;background:rgba(255,255,255,.1);color:#fff}
            .zoom-guide__dialog>nav{display:flex;gap:.35rem;overflow-x:auto;padding:.7rem 1rem;border-bottom:1px solid #e2e8f0}.zoom-guide__dialog>nav button{white-space:nowrap;padding:.45rem .7rem;border:0;border-radius:8px;background:#f1f5f9;color:#475569;font-size:.65rem;font-weight:900}.zoom-guide__dialog>nav button.is-active{background:#dcfce7;color:#166534}
            .zoom-guide__body{padding:1.1rem 1.25rem;color:#475569;font-size:.76rem;line-height:1.9}.zoom-guide__body h3{margin:0 0 .55rem;color:#17251f;font-size:.9rem}.zoom-guide__body p{margin:.3rem 0 .7rem}.zoom-guide__body ol,.zoom-guide__body ul{margin:0;padding-inline-start:1.3rem}.zoom-guide__body li{margin-bottom:.35rem}.zoom-guide__dialog>footer{display:flex;gap:.55rem;padding:.8rem 1.1rem;background:#fffbeb;color:#854d0e;font-size:.68rem;line-height:1.7}
        </style>
    @endpush
@endonce
