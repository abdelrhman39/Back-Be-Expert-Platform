<div
    class="admin-exam-help"
    x-data="{ open: false, section: 'overview' }"
    x-show="open"
    x-cloak
    x-transition.opacity
    @open-exam-help.window="section = $event.detail?.section || 'overview'; open = true"
    @keydown.escape.window="open = false"
    @click.self="open = false"
    x-effect="document.body.style.overflow = open ? 'hidden' : ''"
    role="dialog"
    aria-modal="true"
    aria-labelledby="admin-exam-help-title"
>
    <div class="admin-exam-help__dialog">
        <header class="admin-exam-help__head">
            <div class="admin-exam-help__head-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            <div>
                <span>الدليل التفاعلي</span>
                <h2 id="admin-exam-help-title">شرح بناء وإدارة الاختبار</h2>
                <p>اختر جزءاً من القائمة لعرض طريقة استخدامه خطوة بخطوة.</p>
            </div>
            <button type="button" @click="open = false" class="admin-exam-help__close" aria-label="إغلاق الدليل">×</button>
        </header>

        <div class="admin-exam-help__body">
            <nav class="admin-exam-help__nav" aria-label="أقسام دليل الاختبار">
                <button type="button" @click="section='overview'" :class="{ 'is-active': section==='overview' }"><i class="fa-solid fa-route"></i><span>نظرة عامة</span></button>
                <button type="button" @click="section='editor'" :class="{ 'is-active': section==='editor' }"><i class="fa-solid fa-pen-to-square"></i><span>إضافة سؤال</span></button>
                <button type="button" @click="section='canvas'" :class="{ 'is-active': section==='canvas' }"><i class="fa-solid fa-list-ol"></i><span>مخطط الأسئلة</span></button>
                <button type="button" @click="section='bank'" :class="{ 'is-active': section==='bank' }"><i class="fa-solid fa-box-archive"></i><span>بنك الأسئلة</span><b>مهم</b></button>
                <button type="button" @click="section='pool'" :class="{ 'is-active': section==='pool' }"><i class="fa-solid fa-shuffle"></i><span>المجموعة العشوائية</span></button>
                <button type="button" @click="section='import'" :class="{ 'is-active': section==='import' }"><i class="fa-solid fa-file-import"></i><span>الاستيراد والتصنيفات</span></button>
                <button type="button" @click="section='readiness'" :class="{ 'is-active': section==='readiness' }"><i class="fa-solid fa-clipboard-check"></i><span>المعاينة والجاهزية</span><b>جديد</b></button>
                <button type="button" @click="section='attempts'" :class="{ 'is-active': section==='attempts' }"><i class="fa-solid fa-stopwatch"></i><span>المؤقت والمحاولات</span><b>جديد</b></button>
                <button type="button" @click="section='integrity'" :class="{ 'is-active': section==='integrity' }"><i class="fa-solid fa-shield-halved"></i><span>مراقبة النزاهة</span><b>جديد</b></button>
                <button type="button" @click="section='publish'" :class="{ 'is-active': section==='publish' }"><i class="fa-solid fa-paper-plane"></i><span>المراجعة والنشر</span></button>
            </nav>

            <main class="admin-exam-help__content">
                <section x-show="section==='overview'">
                    <span class="admin-exam-help__eyebrow">مسار العمل الموصى به</span>
                    <h3>كيف تُنشئ اختباراً متكاملاً؟</h3>
                    <p class="admin-exam-help__lead">ابدأ بإعداد الاختبار، ثم أضف المحتوى وراجعه قبل النشر. لا يصل الاختبار إلى الطلاب أثناء بقائه في حالة مسودة.</p>
                    <div class="admin-exam-help__steps">
                        <div><b>1</b><span><strong>الإعدادات</strong><small>حدد المواعيد، المدة، المحاولات، النجاح وسياسة إظهار النتيجة.</small></span></div>
                        <div><b>2</b><span><strong>إضافة الأسئلة</strong><small>أنشئ أسئلة جديدة أو استخدم أسئلة جاهزة من بنك المقرر.</small></span></div>
                        <div><b>3</b><span><strong>بناء النموذج</strong><small>رتّب الأسئلة، حدد الدرجات وأضف مجموعة عشوائية عند الحاجة.</small></span></div>
                        <div><b>4</b><span><strong>المراجعة والنشر</strong><small>تحقق من الإجابات والدرجة النهائية ثم انشر الاختبار للطلاب المرشحين.</small></span></div>
                    </div>
                    <div class="admin-exam-help__note"><i class="fa-solid fa-shield-halved"></i><span><strong>حماية المحاولات:</strong> عند بدء الطالب، تُحفظ له نسخة مستقلة من الأسئلة؛ التعديلات اللاحقة لا تغيّر محاولته الجارية.</span></div>
                </section>

                <section x-show="section==='editor'">
                    <span class="admin-exam-help__eyebrow">محرر السؤال</span>
                    <h3>إضافة سؤال جديد بطريقة صحيحة</h3>
                    <div class="admin-exam-help__cards">
                        <article><i class="fa-solid fa-folder"></i><h4>التصنيف والوسوم</h4><p>اختر الوحدة أو الموضوع، وأضف وسوماً مفصولة بفاصلة لتسهيل العثور على السؤال لاحقاً.</p></article>
                        <article><i class="fa-solid fa-shapes"></i><h4>نوع السؤال</h4><p>اختر من الاختيار المفرد والمتعدد، صح وخطأ، النص، الفراغات، المطابقة، الترتيب، الرقمي، المقالي أو رفع ملف.</p></article>
                        <article><i class="fa-solid fa-star"></i><h4>الدرجة والصعوبة</h4><p>الدرجة تُستخدم في هذا الاختبار وتُحفظ أيضاً كدرجة افتراضية للسؤال داخل البنك.</p></article>
                        <article><i class="fa-solid fa-circle-check"></i><h4>نموذج الإجابة</h4><p>حدد الإجابة الصحيحة بدقة. الأسئلة المقالية ورفع الملفات تنتقل للتصحيح اليدوي.</p></article>
                    </div>
                    <div class="admin-exam-help__how">
                        <strong>طريقة الإضافة</strong>
                        <ol><li>اختر التصنيف والنوع.</li><li>اكتب نصاً واضحاً للسؤال.</li><li>أدخل الخيارات وحدد الصحيح.</li><li>حدد الدرجة والصعوبة.</li><li>أضف شرح الإجابة ثم اضغط «إضافة السؤال».</li></ol>
                    </div>
                </section>

                <section x-show="section==='canvas'">
                    <span class="admin-exam-help__eyebrow">مخطط الأسئلة</span>
                    <h3>التحكم في الأسئلة المضافة</h3>
                    <div class="admin-exam-help__legend">
                        <div><i class="fa-solid fa-grip-vertical"></i><span><strong>السحب والإفلات</strong><small>اسحب المقبض لتغيير ترتيب السؤال؛ يُحفظ الترتيب عند الإفلات.</small></span></div>
                        <div><i class="fa-solid fa-chevron-down"></i><span><strong>عرض التفاصيل</strong><small>يفتح النص الكامل والخيارات والإجابة الصحيحة وشرحها.</small></span></div>
                        <div><i class="fa-solid fa-pen"></i><span><strong>التعديل</strong><small>ينقل السؤال إلى المحرر لتعديل محتواه ودرجته وتصنيفه.</small></span></div>
                        <div><i class="fa-solid fa-trash"></i><span><strong>الإزالة</strong><small>يزيل السؤال من الاختبار فقط، ويظل محفوظاً داخل بنك المقرر.</small></span></div>
                    </div>
                </section>

                <section x-show="section==='bank'">
                    <span class="admin-exam-help__eyebrow">مركز إدارة المحتوى</span>
                    <h3>ما هو بنك أسئلة المقرر؟</h3>
                    <p class="admin-exam-help__lead">هو مستودع مركزي للأسئلة الخاصة بالمقرر. كل سؤال جديد تنشئه يُحفظ في البنك تلقائياً، ويمكن إعادة استخدامه في اختبارات أخرى دون كتابته من جديد.</p>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <div><h4>استعراض البنك</h4><p>ابحث بنص السؤال أو الوسوم، ثم صفِّ النتائج حسب التصنيف ونوع السؤال ومستوى الصعوبة. الأسئلة الموجودة بالفعل في الاختبار تُستبعد تلقائياً لمنع التكرار.</p></div>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-plus"></i></div>
                        <div><h4>إضافة سؤال جاهز</h4><p>اضغط «إضافة» بجانب السؤال. سيُضاف إلى مخطط الاختبار بدرجته الافتراضية، ويمكنك بعدها تعديله أو تغيير ترتيبه.</p></div>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-file-export"></i></div>
                        <div><h4>تصدير CSV</h4><p>ينزّل نسخة من أسئلة المقرر تشمل الأنواع والإجابات والتصنيفات والوسوم. يمكن استخدام الملف نسخة احتياطية أو نموذجاً للاستيراد.</p></div>
                    </div>
                    <div class="admin-exam-help__note admin-exam-help__note--gold"><i class="fa-solid fa-lightbulb"></i><span><strong>أفضل ممارسة:</strong> صنّف الأسئلة حسب الوحدة والموضوع، وأضف وسوماً دقيقة؛ بذلك تصبح الفلاتر والمجموعات العشوائية أكثر فاعلية.</span></div>
                </section>

                <section x-show="section==='pool'">
                    <span class="admin-exam-help__eyebrow">النماذج المتنوعة</span>
                    <h3>إنشاء مجموعة أسئلة عشوائية</h3>
                    <p class="admin-exam-help__lead">تُستخدم لإعطاء كل طالب مجموعة مختلفة من الأسئلة مع الحفاظ على العدد والدرجة نفسيهما.</p>
                    <div class="admin-exam-help__how">
                        <ol>
                            <li>حدد التصنيف والنوع والصعوبة المطلوبة.</li>
                            <li>راقب رقم «الأسئلة المطابقة الآن» للتأكد من كفاية البنك.</li>
                            <li>حدد عدد الأسئلة التي سيحصل عليها كل طالب.</li>
                            <li>حدد درجة موحدة للسؤال الواحد.</li>
                            <li>اضغط «حفظ وتثبيت المجموعة».</li>
                        </ol>
                    </div>
                    <div class="admin-exam-help__example"><strong>مثال</strong><span>يوجد 30 سؤالاً في تصنيف «الوحدة الأولى»؛ اختر 10 أسئلة × درجتين. يحصل كل طالب على 10 أسئلة عشوائية بإجمالي 20 درجة.</span></div>
                    <div class="admin-exam-help__note"><i class="fa-solid fa-lock"></i><span>تُثبّت قائمة الأسئلة المرشحة عند الحفظ لضمان عدم تغير المجموعة دون قرار منك. أعد حفظها إذا أضفت أسئلة جديدة وتريد إدخالها في السحب.</span></div>
                </section>

                <section x-show="section==='import'">
                    <span class="admin-exam-help__eyebrow">الإدارة الجماعية</span>
                    <h3>الاستيراد والتصنيفات</h3>
                    <div class="admin-exam-help__cards">
                        <article><i class="fa-solid fa-file-csv"></i><h4>ملف CSV</h4><p>صدّر البنك أولاً للحصول على رؤوس الأعمدة الصحيحة، عدّل الملف ثم ارفعه بحجم لا يتجاوز 2MB.</p></article>
                        <article><i class="fa-solid fa-rotate-left"></i><h4>استيراد آمن</h4><p>تُفحص جميع الصفوف قبل الحفظ النهائي. إذا احتوى صف على خطأ، يُلغى الاستيراد بالكامل ولا تتكون بيانات ناقصة.</p></article>
                        <article><i class="fa-solid fa-folder-tree"></i><h4>تصنيف رئيسي</h4><p>يمثل وحدة أو محوراً كبيراً مثل «الوحدة الأولى» أو «القواعد الأساسية».</p></article>
                        <article><i class="fa-regular fa-folder"></i><h4>تصنيف فرعي</h4><p>يمثل موضوعاً داخل التصنيف الرئيسي، ما يتيح تنظيماً أكثر دقة للأسئلة.</p></article>
                    </div>
                </section>

                <section x-show="section==='readiness'">
                    <span class="admin-exam-help__eyebrow">المعاينة وفحص الجاهزية</span>
                    <h3>راجع الاختبار كما سيظهر للطالب قبل نشره</h3>
                    <p class="admin-exam-help__lead">اضغط زر «المعاينة والفحص» أعلى منشئ الاختبار. ستفتح صفحة تجمع نموذج شاشة الطالب مع تقرير تقني يحدد هل الاختبار جاهز للنشر أم يحتاج إلى تعديل.</p>

                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-gauge-high"></i></div>
                        <div><h4>درجة الجاهزية</h4><p>نسبة تلخص سلامة الاختبار. تعتمد على اكتمال الإعدادات والأسئلة والإجابات والدرجات والمواعيد والمجموعات العشوائية والطلاب المرشحين.</p></div>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-laptop"></i></div>
                        <div><h4>معاينة شاشة الطالب</h4><p>تعرض ترتيب الأسئلة وأنواع حقول الإجابة والدرجات والتعليمات. وهي معاينة فقط ولا تنشئ محاولة طالب أو تحفظ إجابات.</p></div>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                        <div><h4>الأخطاء المانعة للنشر</h4><p>مثل سؤال بلا إجابة صحيحة، تكرار السؤال، مجموعة عشوائية غير مكتملة، درجة غير صالحة أو موعد إغلاق يسبق الفتح. يجب إصلاحها قبل تفعيل زر النشر.</p></div>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div><h4>التنبيهات</h4><p>لا تمنع النشر، لكنها تلفت الانتباه إلى أمور مهمة مثل وجود أسئلة تحتاج تصحيحاً يدوياً أو عدم وجود طلاب مرشحين حالياً.</p></div>
                    </div>

                    <div class="admin-exam-help__how">
                        <strong>طريقة العمل المقترحة</strong>
                        <ol>
                            <li>افتح «المعاينة والفحص» بعد الانتهاء من بناء الأسئلة.</li>
                            <li>راجع عدد الأسئلة وإجمالي الدرجات والطلاب المرشحين.</li>
                            <li>افتح تقرير الفحص وعالج كل عنصر أحمر.</li>
                            <li>راجع نموذج الطالب بصرياً وتأكد من وضوح الأسئلة.</li>
                            <li>عندما تظهر حالة «جاهز للنشر» اضغط «نشر الاختبار».</li>
                        </ol>
                    </div>

                    <div class="admin-exam-help__note"><i class="fa-solid fa-lock"></i><span><strong>نسخة النشر الثابتة:</strong> عند النشر تُحفظ نسخة مشفرة من الأسئلة والإجابات والدرجات والإعدادات. محاولات الطلاب المرتبطة بها لا تتغير إذا عدّلت الاختبار لاحقاً.</span></div>
                    <div class="admin-exam-help__note admin-exam-help__note--gold"><i class="fa-solid fa-code-branch"></i><span><strong>إعادة النشر:</strong> إذا عدّلت اختباراً منشوراً، ارجع إلى صفحة المعاينة واضغط «نشر نسخة جديدة». المحاولات القديمة تظل على نسختها الأصلية، والجديدة تستخدم أحدث نسخة.</span></div>
                </section>

                <section x-show="section==='attempts'">
                    <span class="admin-exam-help__eyebrow">تجربة الطالب والاستثناءات الفردية</span>
                    <h3>المؤقت، تتبع الإجابات وإعادة فتح الاختبار</h3>
                    <div class="admin-exam-help__cards">
                        <article><i class="fa-solid fa-stopwatch"></i><h4>المؤقت الحي</h4><p>يبدأ العد فور إنشاء المحاولة ويتحرك كل ثانية. يتحول إلى الأحمر ويومض خلال آخر خمس دقائق، ويتم التسليم تلقائياً عند انتهاء الوقت.</p></article>
                        <article><i class="fa-solid fa-table-cells"></i><h4>خريطة الأسئلة</h4><p>كل أرقام الأسئلة تبدأ باللون الأحمر. يتحول السؤال المكتمل إلى الأخضر، ويحدد الإطار الأزرق السؤال المعروض حالياً.</p></article>
                        <article><i class="fa-solid fa-arrow-pointer"></i><h4>التنقل المباشر</h4><p>يستطيع الطالب الضغط على أي رقم للانتقال إلى السؤال المقابل، وتُحفظ الإجابة الحالية قبل الانتقال.</p></article>
                        <article><i class="fa-solid fa-flag"></i><h4>علامة المراجعة</h4><p>يمكن وضع علامة برتقالية على سؤال للعودة إليه قبل التسليم دون أن تغيّر حالة كونه مجاباً أو غير مجاب.</p></article>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-user-clock"></i></div>
                        <div><h4>محاولة إضافية لطالب محدد</h4><p>من صفحة «المعاينة والفحص» انتقل إلى «استثناءات المحاولات»، وابحث عن الطالب ثم اضغط «محاولة إضافية». يُفتح الاختبار له فوراً حتى إذا استنفد محاولاته أو أُغلق الاختبار عاماً.</p></div>
                    </div>
                    <div class="admin-exam-help__feature">
                        <div class="admin-exam-help__feature-icon"><i class="fa-solid fa-infinity"></i></div>
                        <div><h4>فتح بلا نهاية</h4><p>يفعّل محاولات غير محدودة لهذا الطالب فقط. يمكن إيقاف الوضع غير المحدود أو إلغاء إعادة الفتح الفردية في أي وقت دون التأثير على محاولاته المسجلة.</p></div>
                    </div>
                    <div class="admin-exam-help__note admin-exam-help__note--warning"><i class="fa-solid fa-user-shield"></i><span>الاستثناء فردي ولا يغيّر عدد المحاولات أو مواعيد الإتاحة لبقية الطلاب.</span></div>
                </section>

                <section x-show="section==='integrity'">
                    <span class="admin-exam-help__eyebrow">مركز مراقبة النزاهة</span>
                    <h3>تتبع الأحداث ومراجعة المحاولات</h3>
                    <p class="admin-exam-help__lead">اضغط «النزاهة» أعلى منشئ الاختبار لعرض جميع المحاولات مرتبة حسب مستوى الخطورة، ثم افتح السجل الزمني لكل طالب قبل اتخاذ القرار.</p>
                    <div class="admin-exam-help__cards">
                        <article><i class="fa-solid fa-eye-slash"></i><h4>مغادرة الصفحة</h4><p>تُسجل عند الانتقال إلى تبويب أو نافذة أخرى وتضيف نقطة واحدة إلى مؤشر الخطورة.</p></article>
                        <article><i class="fa-solid fa-copy"></i><h4>النسخ واللصق</h4><p>كل محاولة تضيف نقطتين، مع تجاهل التكرار التقني خلال ثانيتين لمنع تضخيم المؤشر.</p></article>
                        <article><i class="fa-solid fa-expand"></i><h4>الخروج من ملء الشاشة</h4><p>يُسجل زمن الخروج والسؤال الذي كان معروضاً، ويضيف نقطتين إلى المؤشر.</p></article>
                        <article><i class="fa-solid fa-gauge-high"></i><h4>تصنيف الخطورة</h4><p>منخفض 1–2، متوسط 3–5، مرتفع 6–9، وحرج من 10 نقاط فأكثر.</p></article>
                    </div>
                    <div class="admin-exam-help__how">
                        <strong>طريقة المراجعة</strong>
                        <ol><li>صفِّ المحاولات حسب الخطورة أو حالة المراجعة.</li><li>افتح «السجل» للمحاولة المطلوبة.</li><li>راجع تسلسل الأحداث والتوقيت والسؤال وعنوان IP.</li><li>اكتب ملاحظتك ثم اختر «اعتمادها سليمة» أو «تأكيد المخالفة».</li></ol>
                    </div>
                    <div class="admin-exam-help__note"><i class="fa-solid fa-scale-balanced"></i><span><strong>قرار بشري:</strong> المؤشر لا يحكم بالغش تلقائياً؛ هو أداة لترتيب الحالات التي تحتاج مراجعة وتوثيق قرار المسؤول.</span></div>
                </section>

                <section x-show="section==='publish'">
                    <span class="admin-exam-help__eyebrow">الخطوة النهائية</span>
                    <h3>المراجعة قبل نشر الاختبار</h3>
                    <div class="admin-exam-help__checklist">
                        <label><i class="fa-solid fa-check"></i><span>تأكد من صحة المواعيد والمدة وعدد المحاولات.</span></label>
                        <label><i class="fa-solid fa-check"></i><span>افتح تفاصيل كل سؤال وراجع الإجابة الصحيحة.</span></label>
                        <label><i class="fa-solid fa-check"></i><span>تحقق من إجمالي الدرجات ونسبة النجاح.</span></label>
                        <label><i class="fa-solid fa-check"></i><span>تأكد أن المجموعة العشوائية تحتوي عدداً كافياً من الأسئلة.</span></label>
                        <label><i class="fa-solid fa-check"></i><span>راجع سياسة ظهور النتيجة والإجابات للطالب.</span></label>
                        <label><i class="fa-solid fa-check"></i><span>افتح «المعاينة والفحص» وتأكد أن الحالة «جاهز للنشر».</span></label>
                    </div>
                    <div class="admin-exam-help__note admin-exam-help__note--warning"><i class="fa-solid fa-triangle-exclamation"></i><span>بعد الضغط على «نشر الاختبار» تُسجّل قائمة الطلاب المرشحين، وتُنشأ نسخة نشر ثابتة، ويصلهم إشعار. استخدم المسودة حتى تنتهي من المراجعة.</span></div>
                </section>
            </main>
        </div>
    </div>
</div>
