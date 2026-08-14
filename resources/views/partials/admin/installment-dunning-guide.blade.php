<div
    class="dunning-guide"
    x-data="{ open: false, section: 'overview' }"
    @open-dunning-guide.window="open = true; section = $event.detail?.section || 'overview'"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        class="dunning-guide__fab"
        title="دليل تصعيد المتأخرات"
        @click="open = true; section = 'overview'"
    >
        <i class="fa-solid fa-book-open"></i>
        <span>دليل</span>
    </button>

    <div x-show="open" x-cloak x-transition.opacity class="dunning-guide__backdrop" @click.self="open = false">
        <section class="dunning-guide__dialog" role="dialog" aria-modal="true" aria-labelledby="dunning-guide-title">
            <header>
                <div>
                    <span>دليل التشغيل</span>
                    <h2 id="dunning-guide-title">كيف يعمل مسار تصعيد الأقساط؟</h2>
                </div>
                <button type="button" @click="open = false" aria-label="إغلاق"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <nav aria-label="أقسام الدليل">
                <button type="button" @click="section = 'overview'" :class="{ 'is-active': section === 'overview' }">نظرة عامة</button>
                <button type="button" @click="section = 'steps'" :class="{ 'is-active': section === 'steps' }">الخطوات</button>
                <button type="button" @click="section = 'actions'" :class="{ 'is-active': section === 'actions' }">الإجراءات</button>
                <button type="button" @click="section = 'payment'" :class="{ 'is-active': section === 'payment' }">عند السداد</button>
                <button type="button" @click="section = 'ops'" :class="{ 'is-active': section === 'ops' }">التشغيل</button>
            </nav>

            <div class="dunning-guide__body">
                <div x-show="section === 'overview'">
                    <h3>الفكرة</h3>
                    <p>عند تأخر طالب عن سداد قسط، يعمل النظام على <b>مسار خطوات</b> يحدده الأدمن بالكامل: ترتيب الخطوات، توقيتها، نص الإيميل، وما الذي يحدث للحساب (تحذير فقط، منع اختبارات، إيقاف تعلم، قفل دخول، قفل نهائي…).</p>
                    <ul>
                        <li>الخطوات ليست ثابتة في الكود — يمكن إضافة / حذف / إعادة ترتيب في أي وقت.</li>
                        <li>كل خطوة تُنفَّذ <b>مرة واحدة فقط</b> لكل قسط.</li>
                        <li>شرط الاستمرار: القسط ما زال غير مدفوع.</li>
                    </ul>
                </div>

                <div x-show="section === 'steps'">
                    <h3>بناء خطوة</h3>
                    <ol>
                        <li>سمِّ الخطوة بما يوضح هدفها للأدمن (مثال: «إنذار بالقفل خلال يومين»).</li>
                        <li>حدد <b>أيام بعد الاستحقاق</b> (أو بالسالب قبل الاستحقاق إن رغبت).</li>
                        <li>اختيارياً حدد <b>ساعة</b> داخل ذلك اليوم (مفيد لإنذار صباحي ثم قفل مسائي).</li>
                        <li>اختر الإجراءات + نص البريد/الإشعار مع المتغيرات.</li>
                    </ol>
                    <p>القالب الافتراضي يوفّر 6 خطوات كمقترح ابتدائي — عدّله بحرية.</p>
                </div>

                <div x-show="section === 'actions'">
                    <h3>ماذا تفعل كل إجراء؟</h3>
                    <ul>
                        <li><b>إرسال إشعار / بريد:</b> يرسل العنوان والنص للطالب.</li>
                        <li><b>إيقاف التعلم:</b> يمنع الحصص والواجبات والمحتوى، مع الإبقاء على صفحة الأقساط للسداد.</li>
                        <li><b>منع الاختبارات:</b> يمنع دخول الاختبارات حتى مع بقاء باقي التعلم متاحاً.</li>
                        <li><b>قفل تسجيل الدخول:</b> يمنع دخول البوابة برسالة توضح سبب الأقساط.</li>
                        <li><b>وسم متعثر / قفل نهائي:</b> يرفع مستوى التصعيد المالي والأمني.</li>
                        <li><b>رسوم تأخير:</b> يطبّق رسوم التأخير وفق إعدادات الرسوم إن كانت مفعّلة.</li>
                    </ul>
                </div>

                <div x-show="section === 'payment'">
                    <h3>عند السداد</h3>
                    <ul>
                        <li>يتوقف مسار التصعيد لذلك القسط فوراً (لا تُنفَّذ خطوات لاحقة).</li>
                        <li>إذا لم يبقَ أقساط متأخرة على العقد: تُرفع القيود ويُعاد تفعيل التعلم/الدخول حسب الحالة.</li>
                        <li>يُرسل إشعار بأن السداد تم ورفع القيود.</li>
                    </ul>
                </div>

                <div x-show="section === 'ops'">
                    <h3>التشغيل والمراقبة</h3>
                    <ul>
                        <li>الأمر المجدول: <code dir="ltr">installments:process-dunning</code> يومياً حسب الوقت المضبوط.</li>
                        <li>يمكن الضغط على «تشغيل الآن» للاختبار اليدوي.</li>
                        <li>جدول «آخر التنفيذات» يعرض ما طُبّق فعلياً.</li>
                        <li>مسار التصعيد الديناميكي يعمل إلى جانب تذكيرات ما قبل الاستحقاق القديمة إن كانت مفعّلة.</li>
                    </ul>
                </div>
            </div>

            <footer>
                <i class="fa-solid fa-shield-halved"></i>
                <span>صلاحية الإدارة: <code>installments.manage</code> — راقب التنفيذات قبل تفعيل القفل النهائي على بيئة الإنتاج.</span>
            </footer>
        </section>
    </div>
</div>

<style>
    .dunning-guide__fab {
        position: fixed;
        left: 1.25rem;
        bottom: 1.25rem;
        z-index: 40;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 0;
        border-radius: 999px;
        padding: 0.7rem 1rem;
        background: #0f766e;
        color: #fff;
        font-weight: 800;
        box-shadow: 0 10px 30px rgba(15, 118, 110, 0.35);
        cursor: pointer;
    }
    .dunning-guide__backdrop {
        position: fixed;
        inset: 0;
        z-index: 80;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .dunning-guide__dialog {
        width: min(720px, 100%);
        max-height: min(88vh, 900px);
        overflow: auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }
    .dunning-guide__dialog header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .dunning-guide__dialog header span {
        display: block;
        color: #0f766e;
        font-size: 0.75rem;
        font-weight: 800;
        margin-bottom: 0.2rem;
    }
    .dunning-guide__dialog header h2 { margin: 0; font-size: 1.15rem; }
    .dunning-guide__dialog header button {
        border: 0; background: #f1f5f9; width: 2.2rem; height: 2.2rem; border-radius: 999px; cursor: pointer;
    }
    .dunning-guide__dialog nav {
        display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0;
    }
    .dunning-guide__dialog nav button {
        border: 1px solid #dbe3ea; background: #fff; border-radius: 999px; padding: 0.35rem 0.8rem;
        font-size: 0.8rem; font-weight: 700; cursor: pointer;
    }
    .dunning-guide__dialog nav button.is-active { background: #0f766e; border-color: #0f766e; color: #fff; }
    .dunning-guide__body { padding: 1.1rem 1.25rem 0.5rem; line-height: 1.7; }
    .dunning-guide__body h3 { margin: 0 0 0.5rem; }
    .dunning-guide__dialog footer {
        display: flex; gap: 0.55rem; align-items: flex-start; padding: 0.9rem 1.25rem 1.2rem;
        color: #64748b; font-size: 0.82rem;
    }
</style>
