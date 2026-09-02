<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use App\Models\CatalogCourse;
use App\Models\CatalogCourseDetail;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use App\Services\CatalogCourseService;
use App\Support\CatalogSlugResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogPublicProgramsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogFieldsSeeder::class);
        $this->ensureCategories();

        foreach ($this->programs() as $definition) {
            $this->upsertProgram($definition);
        }

        $this->command?->info('Public catalog programs seeded: '.CatalogCourse::query()->where('status', 'published')->count().' published courses.');
    }

    protected function ensureCategories(): void
    {
        CatalogCategory::query()->updateOrCreate(
            ['id' => CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES],
            [
                'title_ar' => 'الشهادات الاحترافية',
                'title_en' => 'Professional certificates',
                'slug' => 'professional-certificates',
                'sort_order' => 10,
                'sidebar_visible' => true,
            ],
        );

        CatalogCategory::query()->updateOrCreate(
            ['id' => CatalogCourseService::CATEGORY_DIPLOMAS],
            [
                'title_ar' => 'الدبلومات',
                'title_en' => 'Diplomas',
                'slug' => 'diplomas',
                'sort_order' => 20,
                'sidebar_visible' => true,
            ],
        );
    }

    protected function upsertProgram(array $definition): void
    {
        $existing = CatalogCourse::query()->where('slug', $definition['slug'])->first();
        $id = $existing?->id ?? max(201, ((int) CatalogCourse::query()->max('id')) + 1);

        $course = CatalogCourse::query()->updateOrCreate(
            ['id' => $id],
            [
                'title_ar' => $definition['title_ar'],
                'title_en' => $definition['title_en'],
                'slug' => $definition['slug'],
                'image' => $definition['image'] ?? null,
                'price_online' => $definition['price_online'] ?? null,
                'price_onsite' => $definition['price_onsite'] ?? null,
                'delivery_type' => $definition['delivery_type'] ?? 'online',
                'duration_hours' => $definition['duration_hours'] ?? null,
                'duration_days' => $definition['duration_days'] ?? null,
                'duration_label' => $definition['duration_label'] ?? null,
                'city' => $definition['city'] ?? 'الرياض',
                'is_self_learning' => (bool) ($definition['is_self_learning'] ?? false),
                'status' => 'published',
                'is_featured' => (bool) ($definition['is_featured'] ?? false),
            ],
        );

        CatalogSlugResolver::assignSlug($course, $definition['slug']);
        $course->categories()->sync([$definition['category_id']]);
        $course->fields()->sync($definition['field_ids']);

        CatalogCourseDetail::query()->updateOrCreate(
            ['course_id' => $course->id],
            $this->detailPayload($definition),
        );

        $this->replaceCurriculum($course, $definition);
    }

    /** @param  array<string, mixed>  $definition */
    protected function detailPayload(array $definition): array
    {
        $topicsAr = $this->htmlList($definition['topics_ar']);
        $topicsEn = $this->htmlList($definition['topics_en']);

        return [
            'meta_description_ar' => $definition['meta_ar'],
            'meta_description_en' => $definition['meta_en'],
            'brief_ar' => $this->htmlParagraphs($definition['brief_ar']),
            'brief_en' => $this->htmlParagraphs($definition['brief_en']),
            'goals_ar' => $this->htmlList($definition['goals_ar']),
            'goals_en' => $this->htmlList($definition['goals_en']),
            'audience_ar' => $this->htmlList($definition['audience_ar']),
            'audience_en' => $this->htmlList($definition['audience_en']),
            'features_ar' => $this->htmlList($definition['features_ar']),
            'features_en' => $this->htmlList($definition['features_en']),
            'topics_ar' => $topicsAr,
            'topics_en' => $topicsEn,
            'outcomes_ar' => $this->htmlList($definition['outcomes_ar']),
            'outcomes_en' => $this->htmlList($definition['outcomes_en']),
            'conditions_ar' => $this->htmlList($definition['conditions_ar']),
            'conditions_en' => $this->htmlList($definition['conditions_en']),
            'faq_ar' => $this->htmlFaq($definition['faq_ar']),
            'faq_en' => $this->htmlFaq($definition['faq_en']),
            'article_ar' => $this->articleHtml($definition, 'ar'),
            'article_en' => $this->articleHtml($definition, 'en'),
        ];
    }

    protected function replaceCurriculum(CatalogCourse $course, array $definition): void
    {
        CatalogCourseLesson::query()
            ->whereHas('module', fn ($query) => $query->where('course_id', $course->id))
            ->delete();
        CatalogCourseModule::query()->where('course_id', $course->id)->delete();

        foreach ($definition['modules'] as $moduleIndex => $moduleData) {
            $module = CatalogCourseModule::query()->create([
                'course_id' => $course->id,
                'title_ar' => $moduleData['title_ar'],
                'title_en' => $moduleData['title_en'],
                'code' => $moduleData['code'] ?? ('M'.($moduleIndex + 1)),
                'summary_ar' => $moduleData['summary_ar'] ?? null,
                'summary_en' => $moduleData['summary_en'] ?? null,
                'description_ar' => $moduleData['description_ar'] ?? null,
                'objectives_ar' => isset($moduleData['objectives_ar']) ? $this->htmlList($moduleData['objectives_ar']) : null,
                'objectives_en' => isset($moduleData['objectives_en']) ? $this->htmlList($moduleData['objectives_en']) : null,
                'status' => 'published',
                'estimated_duration_minutes' => $moduleData['minutes'] ?? 180,
                'completion_rule' => 'all_lessons',
                'sort_order' => $moduleIndex + 1,
            ]);

            foreach ($moduleData['lessons'] as $lessonIndex => $lesson) {
                CatalogCourseLesson::query()->create([
                    'module_id' => $module->id,
                    'title_ar' => $lesson['title_ar'],
                    'title_en' => $lesson['title_en'],
                    'type' => $lesson['type'] ?? ($lessonIndex === 1 ? 'document' : 'html'),
                    'status' => 'published',
                    'is_preview' => $moduleIndex === 0 && $lessonIndex === 0,
                    'body_ar' => $lesson['body_ar'],
                    'body_en' => $lesson['body_en'],
                    'duration_minutes' => $lesson['minutes'] ?? 45,
                    'sort_order' => $lessonIndex + 1,
                ]);
            }
        }
    }

    /** @return list<array<string, mixed>> */
    protected function programs(): array
    {
        return array_merge($this->certificates(), $this->diplomas());
    }

    /** @return list<array<string, mixed>> */
    protected function certificates(): array
    {
        $cert = CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES;

        return [
            $this->certificateProjectManagement($cert),
            $this->certificateHumanResources($cert),
            $this->certificateDigitalMarketing($cert),
            $this->certificateCybersecurity($cert),
            $this->certificateFinancialAnalysis($cert),
            $this->certificateHospitality($cert),
            $this->certificateDataAnalytics($cert),
        ];
    }

    /** @return list<array<string, mixed>> */
    protected function diplomas(): array
    {
        $dip = CatalogCourseService::CATEGORY_DIPLOMAS;

        return [
            $this->diplomaHumanResources($dip),
            $this->diplomaProjectManagement($dip),
            $this->diplomaInformationTechnology($dip),
            $this->diplomaDigitalMarketing($dip),
            $this->diplomaAccounting($dip),
        ];
    }

    protected function certificateProjectManagement(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'professional-project-management-certificate',
            'title_ar' => 'شهادة محترف إدارة المشاريع',
            'title_en' => 'Professional Project Management Certificate',
            'category_id' => $categoryId,
            'field_ids' => [8],
            'price_online' => 2200,
            'price_onsite' => 2800,
            'delivery_type' => 'both',
            'duration_hours' => 35,
            'duration_days' => 5,
            'duration_label' => '5 أيام / 35 ساعة',
            'is_featured' => true,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'شهادة احترافية في إدارة المشاريع وفق منهجيات التخطيط والتنفيذ والمتابعة، من مركز التعلم المستمر في الجامعة العربية المفتوحة.',
            'meta_en' => 'A professional certificate in project management covering planning, execution, and monitoring from the Continuing Learning Center at Arab Open University.',
            'brief_ar' => [
                'برنامج مكثّف يبني قدرة المشارك على إدارة المشروع من ميثاقه حتى إغلاقه، مع التركيز على النطاق والجدول والتكلفة والمخاطر والتواصل مع أصحاب المصلحة.',
                'يجمع التدريب بين المفاهيم المعتمدة دولياً وتطبيقات عملية على حالات من بيئة العمل السعودية، بما يساعد الفرق على تسليم المبادرات في وقتها وبجودة قابلة للقياس.',
            ],
            'brief_en' => [
                'An intensive track that builds the ability to manage a project from charter to close, covering scope, schedule, cost, risk, and stakeholder communication.',
                'Training combines internationally recognized concepts with practical cases from the Saudi workplace so teams can deliver initiatives on time with measurable quality.',
            ],
            'goals_ar' => ['صياغة ميثاق مشروع وأهداف قابلة للقياس', 'بناء جدول زمني ومسار حرج', 'إدارة المخاطر والتغيير', 'قيادة فريق المشروع والتواصل مع أصحاب المصلحة'],
            'goals_en' => ['Write a measurable project charter', 'Build a schedule and critical path', 'Manage risk and change', 'Lead the project team and stakeholders'],
            'audience_ar' => ['مديرو المشاريع والمنسقون', 'قادة المبادرات والتحوّل', 'مهندسون ومختصون يديرون تسليمات متعددة'],
            'audience_en' => ['Project managers and coordinators', 'Transformation initiative leads', 'Engineers managing multiple deliverables'],
            'features_ar' => ['تطبيق عملي على دراسة حالة', 'قوالب جاهزة للميثاق والجدول', 'اختبار تقييمي في نهاية البرنامج', 'شهادة إتمام من مركز التعلم المستمر'],
            'features_en' => ['A practical case study', 'Ready templates for charter and schedule', 'End-of-program assessment', 'Completion certificate from the Continuing Learning Center'],
            'topics_ar' => ['إطار عمل إدارة المشروع', 'إدارة النطاق والمتطلبات', 'الجدولة والموارد', 'التكلفة والجودة', 'المخاطر والتواصل'],
            'topics_en' => ['Project management framework', 'Scope and requirements', 'Scheduling and resources', 'Cost and quality', 'Risk and communication'],
            'outcomes_ar' => ['خطة مشروع متكاملة', 'سجل مخاطر عملي', 'تقرير حالة أسبوعي'],
            'outcomes_en' => ['An integrated project plan', 'A working risk register', 'A weekly status report'],
            'conditions_ar' => ['مؤهل ثانوي على الأقل', 'خبرة عملية مستحسنة وليست إلزامية', 'جهاز واتصال إنترنت للتدريب عن بعد'],
            'conditions_en' => ['Secondary qualification as a minimum', 'Work experience is helpful but not required', 'A device and internet connection for online delivery'],
            'faq_ar' => [
                ['هل الشهادة معتمدة ضمن برامج المركز؟', 'نعم، تصدر بعد استيفاء الحضور والتقييم النهائي، ويمكن التحقق منها إلكترونياً.'],
                ['هل يتوفر خيار حضوري؟', 'نعم، يُقدَّم البرنامج عن بعد وحضورياً في الرياض وفق الجدول المعلن.'],
            ],
            'faq_en' => [
                ['Is the certificate issued by the Center?', 'Yes. It is issued after attendance and the final assessment, and it can be verified online.'],
                ['Is on-campus delivery available?', 'Yes. The program is offered online and on campus in Riyadh according to the published schedule.'],
            ],
            'modules' => [
                $this->module('M1', 'أساسيات إدارة المشاريع', 'Project management foundations', ['مفهوم المشروع ودورة حياته', 'أدوار مدير المشروع', 'ميثاق المشروع'], ['The project and its life cycle', 'The project manager role', 'The project charter']),
                $this->module('M2', 'التخطيط والجدولة', 'Planning and scheduling', ['تحليل النطاق', 'هيكل تجزئة العمل', 'المسار الحرج'], ['Scope analysis', 'Work breakdown structure', 'The critical path']),
                $this->module('M3', 'التنفيذ والمتابعة', 'Execution and control', ['إدارة الفريق', 'تقارير الأداء', 'التحكم في التغيير'], ['Team leadership', 'Performance reporting', 'Change control']),
                $this->module('M4', 'المخاطر والإغلاق', 'Risk and closure', ['تحديد المخاطر', 'خطط الاستجابة', 'إغلاق المشروع والدروس المستفادة'], ['Risk identification', 'Response plans', 'Closure and lessons learned']),
            ],
        ]);
    }

    protected function certificateHumanResources(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'professional-hr-certificate',
            'title_ar' => 'شهادة محترف الموارد البشرية',
            'title_en' => 'Professional Human Resources Certificate',
            'category_id' => $categoryId,
            'field_ids' => [13],
            'price_online' => 1900,
            'price_onsite' => 2400,
            'delivery_type' => 'both',
            'duration_hours' => 30,
            'duration_days' => 4,
            'duration_label' => '4 أيام / 30 ساعة',
            'is_featured' => true,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'شهادة احترافية في ممارسات الموارد البشرية: الاستقطاب، التطوير، الأداء، والامتثال لأنظمة العمل.',
            'meta_en' => 'A professional certificate in HR practices: attraction, development, performance, and labor-law compliance.',
            'brief_ar' => [
                'يؤهل البرنامج ممارسي الموارد البشرية لتصميم دورة حياة الموظف من الاستقطاب حتى الاحتفاظ، مع ربط السياسات بمؤشرات أداء واضحة.',
                'يركّز المحتوى على بيئة العمل السعودية ومتطلبات الامتثال، إلى جانب مهارات الحوار والتقييم العادل.',
            ],
            'brief_en' => [
                'The program prepares HR practitioners to design the employee lifecycle from attraction to retention, linking policy to clear performance indicators.',
                'Content focuses on the Saudi workplace and compliance requirements, together with fair evaluation and dialogue skills.',
            ],
            'goals_ar' => ['بناء وصف وظيفي ومعايير اختيار', 'تصميم تقييم أداء عادل', 'ربط التدريب باحتياج العمل', 'فهم أساسيات أنظمة العمل ذات الصلة'],
            'goals_en' => ['Write job descriptions and selection criteria', 'Design fair performance reviews', 'Link training to business need', 'Understand relevant labor-system basics'],
            'audience_ar' => ['أخصائيو الموارد البشرية', 'مديرو الإدارات الذين يشرفون على فرق', 'منسقو التوظيف والتدريب'],
            'audience_en' => ['HR specialists', 'Line managers who supervise teams', 'Recruitment and training coordinators'],
            'features_ar' => ['نماذج سياسات جاهزة للتمرين', 'ورش محاكاة لمقابلات التوظيف', 'خطة تطوير فردية في ختام البرنامج'],
            'features_en' => ['Policy templates for practice', 'Interview-simulation workshops', 'A personal development plan at the end of the program'],
            'topics_ar' => ['استراتيجية الموارد البشرية', 'الاستقطاب والاختيار', 'إدارة الأداء', 'التدريب والتطوير', 'علاقات الموظفين'],
            'topics_en' => ['HR strategy', 'Attraction and selection', 'Performance management', 'Learning and development', 'Employee relations'],
            'outcomes_ar' => ['مصفوفة جدارات مبسطة', 'نموذج تقييم أداء', 'خطة استبقاء للوظائف الحرجة'],
            'outcomes_en' => ['A simplified competency matrix', 'A performance-review form', 'A retention plan for critical roles'],
            'conditions_ar' => ['اهتمام بمجال الموارد البشرية أو الإشراف على موظفين', 'إجادة القراءة بالعربية'],
            'conditions_en' => ['Interest in HR or people supervision', 'Ability to read Arabic materials'],
            'faq_ar' => [
                ['هل يناسب حديثي التخرج؟', 'نعم، مع تمرين إضافي على الحالات العملية داخل القاعة.'],
                ['هل يشمل أنظمة العمل؟', 'يتناول المبادئ ذات الصلة دون أن يكون بديلاً عن الاستشارة النظامية المتخصصة.'],
            ],
            'faq_en' => [
                ['Is it suitable for recent graduates?', 'Yes, with extra practice on in-class cases.'],
                ['Does it cover labor regulations?', 'It covers relevant principles and is not a substitute for specialized legal advice.'],
            ],
            'modules' => [
                $this->module('HR1', 'دور الموارد البشرية', 'The HR role', ['الشريك الاستراتيجي', 'دورة حياة الموظف', 'مؤشرات الموارد البشرية'], ['The strategic partner', 'The employee lifecycle', 'HR indicators']),
                $this->module('HR2', 'الاستقطاب والاختيار', 'Attraction and selection', ['تحليل الوظيفة', 'قنوات الاستقطاب', 'المقابلة المنظمة'], ['Job analysis', 'Attraction channels', 'Structured interviews']),
                $this->module('HR3', 'الأداء والتطوير', 'Performance and development', ['أهداف SMART', 'التغذية الراجعة', 'خطط التطوير'], ['SMART goals', 'Feedback', 'Development plans']),
                $this->module('HR4', 'علاقات الموظفين', 'Employee relations', ['بيئة العمل', 'معالجة الشكاوى', 'الاحتفاظ بالكفاءات'], ['Workplace climate', 'Handling grievances', 'Retaining talent']),
            ],
        ]);
    }

    protected function certificateDigitalMarketing(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'digital-marketing-professional-certificate',
            'title_ar' => 'شهادة محترف التسويق الرقمي',
            'title_en' => 'Digital Marketing Professional Certificate',
            'category_id' => $categoryId,
            'field_ids' => [11],
            'price_online' => 1750,
            'price_onsite' => null,
            'delivery_type' => 'online',
            'duration_hours' => 28,
            'duration_days' => 4,
            'duration_label' => '4 أيام / 28 ساعة',
            'is_featured' => true,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'شهادة في التسويق الرقمي: استراتيجية القنوات، المحتوى، الإعلانات، وتحليل الأداء.',
            'meta_en' => 'A certificate in digital marketing: channel strategy, content, advertising, and performance analysis.',
            'brief_ar' => [
                'يمكّن البرنامج المشاركين من بناء خطة تسويق رقمي مترابطة، من فهم العميل إلى اختيار القناة وقياس العائد.',
                'يُقدَّم بالكامل عن بعد مع تمارين على حسابات تجريبية وأدوات قياس شائعة في السوق.',
            ],
            'brief_en' => [
                'The program enables participants to build a connected digital-marketing plan, from customer insight to channel choice and return measurement.',
                'It is delivered fully online with exercises on demo accounts and widely used measurement tools.',
            ],
            'goals_ar' => ['رسم رحلة العميل الرقمية', 'تخطيط محتوى للمنصات', 'إعداد حملة إعلانية أساسية', 'قراءة لوحة مؤشرات الأداء'],
            'goals_en' => ['Map the digital customer journey', 'Plan platform content', 'Set up a basic ad campaign', 'Read a performance dashboard'],
            'audience_ar' => ['مسؤولو التسويق في المنشآت الصغيرة والمتوسطة', 'رواد الأعمال', 'منسقو المحتوى والتواصل'],
            'audience_en' => ['Marketing officers in SMEs', 'Entrepreneurs', 'Content and communications coordinators'],
            'features_ar' => ['خطة قنوات قابلة للتنفيذ خلال أسبوع', 'قوالب تقويم محتوى', 'جلسة مراجعة لحملة تجريبية'],
            'features_en' => ['A one-week channel plan', 'A content-calendar template', 'A review session for a trial campaign'],
            'topics_ar' => ['استراتيجية التسويق الرقمي', 'تحسين محركات البحث', 'الإعلان المدفوع', 'وسائل التواصل', 'التحليلات'],
            'topics_en' => ['Digital strategy', 'Search engine optimization', 'Paid advertising', 'Social media', 'Analytics'],
            'outcomes_ar' => ['وثيقة استراتيجية رقمية مختصرة', 'تقويم محتوى لشهر', 'تقرير أداء أسبوعي'],
            'outcomes_en' => ['A short digital strategy brief', 'A one-month content calendar', 'A weekly performance report'],
            'conditions_ar' => ['إلمام باستخدام الإنترنت ومنصات التواصل', 'حساب بريد إلكتروني للتطبيقات العملية'],
            'conditions_en' => ['Familiarity with the internet and social platforms', 'An email account for practical tools'],
            'faq_ar' => [
                ['هل أحتاج خبرة سابقة؟', 'لا، يبدأ البرنامج من الأساسيات ثم ينتقل إلى بناء الحملة.'],
                ['هل الحضور إلزامي؟', 'نعم بنسبة الحضور المعتمدة للبرنامج عن بعد، مع تسجيلات مساندة عند توفرها.'],
            ],
            'faq_en' => [
                ['Do I need prior experience?', 'No. The program starts from the basics and then builds a campaign.'],
                ['Is attendance required?', 'Yes, according to the approved online attendance rate, with supporting recordings when available.'],
            ],
            'modules' => [
                $this->module('DM1', 'الأساس الاستراتيجي', 'Strategic foundation', ['فهم العميل', 'عرض القيمة', 'اختيار القنوات'], ['Customer insight', 'Value proposition', 'Channel choice']),
                $this->module('DM2', 'المحتوى والبحث', 'Content and search', ['ركائز المحتوى', 'الكلمات المفتاحية', 'تحسين الصفحات'], ['Content pillars', 'Keywords', 'On-page optimization']),
                $this->module('DM3', 'الإعلان المدفوع', 'Paid advertising', ['أهداف الحملة', 'الجمهور', 'ميزانية التجربة'], ['Campaign goals', 'Audience', 'Test budget']),
                $this->module('DM4', 'القياس والتحسين', 'Measurement and improvement', ['مؤشرات التحويل', 'لوحات المتابعة', 'اختبارات أ/ب'], ['Conversion metrics', 'Dashboards', 'A/B tests']),
            ],
        ]);
    }

    protected function certificateCybersecurity(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'cybersecurity-essentials-certificate',
            'title_ar' => 'شهادة أساسيات الأمن السيبراني',
            'title_en' => 'Cybersecurity Essentials Certificate',
            'category_id' => $categoryId,
            'field_ids' => [10],
            'price_online' => 2100,
            'price_onsite' => 2600,
            'delivery_type' => 'both',
            'duration_hours' => 32,
            'duration_days' => 5,
            'duration_label' => '5 أيام / 32 ساعة',
            'is_featured' => false,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'شهادة تأسيسية في الأمن السيبراني: التهديدات، الحماية، والاستجابة للحوادث للممارسين غير المتخصصين والمتخصصين المبتدئين.',
            'meta_en' => 'A foundation certificate in cybersecurity: threats, protection, and incident response for non-specialists and early-career practitioners.',
            'brief_ar' => [
                'يقدّم البرنامج لغة مشتركة لحماية المعلومات داخل الجهة: تصنيف الأصول، التحكم في الوصول، والتصرّف الصحيح عند الاشتباه بحادثة.',
                'المحتوى تطبيقي ويبتعد عن التهويل، مع تمارين على سياسات مبسطة وسيناريوهات تصيّد واحتواء.',
            ],
            'brief_en' => [
                'The program builds a shared language for protecting information: asset classification, access control, and the right response when an incident is suspected.',
                'Content is practical rather than alarmist, with exercises on simplified policies, phishing scenarios, and containment.',
            ],
            'goals_ar' => ['تمييز التهديدات الشائعة', 'تطبيق حد أدنى من الضوابط', 'التبليغ المنظم عن الحوادث', 'رفع وعي المستخدم النهائي'],
            'goals_en' => ['Recognize common threats', 'Apply a minimum control set', 'Report incidents in a structured way', 'Raise end-user awareness'],
            'audience_ar' => ['منسقو تقنية المعلومات', 'مسؤولو الالتزام والجودة', 'موظفون يتعاملون مع بيانات حسّاسة'],
            'audience_en' => ['IT coordinators', 'Compliance and quality officers', 'Staff who handle sensitive data'],
            'features_ar' => ['مختبرات موجهة للمبتدئين', 'قائمة فحص أمنية للإدارة', 'محاكاة رسالة تصيّد'],
            'features_en' => ['Beginner-oriented labs', 'A management security checklist', 'A phishing-mail simulation'],
            'topics_ar' => ['مفاهيم السرية والسلامة والإتاحة', 'إدارة الهويات', 'حماية البريد والشبكات', 'النسخ الاحتياطي', 'الاستجابة للحوادث'],
            'topics_en' => ['Confidentiality, integrity, and availability', 'Identity management', 'Email and network protection', 'Backup', 'Incident response'],
            'outcomes_ar' => ['سياسة استخدام مقبول مختصرة', 'خطة تبليغ عن حادث', 'جلسة توعية لفريق العمل'],
            'outcomes_en' => ['A short acceptable-use policy', 'An incident-reporting plan', 'A team awareness session'],
            'conditions_ar' => ['معرفة عامة باستخدام الحاسب', 'لا يشترط خلفية برمجية'],
            'conditions_en' => ['General computer literacy', 'No programming background required'],
            'faq_ar' => [
                ['هل البرنامج للمختصين فقط؟', 'لا، صُمم لغير المختصين وللمبتدئين في أمن المعلومات.'],
                ['هل هناك اختبار؟', 'نعم، تقييم نظري قصير وحالة عملية في اليوم الأخير.'],
            ],
            'faq_en' => [
                ['Is the program only for specialists?', 'No. It is designed for non-specialists and early-career information-security staff.'],
                ['Is there an assessment?', 'Yes. A short knowledge check and a practical case on the final day.'],
            ],
            'modules' => [
                $this->module('CY1', 'مفاهيم الحماية', 'Protection concepts', ['الأصول المعلوماتية', 'التهديدات الشائعة', 'مبدأ الصلاحيات الأدنى'], ['Information assets', 'Common threats', 'Least privilege']),
                $this->module('CY2', 'الضوابط الأساسية', 'Core controls', ['كلمات المرور والمصادقة', 'تحديث الأنظمة', 'النسخ الاحتياطي'], ['Passwords and authentication', 'System updates', 'Backup']),
                $this->module('CY3', 'الوعي والتصيّد', 'Awareness and phishing', ['علامات الرسالة المشبوهة', 'التبليغ الداخلي', 'تعامل آمن مع الملفات'], ['Signs of a suspicious message', 'Internal reporting', 'Safe file handling']),
                $this->module('CY4', 'الحوادث والتعافي', 'Incidents and recovery', ['التصنيف الأولي', 'الاحتواء', 'الدروس المستفادة'], ['Initial classification', 'Containment', 'Lessons learned']),
            ],
        ]);
    }

    protected function certificateFinancialAnalysis(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'financial-analysis-professional-certificate',
            'title_ar' => 'شهادة التحليل المالي الاحترافي',
            'title_en' => 'Professional Financial Analysis Certificate',
            'category_id' => $categoryId,
            'field_ids' => [14],
            'price_online' => 2300,
            'price_onsite' => 2900,
            'delivery_type' => 'both',
            'duration_hours' => 36,
            'duration_days' => 6,
            'duration_label' => '6 أيام / 36 ساعة',
            'is_featured' => false,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'شهادة في قراءة القوائم المالية، النسب، والتدفقات النقدية لدعم قرارات الإدارة.',
            'meta_en' => 'A certificate in reading financial statements, ratios, and cash flows to support management decisions.',
            'brief_ar' => [
                'يربط البرنامج بين التقارير المالية والأسئلة الإدارية: الربحية، السيولة، والكفاءة، دون تحويل المشارك إلى محاسب قانوني.',
                'يتدرّب المشاركون على حالات من قطاعات خدمية وتجارية، مع نماذج جداول جاهزة للتحليل.',
            ],
            'brief_en' => [
                'The program connects financial reports to management questions: profitability, liquidity, and efficiency, without turning the participant into a licensed accountant.',
                'Participants practice on service and trading cases, with ready spreadsheet models.',
            ],
            'goals_ar' => ['قراءة قائمة الدخل والمركز المالي', 'حساب النسب الأساسية وتفسيرها', 'تحليل التدفق النقدي التشغيلي', 'إعداد ملخص تنفيذي للإدارة'],
            'goals_en' => ['Read the income statement and financial position', 'Calculate and interpret core ratios', 'Analyze operating cash flow', 'Prepare an executive briefing'],
            'audience_ar' => ['محللون ماليون مبتدئون', 'مديرو إدارات غير مالية', 'رواد أعمال يتابعون أداء منشآتهم'],
            'audience_en' => ['Early-career financial analysts', 'Non-finance department managers', 'Entrepreneurs tracking business performance'],
            'features_ar' => ['ملفات عمل على جداول ممتدة', 'حالة متكاملة من القوائم إلى التوصية', 'لغة مبسطة لغير المحاسبين'],
            'features_en' => ['Workbook files', 'An end-to-end case from statements to recommendation', 'Accessible language for non-accountants'],
            'topics_ar' => ['هيكل القوائم المالية', 'نسب السيولة والربحية', 'التدفقات النقدية', 'التحليل الأفقي والعمودي', 'التوصية الإدارية'],
            'topics_en' => ['Statement structure', 'Liquidity and profitability ratios', 'Cash flows', 'Horizontal and vertical analysis', 'Management recommendation'],
            'outcomes_ar' => ['لوحة نسب لجهة افتراضية', 'مذكرة تحليل من صفحة واحدة', 'أسئلة متابعة للإدارة المالية'],
            'outcomes_en' => ['A ratio dashboard for a sample entity', 'A one-page analysis memo', 'Follow-up questions for finance'],
            'conditions_ar' => ['إلمام بأساسيات الأرقام والجداول', 'آلة حاسبة أو برنامج جداول'],
            'conditions_en' => ['Comfort with basic numbers and spreadsheets', 'A calculator or spreadsheet application'],
            'faq_ar' => [
                ['هل أحتاج شهادة محاسبية؟', 'لا، البرنامج موجّه لصنّاع القرار وللمحللين في بداية المسار.'],
                ['ما لغة المواد؟', 'المادة الأساسية بالعربية، مع مصطلحات إنجليزية شائعة في المهنة.'],
            ],
            'faq_en' => [
                ['Do I need an accounting qualification?', 'No. The program is aimed at decision-makers and early-career analysts.'],
                ['What language is used?', 'Core material is in Arabic, with common English professional terms.'],
            ],
            'modules' => [
                $this->module('FA1', 'قراءة القوائم', 'Reading the statements', ['قائمة الدخل', 'المركز المالي', 'الإيضاحات المهمة'], ['Income statement', 'Financial position', 'Key notes']),
                $this->module('FA2', 'النسب المالية', 'Financial ratios', ['السيولة', 'الربحية', 'النشاط والرفع'], ['Liquidity', 'Profitability', 'Activity and leverage']),
                $this->module('FA3', 'التدفق النقدي', 'Cash flow', ['التشغيل', 'الاستثمار', 'التمويل'], ['Operating', 'Investing', 'Financing']),
                $this->module('FA4', 'من التحليل إلى القرار', 'From analysis to decision', ['الاتجاهات', 'الإنذار المبكر', 'كتابة التوصية'], ['Trends', 'Early warning', 'Writing the recommendation']),
            ],
        ]);
    }

    protected function certificateHospitality(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'hospitality-service-excellence-certificate',
            'title_ar' => 'شهادة التميز في خدمة الضيافة',
            'title_en' => 'Hospitality Service Excellence Certificate',
            'category_id' => $categoryId,
            'field_ids' => [16],
            'price_online' => null,
            'price_onsite' => 1650,
            'delivery_type' => 'onsite',
            'duration_hours' => 24,
            'duration_days' => 3,
            'duration_label' => '3 أيام حضورية / 24 ساعة',
            'city' => 'الرياض',
            'is_featured' => false,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'شهادة حضورية في جودة خدمة الضيافة: الاستقبال، معالجة الشكوى، ومعايير التجربة.',
            'meta_en' => 'An on-campus certificate in hospitality service quality: reception, complaint handling, and experience standards.',
            'brief_ar' => [
                'برنامج حضوري يركّز على سلوك الخدمة في الفنادق والمرافق السياحية وقاعات الضيافة، من لحظة الاستقبال حتى وداع الضيف.',
                'يعتمد على تمثيل أدوار وملاحظة معايير الخدمة أكثر من المحاضرة النظرية.',
            ],
            'brief_en' => [
                'An on-campus program focused on service behavior in hotels, visitor facilities, and hospitality halls, from greeting to farewell.',
                'It relies on role-play and service standards more than theoretical lecturing.',
            ],
            'goals_ar' => ['تطبيق معايير الاستقبال', 'معالجة الشكوى بهدوء', 'العمل ضمن فريق ورديات', 'رفع تقييم تجربة الضيف'],
            'goals_en' => ['Apply greeting standards', 'Handle complaints calmly', 'Work within shift teams', 'Raise the guest-experience score'],
            'audience_ar' => ['موظفو الاستقبال والخدمة', 'مشرفو قاعات ومرافق', 'منسقو الفعاليات'],
            'audience_en' => ['Front-desk and service staff', 'Hall and facility supervisors', 'Event coordinators'],
            'features_ar' => ['تدريب حضوري في الرياض', 'سيناريوهات ضيف صعب', 'بطاقة معايير خدمة يومية'],
            'features_en' => ['On-campus training in Riyadh', 'Difficult-guest scenarios', 'A daily service-standards card'],
            'topics_ar' => ['ثقافة الضيافة', 'التواصل غير اللفظي', 'معالجة الاعتراض', 'العمل الجماعي في الوردية', 'قياس الرضا'],
            'topics_en' => ['Hospitality culture', 'Non-verbal communication', 'Handling objections', 'Shift teamwork', 'Measuring satisfaction'],
            'outcomes_ar' => ['دليل خدمة مختصر للقسم', 'نموذج اعتذار ومعالجة', 'مؤشر رضا أسبوعي بسيط'],
            'outcomes_en' => ['A short departmental service guide', 'An apology and recovery form', 'A simple weekly satisfaction indicator'],
            'conditions_ar' => ['الحضور الشخصي في الرياض', 'ملابس مهنية لأيام التمثيل'],
            'conditions_en' => ['In-person attendance in Riyadh', 'Professional attire for role-play days'],
            'faq_ar' => [
                ['هل يوجد خيار عن بعد؟', 'هذا المسار حضوري لضمان التمرين العملي على مواقف الخدمة.'],
                ['هل يناسب قطاع الفعاليات؟', 'نعم، المعايير قابلة للتطبيق في قاعات المؤتمرات والضيافة المؤسسية.'],
            ],
            'faq_en' => [
                ['Is there an online option?', 'This track is on campus to ensure practical service drills.'],
                ['Does it suit the events sector?', 'Yes. The standards apply to conference halls and corporate hospitality.'],
            ],
            'modules' => [
                $this->module('HO1', 'ثقافة الخدمة', 'Service culture', ['وعد العلامة', 'الانطباع الأول', 'لغة الجسد'], ['Brand promise', 'First impression', 'Body language']),
                $this->module('HO2', 'مسار الضيف', 'The guest journey', ['الاستقبال', 'تلبية الطلب', 'المتابعة'], ['Greeting', 'Fulfilling the request', 'Follow-up']),
                $this->module('HO3', 'معالجة الشكوى', 'Complaint handling', ['الاستماع', 'الاعتذار', 'التعويض المناسب'], ['Listening', 'Apology', 'Proportionate recovery']),
                $this->module('HO4', 'فريق الوردية', 'The shift team', ['تسليم المناوبة', 'معايير النظافة والمظهر', 'قياس الرضا'], ['Handover', 'Grooming and cleanliness standards', 'Measuring satisfaction']),
            ],
        ]);
    }

    protected function certificateDataAnalytics(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'business-data-analytics-certificate',
            'title_ar' => 'شهادة تحليل البيانات للأعمال',
            'title_en' => 'Business Data Analytics Certificate',
            'category_id' => $categoryId,
            'field_ids' => [10],
            'price_online' => 1450,
            'price_onsite' => null,
            'delivery_type' => 'online',
            'duration_hours' => 40,
            'duration_days' => null,
            'duration_label' => 'تعلّم ذاتي / نحو 40 ساعة',
            'is_self_learning' => true,
            'is_featured' => false,
            'kind_ar' => 'شهادة احترافية',
            'kind_en' => 'professional certificate',
            'meta_ar' => 'مسار تعلم ذاتي لتحليل بيانات الأعمال: تنظيف البيانات، الجداول المحورية، ولوحات المتابعة.',
            'meta_en' => 'A self-paced track in business data analysis: cleaning data, pivot tables, and dashboards.',
            'brief_ar' => [
                'مسار مرن عن بعد يمكّن غير المختصين من تحويل جداول العمل إلى رؤى واضحة للإدارة، دون اشتراط خلفية إحصائية متقدمة.',
                'يتقدم المشارك وفق سرعته مع وحدات قصيرة وتمارين ملفات حقيقية مبسّطة.',
            ],
            'brief_en' => [
                'A flexible online track that helps non-specialists turn workplace spreadsheets into clear management insight, without advanced statistics.',
                'Learners progress at their own pace through short units and simplified real-world files.',
            ],
            'goals_ar' => ['تنظيف مجموعة بيانات', 'بناء جدول محوري', 'تصميم لوحة متابعة بسيطة', 'سرد قصة رقمية للإدارة'],
            'goals_en' => ['Clean a dataset', 'Build a pivot table', 'Design a simple dashboard', 'Tell a numeric story for management'],
            'audience_ar' => ['منسقو التقارير', 'موظفو العمليات والجودة', 'خريجون يرغبون بمهارة تحليل أساسية'],
            'audience_en' => ['Reporting coordinators', 'Operations and quality staff', 'Graduates seeking a core analytics skill'],
            'features_ar' => ['تعلم ذاتي بالكامل', 'ملفات تمرين قابلة للتحميل', 'اختبار نهائي قصير لإصدار الشهادة'],
            'features_en' => ['Fully self-paced', 'Downloadable exercise files', 'A short final test to issue the certificate'],
            'topics_ar' => ['جودة البيانات', 'الجداول المحورية', 'الرسوم البيانية', 'مؤشرات الأداء', 'عرض النتائج'],
            'topics_en' => ['Data quality', 'Pivot tables', 'Charts', 'Performance indicators', 'Presenting results'],
            'outcomes_ar' => ['ملف بيانات منظّم', 'لوحة من ثلاثة مؤشرات', 'عرض تنفيذي من شريحتين'],
            'outcomes_en' => ['A cleaned data file', 'A three-metric dashboard', 'A two-slide executive brief'],
            'conditions_ar' => ['برنامج جداول ممتدة', 'التزام شخصي بإنجاز الوحدات'],
            'conditions_en' => ['A spreadsheet application', 'Personal commitment to complete the units'],
            'faq_ar' => [
                ['كم المدة المتاحة للإنجاز؟', 'يُفتح المحتوى لمدة كافية لإكماله وفق إرشادات التسجيل المعلنة لكل دفعة.'],
                ['هل يوجد بث مباشر؟', 'لا، المسار ذاتي مع دعم عبر قنوات المنصة.'],
            ],
            'faq_en' => [
                ['How long do I have to finish?', 'Content remains open long enough to complete it, according to the published rules for each intake.'],
                ['Are there live sessions?', 'No. The track is self-paced with support through the platform channels.'],
            ],
            'modules' => [
                $this->module('DA1', 'البيانات وجودتها', 'Data and quality', ['أنواع البيانات', 'القيم المفقودة', 'التكرارات'], ['Data types', 'Missing values', 'Duplicates']),
                $this->module('DA2', 'التلخيص', 'Summarizing', ['الفرز والتصفية', 'الجدول المحوري', 'المقاييس الشائعة'], ['Sort and filter', 'Pivot tables', 'Common measures']),
                $this->module('DA3', 'العرض', 'Visualization', ['اختيار الرسم', 'تجنب التضليل', 'لوحة المتابعة'], ['Choosing a chart', 'Avoiding misleading visuals', 'The dashboard']),
                $this->module('DA4', 'القصة الإدارية', 'The management story', ['السؤال قبل الرقم', 'التوصية', 'حدود التحليل'], ['The question before the number', 'The recommendation', 'Limits of the analysis']),
            ],
        ]);
    }

    protected function diplomaHumanResources(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'hr-management-diploma',
            'title_ar' => 'دبلوم إدارة الموارد البشرية',
            'title_en' => 'Diploma in Human Resource Management',
            'category_id' => $categoryId,
            'field_ids' => [13],
            'price_online' => 9800,
            'price_onsite' => 11800,
            'delivery_type' => 'both',
            'duration_hours' => 320,
            'duration_days' => null,
            'duration_label' => '9 أشهر',
            'is_featured' => true,
            'kind_ar' => 'دبلوم',
            'kind_en' => 'diploma',
            'meta_ar' => 'دبلوم أكاديمي تطبيقي في إدارة الموارد البشرية على مدى تسعة أشهر، مع مشروع تخرج مرتبط بجهة العمل أو حالة دراسية.',
            'meta_en' => 'An applied academic diploma in human resource management over nine months, with a capstone linked to the workplace or a case study.',
            'brief_ar' => [
                'مسار ممتد يعمّق ممارسات الاستقطاب، التعويضات، التطوير التنظيمي، وتحليل بيانات الموارد البشرية، ضمن إطار أكاديمي واضح.',
                'يتخلل الدبلوم واجبات تطبيقية ومشروع ختامي يناقش أمام لجنة أكاديمية من المركز.',
            ],
            'brief_en' => [
                'An extended track that deepens attraction, compensation, organizational development, and HR analytics within a clear academic frame.',
                'The diploma includes applied assignments and a capstone discussed before an academic committee at the Center.',
            ],
            'goals_ar' => ['تصميم نظام موارد بشرية مبسّط لجهة متوسطة', 'ربط سياسات التعويض بالأداء', 'قيادة مبادرة تطوير تنظيمي صغيرة', 'إعداد مشروع تخرج موثّق'],
            'goals_en' => ['Design a simplified HR system for a mid-sized entity', 'Link compensation policy to performance', 'Lead a small OD initiative', 'Prepare a documented capstone'],
            'audience_ar' => ['أخصائيو موارد بشرية يسعون لمسار أطول', 'مشرفون ينتقلون إلى وظائف شريك أعمال', 'خريجو تخصصات إدارية'],
            'audience_en' => ['HR specialists seeking a longer track', 'Supervisors moving into HRBP roles', 'Graduates of management disciplines'],
            'features_ar' => ['إشراف أكاديمي على المشروع', 'لقاءات دورية عن بعد أو حضورية', 'شهادة دبلوم بعد استيفاء المتطلبات'],
            'features_en' => ['Academic supervision of the project', 'Periodic online or on-campus sessions', 'A diploma certificate after requirements are met'],
            'topics_ar' => ['إدارة المواهب', 'التعويضات والمزايا', 'قانون العمل التطبيقي', 'التطوير التنظيمي', 'تحليلات الموارد البشرية', 'مشروع التخرج'],
            'topics_en' => ['Talent management', 'Compensation and benefits', 'Applied labor rules', 'Organizational development', 'HR analytics', 'Capstone project'],
            'outcomes_ar' => ['ملف سياسات', 'دراسة حالة تعويضات', 'مشروع تخرج مع توصيات'],
            'outcomes_en' => ['A policy file', 'A compensation case', 'A capstone with recommendations'],
            'conditions_ar' => ['شهادة ثانوية على الأقل', 'القدرة على الالتزام بعبء دراسي ممتد', 'جهاز واتصال للتعلم عن بعد عند اختياره'],
            'conditions_en' => ['Secondary certificate as a minimum', 'Ability to sustain an extended study load', 'A device and connection if studying online'],
            'faq_ar' => [
                ['هل يمكن التقسيط؟', 'يتاح التقسيط وفق الخطط المعتمدة في المنصة عند فتح التسجيل.'],
                ['هل يعادل درجة جامعية؟', 'الدبلوم برنامج تطوير مهني أكاديمي تطبيقي، وليس بديلاً عن الدرجة الجامعية النظامية.'],
            ],
            'faq_en' => [
                ['Are installments available?', 'Installments may be offered according to approved platform plans when registration opens.'],
                ['Is it equivalent to a university degree?', 'The diploma is an applied professional-academic program, not a substitute for a formal university degree.'],
            ],
            'modules' => [
                $this->module('DHR1', 'أسس إدارة الموارد البشرية', 'HR management foundations', ['الهيكل والأدوار', 'التخطيط للقوى العاملة', 'الجدارات'], ['Structure and roles', 'Workforce planning', 'Competencies']),
                $this->module('DHR2', 'الاستقطاب والتعويض', 'Attraction and compensation', ['رحلة التوظيف', 'هيكل الرواتب', 'المزايا'], ['The hiring journey', 'Salary structure', 'Benefits']),
                $this->module('DHR3', 'التطوير والأداء', 'Development and performance', ['تقييم الأداء', 'مسارات وظيفية', 'التعلم المؤسسي'], ['Performance appraisal', 'Career paths', 'Organizational learning']),
                $this->module('DHR4', 'المشروع الختامي', 'Capstone', ['اختيار المشكلة', 'جمع الأدلة', 'العرض والتوصية'], ['Selecting the problem', 'Gathering evidence', 'Presentation and recommendation']),
            ],
        ]);
    }

    protected function diplomaProjectManagement(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'professional-project-management-diploma',
            'title_ar' => 'دبلوم إدارة المشاريع الاحترافية',
            'title_en' => 'Diploma in Professional Project Management',
            'category_id' => $categoryId,
            'field_ids' => [8],
            'price_online' => 10500,
            'price_onsite' => 12500,
            'delivery_type' => 'both',
            'duration_hours' => 340,
            'duration_label' => '9 أشهر',
            'is_featured' => true,
            'kind_ar' => 'دبلوم',
            'kind_en' => 'diploma',
            'meta_ar' => 'دبلوم في إدارة المحافظ والمشاريع مع تطبيقات الجدولة، المشتريات، والحوكمة.',
            'meta_en' => 'A diploma in portfolio and project management with applications in scheduling, procurement, and governance.',
            'brief_ar' => [
                'يمتد الدبلوم لتأهيل مديري مشاريع قادرين على قيادة مبادرات متعددة، لا مشروعاً واحداً قصيراً فقط.',
                'يشمل أدوات جدولة، إدارة موردين، وتقارير حوكمة للإدارة العليا، مع مشروع تخرج على مبادرة افتراضية أو حقيقية.',
            ],
            'brief_en' => [
                'The diploma prepares project managers to lead multiple initiatives, not only a single short project.',
                'It includes scheduling tools, supplier management, and governance reporting for senior management, with a capstone on a real or simulated initiative.',
            ],
            'goals_ar' => ['إدارة برنامج من عدة مشاريع', 'بناء حوكمة تقارير شهرية', 'ضبط المشتريات والتعاقدات الأساسية', 'إغلاق محفظة بدروس موثّقة'],
            'goals_en' => ['Manage a program of several projects', 'Build monthly governance reporting', 'Control basic procurement and contracting', 'Close a portfolio with documented lessons'],
            'audience_ar' => ['مديرو مشاريع ذوو خبرة أولية', 'مهندسو تخطيط', 'مسؤولو مكاتب إدارة المشاريع'],
            'audience_en' => ['Project managers with early experience', 'Planning engineers', 'PMO officers'],
            'features_ar' => ['محاكاة مكتب إدارة مشاريع', 'قوالب حوكمة', 'مشروع تخرج مراقب'],
            'features_en' => ['A PMO simulation', 'Governance templates', 'A supervised capstone'],
            'topics_ar' => ['إدارة البرامج', 'الجدولة المتقدمة', 'المشتريات', 'الحوكمة', 'إدارة أصحاب المصلحة', 'المشروع الختامي'],
            'topics_en' => ['Program management', 'Advanced scheduling', 'Procurement', 'Governance', 'Stakeholder management', 'Capstone'],
            'outcomes_ar' => ['دليل حوكمة مبسّط', 'خطة برنامج', 'تقرير إغلاق'],
            'outcomes_en' => ['A simplified governance guide', 'A program plan', 'A closure report'],
            'conditions_ar' => ['شهادة ثانوية على الأقل', 'يفضّل خبرة في بيئة مشاريع'],
            'conditions_en' => ['Secondary certificate as a minimum', 'Project-environment experience is preferred'],
            'faq_ar' => [
                ['ما الفرق عن الشهادة القصيرة؟', 'الدبلوم أطول وأعمق ويشمل مشروعاً ختامياً وحوكمة برامج، لا دورة مكثفة لأيام معدودة.'],
                ['هل توجد جدولة مسائية؟', 'تُعلن مواعيد اللقاءات عند فتح كل دفعة.'],
            ],
            'faq_en' => [
                ['How is it different from the short certificate?', 'The diploma is longer and deeper, with a capstone and program governance, not a few intensive days.'],
                ['Are there evening sessions?', 'Session times are announced when each intake opens.'],
            ],
            'modules' => [
                $this->module('DPM1', 'من المشروع إلى البرنامج', 'From project to program', ['الفروقات', 'اختيار المبادرات', 'مكتب الإدارة'], ['Differences', 'Selecting initiatives', 'The management office']),
                $this->module('DPM2', 'التخطيط المتقدم', 'Advanced planning', ['الاعتماديات', 'الموارد المشتركة', 'السيناريوهات'], ['Dependencies', 'Shared resources', 'Scenarios']),
                $this->module('DPM3', 'المشتريات والحوكمة', 'Procurement and governance', ['نطاق التوريد', 'مؤشرات اللجنة', 'التدقيق الداخلي المبسط'], ['Supply scope', 'Committee indicators', 'Simplified internal audit']),
                $this->module('DPM4', 'المشروع الختامي', 'Capstone', ['ميثاق البرنامج', 'لوحة المتابعة', 'العرض النهائي'], ['Program charter', 'Dashboard', 'Final presentation']),
            ],
        ]);
    }

    protected function diplomaInformationTechnology(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'information-technology-diploma',
            'title_ar' => 'دبلوم تقنية المعلومات',
            'title_en' => 'Diploma in Information Technology',
            'category_id' => $categoryId,
            'field_ids' => [10],
            'price_online' => 11200,
            'price_onsite' => null,
            'delivery_type' => 'online',
            'duration_hours' => 380,
            'duration_label' => '12 شهراً',
            'is_featured' => true,
            'kind_ar' => 'دبلوم',
            'kind_en' => 'diploma',
            'meta_ar' => 'دبلوم عن بعد في أساسيات تقنية المعلومات: الشبكات، الدعم الفني، قواعد البيانات، وأمن المعلومات التطبيقي.',
            'meta_en' => 'An online diploma in IT fundamentals: networks, technical support, databases, and applied information security.',
            'brief_ar' => [
                'مسار سنوي عن بعد يبني فنيّاً قادراً على دعم المستخدم، فهم الشبكات المحلية، والتعامل مع البيانات بأمان.',
                'المحتوى متدرج من المفاهيم إلى مختبرات افتراضية وواجبات أسبوعية، مع مشروع تخرج لخدمة تقنية داخل جهة افتراضية.',
            ],
            'brief_en' => [
                'A year-long online track that builds a technician able to support users, understand local networks, and handle data safely.',
                'Content progresses from concepts to virtual labs and weekly assignments, with a capstone for an IT service in a simulated entity.',
            ],
            'goals_ar' => ['تشغيل دعم فني وفق تذاكر', 'ضبط شبكة صغيرة', 'الاستعلام الأساسي من قاعدة بيانات', 'تطبيق ضوابط حماية أولية'],
            'goals_en' => ['Run ticket-based technical support', 'Configure a small network', 'Perform basic database queries', 'Apply initial protection controls'],
            'audience_ar' => ['خريجو ثانوية يتجهون لمسار تقني', 'موظفو دعم مبتدئون', 'محوّلون من تخصصات أخرى'],
            'audience_en' => ['Secondary graduates entering a technical path', 'Junior support staff', 'Career changers from other fields'],
            'features_ar' => ['مختبرات افتراضية', 'عبء أسبوعي واضح', 'مشروع تخرج تقني'],
            'features_en' => ['Virtual labs', 'A clear weekly workload', 'A technical capstone'],
            'topics_ar' => ['أساسيات الحوسبة', 'الشبكات', 'نظم التشغيل', 'قواعد البيانات', 'الأمن التطبيقي', 'مشروع التخرج'],
            'topics_en' => ['Computing basics', 'Networks', 'Operating systems', 'Databases', 'Applied security', 'Capstone'],
            'outcomes_ar' => ['دليل دعم للمستخدم', 'مخطط شبكة صغيرة', 'نموذج بيانات مبسّط'],
            'outcomes_en' => ['An end-user support guide', 'A small-network diagram', 'A simplified data model'],
            'conditions_ar' => ['جهاز بمواصفات مناسبة للمختبرات', 'التزام أسبوعي بعدة ساعات'],
            'conditions_en' => ['A device suitable for labs', 'A weekly time commitment of several hours'],
            'faq_ar' => [
                ['هل أحتاج خلفية برمجية؟', 'لا في البداية. البرمجة تُقدَّم لاحقاً بمستوى تمهيدي مرتبط بقواعد البيانات.'],
                ['هل الدبلوم عن بعد بالكامل؟', 'نعم، بما في ذلك اللقاءات الافتراضية والاختبارات وفق سياسة الدفعة.'],
            ],
            'faq_en' => [
                ['Do I need a programming background?', 'Not at the start. Programming is introduced later at a beginner level linked to databases.'],
                ['Is the diploma fully online?', 'Yes, including virtual sessions and assessments according to the intake policy.'],
            ],
            'modules' => [
                $this->module('DIT1', 'أسس الحوسبة والدعم', 'Computing and support foundations', ['مكونات الجهاز', 'نظام التذاكر', 'توثيق الحل'], ['Device components', 'Ticketing', 'Documenting the fix']),
                $this->module('DIT2', 'الشبكات ونظم التشغيل', 'Networks and operating systems', ['عناوين الشبكة', 'المشاركة والصلاحيات', 'النسخ الاحتياطي'], ['Network addressing', 'Sharing and permissions', 'Backup']),
                $this->module('DIT3', 'البيانات والحماية', 'Data and protection', ['الجداول والعلاقات', 'الاستعلام', 'صلاحيات الوصول'], ['Tables and relations', 'Querying', 'Access rights']),
                $this->module('DIT4', 'مشروع التخرج', 'Capstone', ['تحليل الاحتياج', 'تصميم الخدمة', 'التسليم والتوثيق'], ['Needs analysis', 'Service design', 'Handover and documentation']),
            ],
        ]);
    }

    protected function diplomaDigitalMarketing(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'digital-marketing-and-ecommerce-diploma',
            'title_ar' => 'دبلوم التسويق الرقمي والأعمال الإلكترونية',
            'title_en' => 'Diploma in Digital Marketing and E-Commerce',
            'category_id' => $categoryId,
            'field_ids' => [11],
            'price_online' => 9200,
            'price_onsite' => 10800,
            'delivery_type' => 'both',
            'duration_hours' => 300,
            'duration_label' => '9 أشهر',
            'is_featured' => false,
            'kind_ar' => 'دبلوم',
            'kind_en' => 'diploma',
            'meta_ar' => 'دبلوم يجمع التسويق الرقمي مع أساسيات المتاجر الإلكترونية وتحليل رحلة الشراء.',
            'meta_en' => 'A diploma combining digital marketing with e-commerce fundamentals and the purchase journey.',
            'brief_ar' => [
                'يمتد البرنامج لبناء قدرة تشغيل قنوات رقمية ومتجر إلكتروني مصغّر، من المحتوى إلى الدفع والقياس.',
                'يعمل المشارك على مشروع تخرج لمتجر أو حملة متكاملة لجهة افتراضية أو مشروعه القائم.',
            ],
            'brief_en' => [
                'The program builds the ability to operate digital channels and a small e-commerce store, from content to checkout and measurement.',
                'Participants work on a capstone for a store or an integrated campaign for a simulated entity or their own venture.',
            ],
            'goals_ar' => ['تشغيل تقويم محتوى ربع سنوي', 'ضبط مسار الشراء في متجر تجريبي', 'قياس تكلفة الاكتساب', 'تقديم مشروع تخرج قابل للتنفيذ'],
            'goals_en' => ['Run a quarterly content calendar', 'Tune the purchase path in a demo store', 'Measure acquisition cost', 'Present an executable capstone'],
            'audience_ar' => ['مسؤولو التسويق في التجارة الإلكترونية', 'أصحاب المتاجر الناشئة', 'منسقو النمو الرقمي'],
            'audience_en' => ['E-commerce marketing officers', 'Owners of emerging stores', 'Digital-growth coordinators'],
            'features_ar' => ['مشروع متجر أو حملة', 'تحليلات عملية', 'مراجعات دورية مع المشرف'],
            'features_en' => ['A store or campaign project', 'Practical analytics', 'Periodic supervisor reviews'],
            'topics_ar' => ['سلوك المشتري الرقمي', 'المحتوى والعلامات', 'الإعلان', 'المتاجر الإلكترونية', 'خدمة ما بعد البيع', 'المشروع'],
            'topics_en' => ['Digital buyer behavior', 'Content and branding', 'Advertising', 'Online stores', 'After-sales service', 'Capstone'],
            'outcomes_ar' => ['خطة قنوات لربع سنة', 'خريطة مسار الشراء', 'لوحة مؤشرات للمتجر'],
            'outcomes_en' => ['A quarterly channel plan', 'A purchase-path map', 'A store metrics dashboard'],
            'conditions_ar' => ['إلمام بمنصات التواصل', 'استعداد للعمل على مشروع طوال الفصل الأخير'],
            'conditions_en' => ['Familiarity with social platforms', 'Willingness to work on a project throughout the final term'],
            'faq_ar' => [
                ['هل أحتاج متجراً قائماً؟', 'لا، يمكن العمل على متجر تجريبي ضمن بيئة التدريب.'],
                ['هل الشهادة القصيرة كافية بدلاً منه؟', 'الشهادة القصيرة مقدّمة. الدبلوم مناسب لمن يريد تشغيل القنوات بشكل ممتد.'],
            ],
            'faq_en' => [
                ['Do I need an existing store?', 'No. You can work on a training store in the learning environment.'],
                ['Is the short certificate enough instead?', 'The short certificate is an introduction. The diploma suits those who want to operate channels over a longer period.'],
            ],
            'modules' => [
                $this->module('DDM1', 'العميل والقيمة', 'Customer and value', ['شرائح السوق', 'الرسالة', 'العرض'], ['Segments', 'Message', 'Offer']),
                $this->module('DDM2', 'القنوات والحملات', 'Channels and campaigns', ['المحتوى', 'الإعلان', 'البريد والتطبيقات'], ['Content', 'Advertising', 'Email and apps']),
                $this->module('DDM3', 'المتجر الإلكتروني', 'The online store', ['الكتالوج', 'الدفع والشحن', 'التخلي عن السلة'], ['Catalog', 'Payment and shipping', 'Cart abandonment']),
                $this->module('DDM4', 'المشروع الختامي', 'Capstone', ['الفرضية', 'التنفيذ', 'قياس الأثر'], ['Hypothesis', 'Execution', 'Impact measurement']),
            ],
        ]);
    }

    protected function diplomaAccounting(int $categoryId): array
    {
        return $this->makeProgram([
            'slug' => 'accounting-and-finance-diploma',
            'title_ar' => 'دبلوم المحاسبة والمالية',
            'title_en' => 'Diploma in Accounting and Finance',
            'category_id' => $categoryId,
            'field_ids' => [14],
            'price_online' => null,
            'price_onsite' => 12800,
            'delivery_type' => 'onsite',
            'duration_hours' => 360,
            'duration_label' => '12 شهراً',
            'city' => 'الرياض',
            'is_featured' => false,
            'kind_ar' => 'دبلوم',
            'kind_en' => 'diploma',
            'meta_ar' => 'دبلوم حضوري في المحاسبة المالية، التكاليف، والموازنة لدعم الوظائف المالية في المنشآت.',
            'meta_en' => 'An on-campus diploma in financial accounting, costing, and budgeting to support finance roles in organizations.',
            'brief_ar' => [
                'مسار سنوي حضوري في الرياض يبني محاسباً قادراً على التسجيل والتسويات وإعداد تقارير للإدارة، مع مقدمة في التكاليف والموازنة.',
                'يشمل تمارين دفترية ومشروع تخرج على دورة محاسبية كاملة لمنشأة تدريبية.',
            ],
            'brief_en' => [
                'A year-long on-campus track in Riyadh that builds an accountant able to record, adjust, and report to management, with an introduction to costing and budgeting.',
                'It includes bookkeeping drills and a capstone covering a full accounting cycle for a training entity.',
            ],
            'goals_ar' => ['إكمال دورة محاسبية مبسطة', 'إعداد ميزان مراجعة', 'المساهمة في موازنة تشغيلية', 'تقديم تقرير ختامي واضح'],
            'goals_en' => ['Complete a simplified accounting cycle', 'Prepare a trial balance', 'Contribute to an operating budget', 'Present a clear closing report'],
            'audience_ar' => ['موظفون ماليون في بداية المسار', 'خريجو تخصصات غير محاسبية ينتقلون للإدارة المالية', 'مساعدو محاسبين'],
            'audience_en' => ['Early-career finance staff', 'Graduates from non-accounting fields moving into finance', 'Accounting assistants'],
            'features_ar' => ['تدريب حضوري', 'دفاتر تمارين', 'مشروع دورة محاسبية'],
            'features_en' => ['On-campus training', 'Exercise ledgers', 'An accounting-cycle project'],
            'topics_ar' => ['المبادئ المحاسبية', 'القيود والتسويات', 'المحاسبة على التكاليف', 'الموازنة', 'التقارير', 'مشروع التخرج'],
            'topics_en' => ['Accounting principles', 'Entries and adjustments', 'Cost accounting', 'Budgeting', 'Reporting', 'Capstone'],
            'outcomes_ar' => ['مجموعة قيود نموذجية', 'ميزان مراجعة', 'موازنة مبسطة'],
            'outcomes_en' => ['A sample journal set', 'A trial balance', 'A simplified budget'],
            'conditions_ar' => ['الحضور في الرياض', 'أساسيات الحساب والجداول'],
            'conditions_en' => ['Attendance in Riyadh', 'Basic numeracy and spreadsheets'],
            'faq_ar' => [
                ['هل يؤهل لمزاولة مهنة محاسب قانوني؟', 'لا، الدبلوم تأهيل تطبيقي وظيفي وليس ترخيص مزاولة مهنية.'],
                ['هل يمكن الحضور الجزئي عن بعد؟', 'هذا المسار حضوري لضمان التمارين الدفترية والمتابعة الصفية.'],
            ],
            'faq_en' => [
                ['Does it qualify me as a licensed accountant?', 'No. The diploma is applied occupational training, not a professional license.'],
                ['Can part of it be online?', 'This track is on campus to support ledger drills and classroom follow-up.'],
            ],
            'modules' => [
                $this->module('DAC1', 'أسس المحاسبة', 'Accounting foundations', ['المعادلة المحاسبية', 'الدفاتر', 'المستندات'], ['The accounting equation', 'Books', 'Source documents']),
                $this->module('DAC2', 'التسويات والتقارير', 'Adjustments and reports', ['التسويات الجردية', 'ميزان المراجعة', 'القوائم'], ['Period-end adjustments', 'Trial balance', 'Statements']),
                $this->module('DAC3', 'التكاليف والموازنة', 'Costing and budgeting', ['تصنيف التكاليف', 'نقطة التعادل', 'الموازنة التشغيلية'], ['Cost classification', 'Break-even', 'Operating budget']),
                $this->module('DAC4', 'مشروع التخرج', 'Capstone', ['دورة كاملة', 'التحليل المبسّط', 'العرض للإدارة'], ['Full cycle', 'Simplified analysis', 'Presentation to management']),
            ],
        ]);
    }

    /** @param  array<string, mixed>  $definition */
    protected function makeProgram(array $definition): array
    {
        $definition['slug'] = CatalogSlugResolver::normalizeSlug($definition['slug']);

        return $definition;
    }

    /**
     * @param  list<string>  $lessonsAr
     * @param  list<string>  $lessonsEn
     * @return array<string, mixed>
     */
    protected function module(string $code, string $titleAr, string $titleEn, array $lessonsAr, array $lessonsEn): array
    {
        $lessons = [];

        foreach ($lessonsAr as $index => $lessonAr) {
            $lessonEn = $lessonsEn[$index] ?? $lessonAr;
            $lessons[] = [
                'title_ar' => $lessonAr,
                'title_en' => $lessonEn,
                'minutes' => 40 + ($index * 10),
                'body_ar' => $this->lessonBody($titleAr, $lessonAr, 'ar'),
                'body_en' => $this->lessonBody($titleEn, $lessonEn, 'en'),
            ];
        }

        return [
            'code' => $code,
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'summary_ar' => 'وحدة تدريبية ضمن برنامج مركز التعلم المستمر تغطي: '.implode('، ', $lessonsAr).'.',
            'summary_en' => 'A Continuing Learning Center unit covering: '.implode(', ', $lessonsEn).'.',
            'minutes' => 45 * count($lessons),
            'objectives_ar' => array_map(fn (string $item) => 'التمكن من موضوع: '.$item, $lessonsAr),
            'objectives_en' => array_map(fn (string $item) => 'Be able to apply: '.$item, $lessonsEn),
            'lessons' => $lessons,
        ];
    }

    protected function lessonBody(string $moduleTitle, string $lessonTitle, string $locale): string
    {
        if ($locale === 'en') {
            return $this->htmlParagraphs([
                '<strong>'.e($lessonTitle).'</strong> is part of the unit <em>'.e($moduleTitle).'</em> at the Continuing Learning Center of Arab Open University — Saudi Arabia.',
                'The lesson explains the concept, shows a workplace example, and ends with a short practice task you can apply in your role.',
                'Complete the reading, note two actions you will take, and use the unit discussion to compare your approach with peers.',
            ]).$this->htmlList([
                'Key idea and why it matters at work',
                'A short case from a professional setting',
                'A practice task before you continue',
            ]);
        }

        return $this->htmlParagraphs([
            'يتناول درس <strong>'.e($lessonTitle).'</strong> ضمن وحدة <em>'.e($moduleTitle).'</em> في مركز التعلم المستمر بالجامعة العربية المفتوحة — المملكة العربية السعودية.',
            'يشرح الدرس المفهوم، ويعرض مثالاً من بيئة العمل، ويُختتم بمهمة تطبيقية قصيرة يمكن تنفيذها في موقعك الوظيفي.',
            'أكمل القراءة، سجّل إجراءين ستطبقهما، ثم استخدم نقاش الوحدة لمقارنة أسلوبك مع الزملاء.',
        ]).$this->htmlList([
            'الفكرة الأساسية وأهميتها في العمل',
            'حالة قصيرة من بيئة مهنية',
            'مهمة تطبيق قبل الانتقال للدرس التالي',
        ]);
    }

    /** @param  array<string, mixed>  $definition */
    protected function articleHtml(array $definition, string $locale): string
    {
        if ($locale === 'en') {
            return $this->htmlParagraphs([
                e($definition['title_en']).' is a '.$definition['kind_en'].' offered by the Continuing Learning Center at Arab Open University — Saudi Arabia.',
                'The program is designed around labor-market practice: clear outcomes, applied tasks, and a completion path that can be verified after requirements are met.',
                'Learners who finish the track leave with usable tools—not only attendance—aligned with the Center’s academic follow-up model.',
            ]);
        }

        return $this->htmlParagraphs([
            e($definition['title_ar']).' '.$definition['kind_ar'].' يقدّمه مركز التعلم المستمر في الجامعة العربية المفتوحة — المملكة العربية السعودية.',
            'صُمم البرنامج حول الممارسة في سوق العمل: مخرجات واضحة، مهام تطبيقية، ومسار إتمام يمكن التحقق منه بعد استيفاء المتطلبات.',
            'يخرج المتعلم بأدوات قابلة للاستخدام، لا بحضور فقط، ضمن نموذج المتابعة الأكاديمية في المركز.',
        ]);
    }

    /** @param  list<string>  $items */
    protected function htmlList(array $items): string
    {
        $lis = collect($items)
            ->filter(fn ($item) => filled($item))
            ->map(fn ($item) => '<li>'.e((string) $item).'</li>')
            ->implode('');

        return $lis === '' ? '' : '<ul>'.$lis.'</ul>';
    }

    /** @param  list<string>  $paragraphs */
    protected function htmlParagraphs(array $paragraphs): string
    {
        return collect($paragraphs)
            ->filter(fn ($item) => filled($item))
            ->map(function ($item) {
                $text = (string) $item;

                return str_contains($text, '<')
                    ? '<p>'.$text.'</p>'
                    : '<p>'.e($text).'</p>';
            })
            ->implode('');
    }

    /** @param  list<array{0: string, 1: string}>  $pairs */
    protected function htmlFaq(array $pairs): string
    {
        return collect($pairs)
            ->map(fn (array $pair) => '<h3>'.e($pair[0]).'</h3><p>'.e($pair[1]).'</p>')
            ->implode('');
    }
}
