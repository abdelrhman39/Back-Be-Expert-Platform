<?php

namespace Database\Seeders;

use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use Illuminate\Database\Seeder;

class CatalogCourse102ContentSeeder extends Seeder
{
    public function run(): void
    {
        $course = CatalogCourse::query()->find(102);

        if (! $course) {
            $this->command?->warn('Course #102 not found — skipping.');

            return;
        }

        $course->update([
            'slug' => 'certified-business-professional-in-employee-motivation',
        ]);

        CatalogCourseLesson::query()
            ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
            ->delete();

        CatalogCourseModule::query()->where('course_id', $course->id)->delete();

        $modules = $this->curriculum();

        foreach ($modules as $moduleIndex => $moduleData) {
            $module = CatalogCourseModule::query()->create([
                'course_id' => $course->id,
                'title_ar' => $moduleData['title_ar'],
                'title_en' => $moduleData['title_en'] ?? null,
                'code' => $moduleData['code'] ?? null,
                'summary_ar' => $moduleData['summary_ar'] ?? null,
                'summary_en' => $moduleData['summary_en'] ?? null,
                'description_ar' => $moduleData['description_ar'] ?? null,
                'objectives_ar' => $moduleData['objectives_ar'] ?? null,
                'status' => $moduleData['status'] ?? 'published',
                'is_optional' => $moduleData['is_optional'] ?? false,
                'estimated_duration_minutes' => $moduleData['estimated_duration_minutes'] ?? null,
                'drip_days' => $moduleData['drip_days'] ?? null,
                'completion_rule' => $moduleData['completion_rule'] ?? 'all_lessons',
                'icon' => $moduleData['icon'] ?? null,
                'sort_order' => $moduleIndex + 1,
            ]);

            foreach ($moduleData['lessons'] as $lessonIndex => $lesson) {
                CatalogCourseLesson::query()->create([
                    'module_id' => $module->id,
                    'title_ar' => $lesson['title_ar'],
                    'title_en' => $lesson['title_en'] ?? null,
                    'type' => $lesson['type'] ?? 'html',
                    'status' => $lesson['status'] ?? 'published',
                    'is_preview' => $lesson['is_preview'] ?? false,
                    'body_ar' => $lesson['body_ar'] ?? null,
                    'body_en' => $lesson['body_en'] ?? null,
                    'external_url' => $lesson['external_url'] ?? null,
                    'duration_minutes' => $lesson['duration_minutes'] ?? 45,
                    'sort_order' => $lessonIndex + 1,
                ]);
            }
        }

        $stats = [
            'modules' => CatalogCourseModule::query()->where('course_id', $course->id)->count(),
            'lessons' => CatalogCourseLesson::query()
                ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
                ->count(),
        ];

        $this->command?->info("Course #102 seeded: {$stats['modules']} modules, {$stats['lessons']} lessons.");
    }

    /** @return array<int, array<string, mixed>> */
    protected function curriculum(): array
    {
        return [
            [
                'title_ar' => 'الوحدة 1 — مقدمة البرنامج وأهداف CBP-EM',
                'title_en' => 'Module 1 — Program Introduction & Goals',
                'code' => 'M1-INTRO',
                'icon' => 'fa-rocket',
                'summary_ar' => 'تعريف بالبرنامج، أهدافه، والفئة المستهدفة — نقطة انطلاق رحلة CBP-EM.',
                'estimated_duration_minutes' => 90,
                'lessons' => [
                    [
                        'title_ar' => 'مرحباً بك في برنامج CBP-EM',
                        'title_en' => 'Welcome to CBP-EM',
                        'type' => 'html',
                        'is_preview' => true,
                        'duration_minutes' => 30,
                        'body_ar' => <<<'HTML'
<h3>نبذة عامة عن البرنامج</h3>
<p>برنامج <strong>محترف الأعمال المعتمد في تحفيز الموظفين (CBP-EM)</strong> مخصص لتأهيل المختصين والقادة في الموارد البشرية وإدارة الفرق، بمهارات علمية وعملية لفهم سلوك الموظفين وتحفيزهم.</p>
<p>يركز البرنامج على تطبيق نظريات التحفيز الحديثة وبناء استراتيجيات فعّالة لرفع الأداء وزيادة الرضا الوظيفي وتعزيز الانتماء المؤسسي.</p>
<ul>
<li><strong>المدة:</strong> 3 أيام تدريب عن بُعد</li>
<li><strong>إجمالي الساعات:</strong> 15 ساعة تدريبية</li>
<li><strong>الشهادة:</strong> محترف أعمال معتمد في تحفيز الموظفين</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'أهداف البرنامج التدريبي',
                        'title_en' => 'Program Goals',
                        'type' => 'document',
                        'duration_minutes' => 45,
                        'body_ar' => <<<'HTML'
<h3>أهداف البرنامج</h3>
<ul>
<li>إعداد المشاركين لفهم أساسيات تحفيز الموظفين في بيئات العمل الحديثة.</li>
<li>تطوير مهارات القادة والمديرين في بناء فرق أكثر انخراطاً وإنتاجية.</li>
<li>تمكين المتدربين من تحليل أداء الفريق وتحديد نقاط القوة والضعف.</li>
<li>تطبيق نظريات التحفيز الحديثة: ماسلو، هرتzberg، وMcClelland.</li>
<li>تعزيز مهارات التواصل والحوار والاستماع الفعّال داخل الفرق.</li>
<li>تصميم بيئة عمل محفّزة تقلل دوران الموظفين وترفع الرضا الوظيفي.</li>
<li>الاستعداد لاجتياز اختبار الاعتماد CBP-EM بثقة وكفاءة.</li>
</ul>
<h4>الفئة المستهدفة</h4>
<ul>
<li>مديرو ومسؤولو الموارد البشرية</li>
<li>متخصصو التدريب والتطوير</li>
<li>قادة الفرق والمشرفون</li>
<li>رواد الأعمال وأصحاب المشاريع</li>
<li>مديرون يسعون لتحسين أداء فرقهم</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'فيديو: نظرة عامة على رحلة التعلم',
                        'title_en' => 'Video: Learning Journey Overview',
                        'type' => 'video',
                        'duration_minutes' => 15,
                        'external_url' => 'https://www.youtube.com/embed/1gUbd5bXuO0',
                        'body_ar' => '<p>شاهد هذا الفيديو التمهيدي لفهم هيكل البرنامج وما ستتعلمه خلال الأيام الثلاثة.</p>',
                    ],
                ],
            ],
            [
                'title_ar' => 'الوحدة 2 — نظريات وفهم التحفيز',
                'title_en' => 'Module 2 — Motivation Theories',
                'code' => 'M2-THEORY',
                'icon' => 'fa-lightbulb',
                'summary_ar' => 'استكشاف نظريات التحفيز الحديثة وتأثيرها على الأداء المؤسسي.',
                'estimated_duration_minutes' => 240,
                'lessons' => [
                    [
                        'title_ar' => 'مفهوم التحفيز وأهميته في المنظمات',
                        'type' => 'html',
                        'duration_minutes' => 60,
                        'body_ar' => <<<'HTML'
<h3>مفهوم التحفيز</h3>
<p>التحفيز هو القوة الداخلية أو الخارجية التي تدفع الفرد نحو تحقيق أهدافه الوظيفية. في المنظمات الناجحة، لا يكفي إنجاز المهام — بل بناء فرق متحمسة قادرة على الابتكار والاستمرارية.</p>
<h4>محاور الدرس</h4>
<ul>
<li>تعريف التحفيز في سياق العمل المؤسسي</li>
<li>الفرق بين الأداء والانخراط الوظيفي</li>
<li>تأثير التحفيز على الإنتاجية والاستقرار الوظيفي</li>
<li>دور القيادة في خلق climate إيجابي</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'التحفيز الداخلي مقابل التحفيز الخارجي',
                        'type' => 'html',
                        'duration_minutes' => 50,
                        'body_ar' => <<<'HTML'
<h3>التحفيز الداخلي vs الخارجي</h3>
<p><strong>التحفيز الداخلي (Intrinsic):</strong> يأتي من رغبة الموظف الشخصية في التعلم والإنجاز والنمو — مثل شعوره بالمعنى في عمله.</p>
<p><strong>التحفيز الخارجي (Extrinsic):</strong> يعتمد على عوامل خارجية كالمكافآت المالية، الترقيات، التقدير العلني، والحوافز.</p>
<blockquote>القائد الناجح يجمع بين النوعين: يبني تحفيزاً داخلياً مستداماً ويستخدم حوافز خارجية في الوقت المناسب.</blockquote>
<h4>نشاط تطبيقي</h4>
<p>حدّد 3 عوامل تحفيز داخلي و3 خارجي لدى فريقك الحالي، وناقشها في منتدى الدرس.</p>
HTML,
                    ],
                    [
                        'title_ar' => 'نظريات التحفيز: ماسلو، Herzberg، McClelland',
                        'type' => 'document',
                        'duration_minutes' => 75,
                        'body_ar' => <<<'HTML'
<h3>نظريات التحفيز الأساسية</h3>
<h4>1. هرمی ماسلو (Maslow)</h4>
<p>الاحتياجات الهرمية: physiological → safety → belonging → esteem → self-actualization. الموظف المُلبّى احتياجاته الأساسية يركز على التطور والإبداع.</p>
<h4>2. Frederick Herzberg (نظرية عاملين)</h4>
<p><strong>عوامل الرضا (Motivators):</strong> الإنجاز، التقدير، العمل ذو المعنى.<br>
<strong>عوامل النظافة (Hygiene):</strong> الراتب، بيئة العمل، السياسات — غيابها يسبب dissatisfaction لكن وجودها لا يكفي للتحفيز.</p>
<h4>3. David McClelland</h4>
<p>ثلاثة احتياجات: <em>الإنجاز Achievement</em>، <em>السلطة Power</em>، <em>الانتماء Affiliation</em>. فهم نمط موظفك يساعدك على اختيار أسلوب التحفيز المناسب.</p>
HTML,
                    ],
                    [
                        'title_ar' => 'تأثير القيم الشخصية والرضا الوظيفي',
                        'type' => 'html',
                        'duration_minutes' => 55,
                        'body_ar' => <<<'HTML'
<h3>القيم والرضا الوظيفي</h3>
<ul>
<li>العلاقة بين القيم الشخصية للموظف وقيم المنظمة</li>
<li>كيف يؤثر الرضا الوظيفي على الأداء والإنتاجية</li>
<li>مؤشرات قياس الرضا: استبيانات، مقابلات، معدل الدوران</li>
<li>دور المدير في مواءمة أهداف الفرد مع أهداف الفريق</li>
</ul>
HTML,
                    ],
                ],
            ],
            [
                'title_ar' => 'الوحدة 3 — استراتيجيات التحفيز العملية',
                'title_en' => 'Module 3 — Practical Motivation Strategies',
                'code' => 'M3-PRACTICE',
                'icon' => 'fa-users',
                'summary_ar' => 'تطبيق عملي لاستراتيجيات التحفيز وبناء خطط للفرق.',
                'estimated_duration_minutes' => 320,
                'drip_days' => 1,
                'lessons' => [
                    [
                        'title_ar' => 'دور المدير في بيئة العمل المحفّزة',
                        'type' => 'html',
                        'duration_minutes' => 60,
                        'body_ar' => <<<'HTML'
<h3>دور القائد في التحفيز</h3>
<p>المدير هو المحرّك الأول لثقافة التحفيز. من خلال أسلوب القيادة، التواصل اليومي، وتوزيع المسؤوليات، يمكن بناء بيئة يشعر فيها الموظف بالتقدير والأمان.</p>
<ul>
<li>أنماط القيادة التحفيزية vs التفويضية</li>
<li>بناء الثقة والشفافية</li>
<li>تحديد توقعات واضحة وقابلة للقياس</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'أدوات التقدير والمكافآت',
                        'type' => 'document',
                        'duration_minutes' => 50,
                        'body_ar' => <<<'HTML'
<h3>التقدير والمكافآت</h3>
<ul>
<li>المكافآت المالية وغير المالية</li>
<li>التقدير العلني vs الخاص — متى تستخدم كل نوع؟</li>
<li>برامج Employee of the Month ونظام النقاط</li>
<li>تجنب أخطاء المكافأة: التوقيت الخاطئ، عدم العدالة، التكرار بلا معنى</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'مهارات التواصل التحفيزي',
                        'type' => 'html',
                        'duration_minutes' => 55,
                        'body_ar' => <<<'HTML'
<h3>التواصل الذي يُحفّز</h3>
<ul>
<li>الاستماع الفعّال والتغذية الراجعة البنّاءة</li>
<li>صياغة رسائل إيجابية وواضحة</li>
<li>التعامل مع أنواع الموظفين المختلفة</li>
<li>تمكين الموظفين والمشاركة في صنع القرار</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'التعامل مع انخفاض التحفيز والسلوكيات السلبية',
                        'type' => 'html',
                        'duration_minutes' => 65,
                        'body_ar' => <<<'HTML'
<h3>عندما ينخفض التحفيز</h3>
<ul>
<li>أسباب انخفاض التحفيز في مكان العمل</li>
<li>التعامل مع السلوكيات السلبية باحترافية</li>
<li>تحويل التحديات إلى فرص للنمو</li>
<li>استخدام التحدي والاعتراف لرفع الأداء</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'ورشة: بناء خطة تحفيز للفريق',
                        'type' => 'document',
                        'duration_minutes' => 90,
                        'body_ar' => <<<'HTML'
<h3>نشاط تطبيقي — خطة تحفيز شاملة</h3>
<p>في هذا الدرس ستُعدّ خطة تحفيز متكاملة لفريقك تتضمن:</p>
<ol>
<li>تحليل الوضع الحالي (SWOT للفريق)</li>
<li>تحديد 3 أهداف تحفيز قابلة للقياس</li>
<li>اختيار استراتيجيات مناسبة (تقدير، تدريب، تمكين…)</li>
<li>جدول زمني للتنفيذ (30 / 60 / 90 يوماً)</li>
<li>مؤشرات قياس النجاح (KPIs)</li>
</ol>
<p><em>ارفع خطتك في منتدى البرنامج للمراجعة من المدرب.</em></p>
HTML,
                    ],
                ],
            ],
            [
                'title_ar' => 'الوحدة 4 — الثقافة التنظيمية والاستعداد للاعتماد',
                'title_en' => 'Module 4 — Culture & Certification Prep',
                'code' => 'M4-CERT',
                'icon' => 'fa-award',
                'summary_ar' => 'بناء ثقافة إيجابية والاستعداد لاختبار الاعتماد CBP-EM.',
                'estimated_duration_minutes' => 180,
                'drip_days' => 2,
                'lessons' => [
                    [
                        'title_ar' => 'بناء ثقافة تنظيمية إيجابية',
                        'type' => 'html',
                        'duration_minutes' => 50,
                        'body_ar' => <<<'HTML'
<h3>الثقافة التنظيمية</h3>
<ul>
<li>عناصر الثقافة الإيجابية: الثقة، الشفافية، التعلم المستمر</li>
<li>تعزيز الانتماء والمساءلة</li>
<li>قياس أثر الثقافة على الأداء المؤسسي</li>
<li>التعامل مع الضغوط والعقبات التي تؤثر على التحفيز</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'مخرجات التعلم ومتطلبات البرنامج',
                        'type' => 'document',
                        'duration_minutes' => 40,
                        'body_ar' => <<<'HTML'
<h3>مخرجات التعلم</h3>
<ul>
<li>فهم العلاقة بين التحفيز والأداء المؤسسي</li>
<li>تطبيق نظريات التحفيز الحديثة عملياً</li>
<li>تصميم بيئة عمل محفّزة</li>
<li>تطوير خطط عملية لتحسين أداء الفريق</li>
<li>تحسين مهارات التواصل الإيجابي</li>
<li>الاستعداد لاجتياز اختبار الاعتماد بنجاح</li>
</ul>
<h3>متطلبات الالتحاق</h3>
<ul>
<li>الالتزام بحضور برنامج 3 أيام (15 ساعة)</li>
<li>المشاركة في الأنشطة العملية ودراسات الحالة</li>
<li>جهاز حاسب واتصال إنترنت مناسب</li>
<li>الاستعداد لاختبار 50 سؤالاً (اختيار من متعدد / صح وخطأ)</li>
</ul>
HTML,
                    ],
                    [
                        'title_ar' => 'أسئلة شائعة — FAQ',
                        'type' => 'html',
                        'duration_minutes' => 30,
                        'body_ar' => <<<'HTML'
<h3>الأسئلة الشائعة</h3>
<p><strong>ما الفرق بين التحفيز الداخلي والخارجي؟</strong><br>
التحفيز الداخلي يأتي من رغبة الموظف الشخصية في الإنجاز والنمو، بينما الخارجي يعتمد على المكافآت والتقدير والترقيات.</p>
<p><strong>هل البرنامج مفيد لرواد الأعمال؟</strong><br>
نعم، يساعد رواد الأعمال على تحفيز الفرق الصغيرة، بناء ثقافة عمل إيجابية، وتحسين الأداء وتشجيع المبادرة.</p>
<p><strong>هل أحتاج خبرة سابقة في الموارد البشرية؟</strong><br>
لا، يكفي الاهتمام أو الخبرة الأساسية في الإدارة أو القيادة أو التدريب.</p>
HTML,
                    ],
                    [
                        'title_ar' => 'المراجعة النهائية والاستعداد للاختبار',
                        'type' => 'document',
                        'duration_minutes' => 60,
                        'body_ar' => <<<'HTML'
<h3>الاستعداد للاعتماد CBP-EM</h3>
<p>راجع المحاور التالية قبل الاختبار:</p>
<ol>
<li>مفهوم التحفيز وأهميته</li>
<li>التحفيز الداخلي vs الخارجي</li>
<li>نظريات ماسلو، هرتzberg، McClelland</li>
<li>استراتيجيات التحفيز والتقدير</li>
<li>التواصل التحفيزي وتمكين الموظفين</li>
<li>التعامل مع انخفاض التحفيز</li>
<li>بناء ثقافة تنظيمية إيجابية</li>
<li>إعداد خطة تحفيز للفريق</li>
</ol>
<p><strong>بالتوفيق!</strong> 🎓</p>
HTML,
                    ],
                ],
            ],
        ];
    }
}
