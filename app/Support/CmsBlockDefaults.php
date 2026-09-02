<?php

namespace App\Support;

class CmsBlockDefaults
{
    /** @return list<array<string, mixed>> */
    public static function home(string $locale = 'ar'): array
    {
        if ($locale === 'en') {
            return self::translateBlocks(self::home('ar'), 'en');
        }

        return [
            self::block('hero', 'hero', true, [
                'title' => '{platform_name}',
                'subtitle_lines' => [
                    'دورات ودبلومات وشهادات احترافية',
                    'مصمَّمة وفق احتياج {platform_org} وسوق العمل',
                ],
                'image' => platform_campus_path('aerial'),
                'showcase_image' => platform_campus_path('entrance'),
                'showcase_video' => '',
                'gallery' => platform_campus_gallery(),
                'search_enabled' => true,
            ]),
            self::block('audiences', 'path_cards', true, [
                'title' => 'مسارات واضحة لكل مستفيد',
                'lead' => 'بوابات مستقلة للمتدربين والشركات والمدربين، ضمن منصة واحدة.',
                'items' => [
                    [
                        'icon' => 'fa-solid fa-user-graduate',
                        'title' => 'المتدربون والأفراد',
                        'body' => 'استكشف البرامج، سجّل مباشرة، وتابع التعلم والشهادات من بوابة المتدرب.',
                        'url' => 'courses.index',
                        'cta_label' => 'تصفح البرامج',
                    ],
                    [
                        'icon' => 'fa-solid fa-building',
                        'title' => 'الشركات والجهات',
                        'body' => 'قدّم طلب تأهيل كوادرك واختر برامج مخصصة وفق احتياج الجهة.',
                        'url' => 'apply/company',
                        'cta_label' => 'تسجيل طلب جهة',
                    ],
                    [
                        'icon' => 'fa-solid fa-chalkboard-user',
                        'title' => 'المدربون والخبراء',
                        'body' => 'انضم إلى الكادر التدريبي وشارك خبرتك ضمن البرامج الأكاديمية والمهنية.',
                        'url' => 'apply/instructor',
                        'cta_label' => 'التقديم كمدرب',
                    ],
                ],
            ]),
            self::block('popular_fields', 'catalog_section', false, [
                'source' => 'popular_fields',
                'anchor_id' => 'section-fields',
            ]),
            self::block('certificates', 'catalog_section', true, [
                'source' => 'certificates',
                'anchor_id' => 'section-certificates',
            ]),
            self::block('diplomas', 'catalog_section', true, [
                'source' => 'diplomas',
                'anchor_id' => 'section-diplomas',
            ]),
            self::block('how_it_works', 'steps_grid', true, [
                'title' => 'كيف تبدأ؟',
                'lead' => 'أربع خطوات واضحة من اختيار البرنامج حتى إصدار الشهادة.',
                'items' => [
                    ['step' => 1, 'title' => 'استكشف البرامج', 'body' => 'اختر المجال والدبلوم أو الشهادة الاحترافية المناسبة لهدفك.'],
                    ['step' => 2, 'title' => 'سجّل والتحق', 'body' => 'أكمل التسجيل أو قدّم طلب الجهة، ثم أكّد المقعد.'],
                    ['step' => 3, 'title' => 'تعلّم مع الخبراء', 'body' => 'احضر الجلسات، سلّم المهام، وتابع تقدمك من بوابة المتدرب.'],
                    ['step' => 4, 'title' => 'احصل على شهادتك', 'body' => 'بعد استيفاء المتطلبات تُصدر شهادتك ويمكن التحقق منها إلكترونياً.'],
                ],
            ]),
            self::block('mission_vision_goals', 'cards_grid', true, [
                'title' => 'من نحن',
                'lead' => 'رسالة {platform_name} ورؤيتها وأهدافها في بناء القدرات وتطوير المهارات.',
                'items' => [
                    [
                        'icon' => 'assets/1853033571057247.png',
                        'title' => 'مهمتنا',
                        'body' => 'تقديم برامج تعليمية وتدريبية مرنة مبنية على احتياج سوق العمل، بالشراكة مع الخبراء والجهات الأكاديمية.',
                    ],
                    [
                        'icon' => 'assets/1853033393294593.png',
                        'title' => 'رؤيتنا',
                        'body' => 'أن نكون المرجع الموثوق للتعليم الاحترافي، وتمكين الأفراد والمؤسسات من اكتساب مهارات المستقبل.',
                    ],
                    [
                        'icon' => 'assets/1853033717546615.png',
                        'title' => 'أهدافنا',
                        'body' => 'تنمية المهارات التطبيقية ودعم التطور المهني عبر محتوى عالي الجودة ومتابعة أكاديمية واضحة.',
                    ],
                ],
            ]),
            self::block('skills_program', 'image_cards', false, [
                'section_id' => 'section-mahara',
                'title' => 'برنامج مهارات',
                'items' => [
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات عامة — باحثون عن عمل'],
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات عامة — موظفون على رأس العمل'],
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات مهنية — باحثون عن عمل'],
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات مهنية — موظفون على رأس العمل'],
                ],
                'cta_label' => 'جميع البرامج',
                'cta_url' => 'courses.index',
            ]),
            self::block('platform_features', 'features_grid', true, [
                'eyebrow' => 'مزايا المنصة',
                'title' => 'لماذا تختار المنصة؟',
                'lead' => 'انتماء أكاديمي واضح، وشهادات يمكن التحقق منها، وتجربة تعلم تربط المعرفة بالممارسة.',
                'items' => [
                    ['icon' => 'fa-solid fa-certificate', 'title' => 'شهادات معتمدة', 'body' => 'شهادات موثوقة تعزز مسارك المهني مع إمكانية التحقق الإلكتروني.'],
                    ['icon' => 'fa-solid fa-layer-group', 'title' => 'تخصصات متنوعة', 'body' => 'برامج تغطي الشهادات الاحترافية والدبلومات والمهارات العملية.'],
                    ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'مدربون محترفون', 'body' => 'خبراء بخبرة تطبيقية وإشراف أكاديمي واضح.'],
                    ['icon' => 'fa-solid fa-display', 'title' => 'تجربة تعليم مرنة', 'body' => 'منصة سهلة للحضور والمحتوى والدعم على مدار رحلتك.'],
                    ['icon' => 'fa-solid fa-lightbulb', 'title' => 'تطبيق عملي مباشر', 'body' => 'محتوى يربط المعرفة بالممارسة عبر مهام وجلسات ومتابعة.'],
                ],
            ]),
            self::block('stats', 'stats', true, [
                'title_prefix' => '',
                'platform_name' => '{platform_name}',
                'title_suffix' => 'في أرقام',
                'items' => [
                    ['label' => 'المتدربون', 'value' => 1000, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'المدربون', 'value' => 43, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'البرامج التدريبية', 'value' => 63, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'رضا المستفيدين', 'value' => 99, 'suffix' => '%', 'icon' => 'isax isax-global5'],
                ],
            ]),
            self::block('partners', 'logo_carousel', true, [
                'title' => 'شركاء النجاح',
                'logos' => [
                    ['image' => 'assets/1853384885027491.png', 'alt' => 'شريك 1'],
                    ['image' => 'assets/1853385108613939.png', 'alt' => 'شريك 2'],
                    ['image' => 'assets/1853384983114238.png', 'alt' => 'شريك 3'],
                ],
            ]),
            self::block('accredited', 'logo_carousel', true, [
                'title' => 'معتمدون لدى',
                'logos' => [
                    ['image' => 'assets/1857913315552753.png', 'alt' => 'جهة اعتماد'],
                    ['image' => 'assets/516e9932-3a38-4c92-a79c-99606a4c6dd9.png', 'alt' => 'اعتماد مهني'],
                ],
            ]),
            self::block('testimonials', 'testimonials', true, [
                'title' => 'آراء المستفيدين',
                'items' => [
                    ['quote' => 'البرامج عملية ومنظمة، وساعدتني على الاستعداد لسوق العمل بثقة أوضح.', 'name' => 'محمد السبيعي', 'role' => 'خريج برنامج مهني', 'avatar' => 'assets/1853038435618862.png', 'rating' => 5],
                    ['quote' => 'تجربة متكاملة من المحتوى إلى الدعم، وأنصح بها كل باحث عن تطوير مهاراته.', 'name' => 'سارة المطيري', 'role' => 'متدربة', 'avatar' => 'assets/1853038521958109.png', 'rating' => 5],
                    ['quote' => 'ساهم البرنامج في تطوير مهاراتي وربطني بفرص مناسبة لمساري المهني.', 'name' => 'خالد الحربي', 'role' => 'موظف على رأس العمل', 'avatar' => 'assets/1853038589759124.png', 'rating' => 5],
                    ['quote' => 'التدريب منظم والمحتوى عالي الجودة، مع متابعة واضحة للحضور والمهام.', 'name' => 'أحمد العتيبي', 'role' => 'متدرب', 'avatar' => 'assets/1853038251279600.png', 'rating' => 5],
                    ['quote' => 'المنصة سهلة الوصول، والمحتوى متوافق مع متطلبات العمل اليومية.', 'name' => 'نورة القحطاني', 'role' => 'خريجة', 'avatar' => 'assets/1853038521958109.png', 'rating' => 5],
                ],
            ]),
            self::block('news', 'news_cards', true, [
                'title' => 'الأخبار والفعاليات',
                'badge' => 'الأخبار',
                'source' => 'latest_articles',
                'limit' => 4,
            ]),
            self::block('faq', 'faq', true, [
                'title' => 'الأسئلة الشائعة',
                'items' => [
                    ['question' => 'ما الذي تقدمه المنصة؟', 'answer' => '{platform_name} منصة تعليمية تقدّم دورات ودبلومات وشهادات احترافية مرنة، بما يتوافق مع احتياج {platform_org} وسوق العمل.'],
                    ['question' => 'من يمكنه الاستفادة؟', 'answer' => 'الأفراد والطلاب والموظفون، إضافة إلى الشركات والجهات الحكومية والخاصة الراغبة في تأهيل كوادرها.'],
                    ['question' => 'هل البرامج عن بُعد أم حضورية؟', 'answer' => 'تُقدَّم البرامج إلكترونياً عن بُعد، مع إمكانية الحضوري أو المدمج حسب طبيعة كل برنامج.'],
                    ['question' => 'كيف أسجّل في برنامج؟', 'answer' => 'تصفح البرامج ثم أكمل التسجيل. الجهات تقدّم طلباً عبر مسار الشركات.'],
                    ['question' => 'هل الشهادات قابلة للتحقق؟', 'answer' => 'نعم. بعد استيفاء المتطلبات تُصدر شهادة يمكن التحقق منها عبر صفحة التحقق في المنصة.'],
                    ['question' => 'كيف أحصل على الدعم؟', 'answer' => 'من صفحة تواصل معنا، أو بفتح تذكرة دعم، أو عبر البريد والجوال المعتمدين.'],
                ],
            ]),
            self::block('closing_cta', 'cta_banner', true, [
                'eyebrow' => '{platform_org}',
                'title' => 'ابدأ رحلتك التعليمية اليوم',
                'body' => 'اختر برنامجك، أكمل التسجيل، وتابع تعلمك حتى إصدار الشهادة.',
                'primary_label' => 'تصفح البرامج',
                'primary_url' => 'courses.index',
                'secondary_label' => 'تواصل معنا',
                'secondary_url' => 'contact',
            ]),
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function about(string $locale = 'ar'): array
    {
        if ($locale === 'en') {
            return self::translateBlocks(self::about('ar'), 'en');
        }

        return [
            self::block('breadcrumb', 'breadcrumb', true, [
                'title' => 'عن المنصة',
                'parent_label' => 'الرئيسية',
                'background_image' => platform_campus_path('aerial'),
            ]),
            self::block('about_intro', 'rich_text_split', true, [
                'eyebrow' => 'تابع لـ {platform_org}',
                'title' => 'نبني القدرات المهنية بمعايير أكاديمية واضحة',
                'image' => platform_campus_path('entrance'),
                'image_badge' => '{platform_name}',
                'paragraphs' => [
                    '{platform_name} منصة التعليم المستمر التابعة لـ {platform_org}. نقدّم برامج تدريبية ودبلومات وشهادات احترافية تُصمَّم وفق احتياج سوق العمل، ضمن بيئة تعليم رقمية منظمة تسهّل التعلم للمتدربين والجهات على حد سواء.',
                    'نعمل مع خبراء وممارسين لردم الفجوة بين المهارات الحالية ومتطلبات المستقبل، عبر محتوى تطبيقي، ومتابعة أكاديمية، ومسارات واضحة من التسجيل حتى إصدار الشهادة.',
                    'سواء كنت فرداً تطوّر مسارك المهني أو جهة تؤهّل كوادرها، تمنحك المنصة تجربة مرنة: حضور إلكتروني، مهام عملية، ودعم مستمر طوال رحلة التعلم.',
                ],
                'highlights' => [
                    'شهادات يمكن التحقق منها إلكترونياً',
                    'برامج مبنية على احتياج سوق العمل',
                    'مسارات مستقلة للمتدربين والجهات والمدربين',
                ],
                'primary_label' => 'تصفح البرامج',
                'primary_url' => 'courses.index',
                'secondary_label' => 'تواصل معنا',
                'secondary_url' => 'contact',
            ]),
            self::block('about_mvg', 'cards_grid', true, [
                'title' => 'رسالتنا ورؤيتنا وأهدافنا',
                'lead' => 'إطار واضح يوجّه تصميم البرامج، وشراكاتنا الأكاديمية، وأثرنا على المتدربين والمؤسسات.',
                'items' => [
                    [
                        'icon' => 'assets/1853033571057247.png',
                        'title' => 'مهمتنا',
                        'body' => 'تقديم برامج تعليمية وتدريبية مرنة مبنية على احتياج سوق العمل، بالشراكة مع الخبراء والجهات الأكاديمية، لتأهيل كوادر قادرة على المنافسة وتحقيق أثر مهني مستدام.',
                    ],
                    [
                        'icon' => 'assets/1853033393294593.png',
                        'title' => 'رؤيتنا',
                        'body' => 'أن يكون {platform_name} المرجع الموثوق للتعليم الاحترافي، والمنصة التي يثق بها الأفراد والمؤسسات لاكتساب مهارات المستقبل وفق معايير أكاديمية ومهنية واضحة.',
                    ],
                    [
                        'icon' => 'assets/1853033717546615.png',
                        'title' => 'أهدافنا',
                        'body' => 'تنمية المهارات التطبيقية، ودعم التطور المهني، وتوسيع فرص التعلم المرن عبر محتوى عالي الجودة، وتقنيات تعليم حديثة، ومتابعة حتى إصدار الشهادة.',
                    ],
                ],
            ]),
            self::block('about_offerings', 'cards_grid', true, [
                'title' => 'ماذا نقدّم؟',
                'lead' => 'ثلاثة مسارات تعليمية تغطي الاحتياج الفردي والمؤسسي، دون تشتيت وبتقييم واضح للمخرجات.',
                'items' => [
                    [
                        'icon' => 'fa-solid fa-certificate',
                        'title' => 'الشهادات الاحترافية',
                        'body' => 'برامج مركّزة تبني كفاءة مهنية قابلة للقياس، وتنتهي بشهادة يمكن التحقق منها عبر المنصة.',
                    ],
                    [
                        'icon' => 'fa-solid fa-graduation-cap',
                        'title' => 'الدبلومات',
                        'body' => 'مسارات أعمق تجمع الأسس النظرية بالتطبيق العملي، وفق معايير أكاديمية ومتابعة منتظمة.',
                    ],
                    [
                        'icon' => 'fa-solid fa-handshake',
                        'title' => 'التأهيل المؤسسي',
                        'body' => 'برامج مخصصة للجهات لتطوير فرق العمل وفق الاحتياج التشغيلي، من الطلب حتى تنفيذ المسار.',
                    ],
                ],
            ]),
            self::block('about_audiences', 'path_cards', true, [
                'title' => 'لمن نقدّم خدماتنا',
                'lead' => 'بوابات واضحة لكل فئة، مع دعم وإجراءات تناسب طبيعة المستفيد.',
                'items' => [
                    [
                        'icon' => 'fa-solid fa-user-graduate',
                        'title' => 'المتدربون والأفراد',
                        'body' => 'استكشف البرامج، سجّل مباشرة، وتابع الحضور والمهام والشهادات من بوابة المتدرب.',
                        'url' => 'courses.index',
                        'cta_label' => 'تصفح البرامج',
                    ],
                    [
                        'icon' => 'fa-solid fa-building',
                        'title' => 'الشركات والجهات',
                        'body' => 'قدّم طلب تأهيل كوادرك واختر برامج مخصصة وفق احتياج جهتك التشغيلي.',
                        'url' => 'apply/company',
                        'cta_label' => 'تسجيل طلب جهة',
                    ],
                    [
                        'icon' => 'fa-solid fa-chalkboard-user',
                        'title' => 'المدربون والخبراء',
                        'body' => 'انضم إلى الكادر التدريبي وشارك خبرتك ضمن البرامج الأكاديمية والمهنية.',
                        'url' => 'apply/instructor',
                        'cta_label' => 'التقديم كمدرب',
                    ],
                ],
            ]),
            self::block('about_features', 'features_grid', true, [
                'title' => 'لماذا {platform_name}؟',
                'items' => [
                    ['icon' => 'fa-solid fa-building-columns', 'title' => 'انتماء أكاديمي موثوق', 'body' => 'المنصة تابعة لـ {platform_org}، وتُدار وفق إطار تعليمي واضح ومعايير مهنية معلنة.'],
                    ['icon' => 'fa-solid fa-certificate', 'title' => 'شهادات قابلة للتحقق', 'body' => 'بعد استيفاء المتطلبات تُصدر شهادة إلكترونية يمكن التحقق منها عبر صفحة التحقق.'],
                    ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'خبراء من الميدان', 'body' => 'محتوى يقدّمه مدربون وممارسون، مع إشراف أكاديمي يربط المعرفة بالتطبيق.'],
                    ['icon' => 'fa-solid fa-display', 'title' => 'تجربة تعليم مرنة', 'body' => 'حضور إلكتروني، متابعة للمهام، ودعم فني وأكاديمي على مدار رحلة التعلم.'],
                ],
            ]),
            self::block('about_stats', 'stats', true, [
                'title_prefix' => '',
                'platform_name' => '{platform_name}',
                'title_suffix' => 'في أرقام',
                'items' => [
                    ['label' => 'المتدربون', 'value' => 1000, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'المدربون', 'value' => 43, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'البرامج التدريبية', 'value' => 63, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'رضا المستفيدين', 'value' => 99, 'suffix' => '%', 'icon' => 'isax isax-global5'],
                ],
            ]),
            self::block('company_profile', 'download_cta', true, [
                'eyebrow' => 'ملف المركز',
                'title' => 'الملف التعريفي',
                'description' => 'اطّلع على هوية {platform_name}، وبرامجه، ونموذج عمله الأكاديمي. حمّل الملف التعريفي للاستخدام المؤسسي أو المشاركة مع جهتك.',
                'button_label' => 'تحميل الملف التعريفي',
                'file_url' => 'assets/website-designs/domain/img/company-profile.pdf',
            ]),
            self::block('about_cta', 'cta_banner', true, [
                'eyebrow' => '{platform_org}',
                'title' => 'ابدأ رحلتك مع مركز التعلم المستمر',
                'body' => 'اختر برنامجاً يناسب هدفك، أو تواصل معنا لتصميم مسار تدريبي لجهتك.',
                'primary_label' => 'تصفح البرامج',
                'primary_url' => 'courses.index',
                'secondary_label' => 'تواصل معنا',
                'secondary_url' => 'contact',
            ]),
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function contact(string $locale = 'ar'): array
    {
        if ($locale === 'en') {
            return self::translateBlocks(self::contact('ar'), 'en');
        }

        $mapEmbedUrl = \App\Support\CampusMap::embedUrl();

        return [
            self::block('contact_breadcrumb', 'breadcrumb', true, [
                'title' => 'تواصل معنا',
                'parent_label' => 'الرئيسية',
                'background_image' => platform_campus_path('entrance'),
            ]),
            self::block('contact_intro', 'contact_intro', true, [
                'title' => 'نرحب بتواصلكم',
                'body' => 'يسر فريق {platform_org} استقبال استفساراتكم بخصوص البرامج التدريبية، التسجيل، والدعم الفني. نسعى للرد خلال أوقات العمل الرسمية، ولطلبات تقنية يمكنكم فتح تذكرة دعم.',
                'buttons' => [
                    ['label' => 'الأسئلة الشائعة', 'style' => 'outline-primary', 'link_type' => 'route', 'link' => 'support.faq'],
                    ['label' => 'فتح تذكرة دعم', 'style' => 'primary', 'link_type' => 'route', 'link' => 'support.ticket.new'],
                    ['label' => 'متابعة تذكرة', 'style' => 'outline-secondary', 'link_type' => 'route', 'link' => 'support.ticket.search'],
                ],
            ]),
            self::block('contact_channels', 'contact_channels', true, [
                'items' => [
                    ['kind' => 'email', 'label' => 'البريد الإلكتروني للتواصل', 'value' => 'info@domain.edu.sa', 'icon' => 'assets/contact-mail.svg', 'icon_type' => 'image', 'enabled' => true],
                    ['kind' => 'phone', 'label' => 'رقم الجوال', 'value' => '966543406744', 'icon' => 'assets/contact-phone.svg', 'icon_type' => 'image', 'enabled' => true],
                    ['kind' => 'whatsapp', 'label' => 'واتساب', 'value' => '966543406744', 'icon' => 'fa-brands fa-whatsapp', 'icon_type' => 'fontawesome', 'enabled' => true],
                    ['kind' => 'address', 'label' => 'العنوان', 'value' => '{platform_org}', 'icon' => 'assets/contact-map.svg', 'icon_type' => 'image', 'enabled' => true],
                ],
            ]),
            self::block('contact_map_form', 'contact_map_form', true, [
                'show_map' => true,
                'map_embed_url' => $mapEmbedUrl,
                'map_iframe_title' => '{platform_org}',
                'form_anchor_id' => 'contact-us-Form',
                'form_title' => 'ابقَ على تواصل',
                'field_name_label' => 'الاسم',
                'field_email_label' => 'البريد الإلكتروني',
                'field_phone_label' => 'رقم الجوال',
                'field_phone_placeholder' => '+9665XXXXXXXX',
                'field_reason_label' => 'سبب التواصل',
                'field_message_label' => 'رسالة',
                'field_message_hint' => '(يجب ألا تتعدى 150 حرفاً)',
                'message_max_length' => 150,
                'submit_label' => 'إرسال',
                'support_email' => 'info@domain.edu.sa',
                'complain_reason_value' => 'complain',
                'complain_redirect_route' => 'support.ticket.new',
                'reasons' => [
                    ['value' => 'asking', 'label' => 'استفسار'],
                    ['value' => 'partnership', 'label' => 'شراكة'],
                    ['value' => 'complain', 'label' => 'شكوى'],
                ],
            ]),
        ];
    }

    public static function usesBlocks(string $type): bool
    {
        return in_array($type, CmsBlockRegistry::blockPageTypes(), true);
    }

    public static function defaultContentMode(string $type): string
    {
        return self::usesBlocks($type) ? 'blocks' : 'html';
    }

    /** @return list<array<string, mixed>> */
    public static function forPageType(string $type, string $locale = 'ar'): array
    {
        return match ($type) {
            'home' => self::home($locale),
            'about' => self::about($locale),
            'contact' => self::contact($locale),
            default => [],
        };
    }

    /**
     * @param  list<array<string, mixed>>|null  $blocks
     * @return list<array<string, mixed>>
     */
    public static function normalize(?array $blocks, string $pageType, string $locale = 'ar'): array
    {
        $blocks = is_array($blocks)
            ? array_values($blocks)
            : self::forPageType($pageType, $locale);

        return array_map(function (array $block): array {
            if (($block['type'] ?? '') !== 'news_cards') {
                return $block;
            }

            $data = $block['data'] ?? [];
            $data['source'] = $data['source'] ?? 'latest_articles';
            $data['limit'] = max(1, (int) ($data['limit'] ?? 6));

            if (($data['source'] ?? '') === 'latest_articles') {
                unset($data['items']);
            }

            $block['data'] = $data;

            return $block;
        }, $blocks);
    }

    public static function hasConfiguredBlocks(?array $blocks): bool
    {
        if (! is_array($blocks) || $blocks === []) {
            return false;
        }

        return collect($blocks)->contains(
            fn ($block) => is_array($block) && ($block['enabled'] ?? true)
        );
    }

    /** Empty / starter data for a newly added block type. */
    public static function skeleton(string $type): array
    {
        $id = $type.'-'.substr(uniqid('', true), -6);

        return match ($type) {
            'hero' => self::block($id, 'hero', true, [
                'title' => '{platform_name}',
                'subtitle_lines' => ['', ''],
                'image' => '',
                'showcase_image' => platform_campus_path('entrance'),
                'showcase_video' => '',
                'gallery' => platform_campus_gallery(),
                'search_enabled' => true,
            ]),
            'catalog_section' => self::block($id, 'catalog_section', true, [
                'source' => 'popular_fields',
                'anchor_id' => '',
            ]),
            'cards_grid' => self::block($id, 'cards_grid', true, [
                'items' => [
                    ['icon' => '', 'title' => '', 'body' => ''],
                ],
            ]),
            'image_cards' => self::block($id, 'image_cards', true, [
                'title' => '',
                'items' => [
                    ['image' => '', 'url' => 'courses.index', 'title' => ''],
                ],
                'cta_label' => '',
                'cta_url' => 'courses.index',
            ]),
            'logo_carousel' => self::block($id, 'logo_carousel', true, [
                'title' => '',
                'logos' => [['image' => '', 'alt' => '']],
            ]),
            'news_cards' => self::block($id, 'news_cards', true, [
                'title' => 'الأخبار والفعاليات',
                'badge' => 'الأخبار',
                'source' => 'latest_articles',
                'limit' => 6,
            ]),
            'testimonials' => self::block($id, 'testimonials', true, [
                'title' => '',
                'items' => [
                    ['quote' => '', 'name' => '', 'role' => '', 'avatar' => '', 'rating' => 5],
                ],
            ]),
            'faq' => self::block($id, 'faq', true, [
                'title' => '',
                'items' => [
                    ['question' => '', 'answer' => ''],
                ],
            ]),
            'stats' => self::block($id, 'stats', true, [
                'title_prefix' => '',
                'platform_name' => '{platform_name}',
                'title_suffix' => 'في أرقام',
                'items' => [
                    ['label' => '', 'value' => 0, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                ],
            ]),
            'features_grid' => self::block($id, 'features_grid', true, [
                'eyebrow' => '',
                'title' => '',
                'lead' => '',
                'items' => [
                    ['icon' => '', 'title' => '', 'body' => ''],
                ],
            ]),
            'path_cards' => self::block($id, 'path_cards', true, [
                'title' => '',
                'lead' => '',
                'items' => [
                    ['icon' => 'fa-solid fa-circle', 'title' => '', 'body' => '', 'url' => 'courses.index', 'cta_label' => 'اعرف المزيد'],
                ],
            ]),
            'steps_grid' => self::block($id, 'steps_grid', true, [
                'title' => '',
                'lead' => '',
                'items' => [
                    ['step' => 1, 'title' => '', 'body' => ''],
                ],
            ]),
            'cta_banner' => self::block($id, 'cta_banner', true, [
                'eyebrow' => '',
                'title' => '',
                'body' => '',
                'primary_label' => 'تصفح البرامج',
                'primary_url' => 'courses.index',
                'secondary_label' => 'تواصل معنا',
                'secondary_url' => 'contact',
            ]),
            'rich_text_split' => self::block($id, 'rich_text_split', true, [
                'eyebrow' => '',
                'title' => '',
                'image' => '',
                'image_badge' => '',
                'paragraphs' => [''],
                'highlights' => [],
                'primary_label' => '',
                'primary_url' => 'courses.index',
                'secondary_label' => '',
                'secondary_url' => 'contact',
            ]),
            'download_cta' => self::block($id, 'download_cta', true, [
                'eyebrow' => '',
                'title' => '',
                'description' => '',
                'button_label' => 'تنزيل',
                'file_url' => '',
            ]),
            'breadcrumb' => self::block($id, 'breadcrumb', true, [
                'title' => '',
                'parent_label' => 'الرئيسية',
                'background_image' => '',
            ]),
            'contact_intro' => self::block($id, 'contact_intro', true, [
                'title' => '',
                'body' => '',
                'buttons' => [],
            ]),
            'contact_channels' => self::block($id, 'contact_channels', true, [
                'items' => [],
            ]),
            'contact_map_form' => self::block($id, 'contact_map_form', true, [
                'show_map' => true,
                'map_embed_url' => '',
                'map_iframe_title' => '',
                'form_anchor_id' => 'contact-us-Form',
                'form_title' => '',
                'support_email' => '',
                'complain_redirect_route' => 'support.ticket.new',
                'complain_reason_value' => 'complain',
                'field_name_label' => 'الاسم',
                'field_email_label' => 'البريد',
                'field_phone_label' => 'الجوال',
                'field_phone_placeholder' => '',
                'field_reason_label' => 'سبب التواصل',
                'field_message_label' => 'الرسالة',
                'field_message_hint' => '',
                'message_max_length' => 150,
                'submit_label' => 'إرسال',
                'reasons' => [
                    ['value' => 'asking', 'label' => 'استفسار'],
                ],
            ]),
            default => self::block($id, $type, true, []),
        };
    }

    /** @param  array<string, mixed>  $data */
    private static function block(string $id, string $type, bool $enabled, array $data): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'enabled' => $enabled,
            'data' => $data,
        ];
    }

    /** @param  list<array<string, mixed>>  $blocks */
    private static function translateBlocks(array $blocks, string $locale): array
    {
        // English placeholders — editable from admin; structure mirrors Arabic.
        return array_map(function (array $block) {
            $copy = $block;
            $copy['data'] = match ($block['id']) {
                'hero' => [
                    'title' => '{platform_name}',
                    'subtitle_lines' => [
                        'Professional courses, diplomas, and certificates',
                        'Designed around {platform_org} and the labor market',
                    ],
                    'image' => $block['data']['image'],
                    'showcase_image' => $block['data']['showcase_image'] ?? platform_campus_path('entrance'),
                    'showcase_video' => $block['data']['showcase_video'] ?? '',
                    'gallery' => $block['data']['gallery'] ?? platform_campus_gallery(),
                    'search_enabled' => $block['data']['search_enabled'] ?? true,
                ],
                'audiences' => [
                    'title' => 'Clear paths for every audience',
                    'lead' => 'Dedicated gateways for learners, organizations, and instructors — on one platform.',
                    'items' => [
                        ['icon' => 'fa-solid fa-user-graduate', 'title' => 'Learners', 'body' => 'Explore programs, enroll, and track learning and certificates from the student portal.', 'url' => 'courses.index', 'cta_label' => 'Browse programs'],
                        ['icon' => 'fa-solid fa-building', 'title' => 'Companies & organizations', 'body' => 'Submit a workforce development request and choose programs tailored to your needs.', 'url' => 'apply/company', 'cta_label' => 'Organization request'],
                        ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'Instructors & experts', 'body' => 'Join the academic staff and share your expertise across professional programs.', 'url' => 'apply/instructor', 'cta_label' => 'Apply as instructor'],
                    ],
                ],
                'how_it_works' => [
                    'title' => 'How to start',
                    'lead' => 'Four clear steps from discovering a program to receiving your certificate.',
                    'items' => [
                        ['step' => 1, 'title' => 'Explore programs', 'body' => 'Choose the field, diploma, or professional certificate that matches your goal.'],
                        ['step' => 2, 'title' => 'Enroll', 'body' => 'Complete registration or submit an organization request, then confirm your seat.'],
                        ['step' => 3, 'title' => 'Learn with experts', 'body' => 'Attend sessions, submit assignments, and track progress in the learner portal.'],
                        ['step' => 4, 'title' => 'Get certified', 'body' => 'After meeting the requirements, your certificate is issued and can be verified online.'],
                    ],
                ],
                'mission_vision_goals' => [
                    'title' => 'About Us',
                    'lead' => 'The mission, vision, and goals of {platform_name}.',
                    'items' => [
                        ['icon' => $block['data']['items'][0]['icon'], 'title' => 'Our Mission', 'body' => 'Deliver innovative, flexible educational programs aligned with labor market needs, in partnership with experts and academic institutions.'],
                        ['icon' => $block['data']['items'][1]['icon'], 'title' => 'Our Vision', 'body' => 'For the Continuing Learning Center to be the leading regional reference for professional education and knowledge transformation.'],
                        ['icon' => $block['data']['items'][2]['icon'], 'title' => 'Our Goals', 'body' => 'The Continuing Learning Center aims to offer professional programs that build applied skills and support career growth through high-quality content and modern learning technologies.'],
                    ],
                ],
                'skills_program' => array_merge($block['data'], ['title' => 'Skills Program', 'cta_label' => 'All Programs']),
                'partners' => array_merge($block['data'], ['title' => 'Success Partners']),
                'accredited' => array_merge($block['data'], ['title' => 'Accredited By']),
                'news' => array_merge($block['data'], ['title' => 'News & Events', 'badge' => 'News']),
                'testimonials' => array_merge($block['data'], ['title' => 'Client Testimonials']),
                'stats' => array_merge($block['data'], [
                    'title_prefix' => '',
                    'title_suffix' => 'in Numbers',
                    'items' => [
                        array_merge($block['data']['items'][0] ?? [], ['label' => 'Learners']),
                        array_merge($block['data']['items'][1] ?? [], ['label' => 'Instructors']),
                        array_merge($block['data']['items'][2] ?? [], ['label' => 'Training programs']),
                        array_merge($block['data']['items'][3] ?? [], ['label' => 'Satisfaction']),
                    ],
                ]),
                'faq' => [
                    'title' => 'FAQ',
                    'items' => [
                        ['question' => 'What does the platform offer?', 'answer' => '{platform_name} offers professional courses, diplomas, and certificates aligned with {platform_org} and labor-market needs.'],
                        ['question' => 'Who can benefit?', 'answer' => 'Individuals, students, employees, and public or private organizations seeking to upskill their teams.'],
                        ['question' => 'Are programs online or in person?', 'answer' => 'Programs are delivered online, with in-person or blended options depending on the offering.'],
                        ['question' => 'How do I enroll?', 'answer' => 'Browse programs, then complete registration. Organizations apply through the company path.'],
                        ['question' => 'Are certificates verifiable?', 'answer' => 'Yes. After requirements are met, a certificate is issued and can be verified online.'],
                        ['question' => 'How do I get support?', 'answer' => 'Use the contact page, open a support ticket, or reach the official email and phone channels.'],
                    ],
                ],
                'platform_features' => array_merge($block['data'], [
                    'eyebrow' => 'Platform strengths',
                    'title' => 'Why this platform?',
                    'lead' => 'Clear academic affiliation, verifiable certificates, and a learning path that connects knowledge to practice.',
                    'items' => [
                        ['icon' => $block['data']['items'][0]['icon'] ?? 'fa-solid fa-certificate', 'title' => 'Accredited certificates', 'body' => 'Trusted certificates that strengthen your career path, with online authenticity checks.'],
                        ['icon' => $block['data']['items'][1]['icon'] ?? 'fa-solid fa-layer-group', 'title' => 'Diverse specializations', 'body' => 'Programs covering professional certificates, diplomas, and applied skills.'],
                        ['icon' => $block['data']['items'][2]['icon'] ?? 'fa-solid fa-chalkboard-user', 'title' => 'Professional instructors', 'body' => 'Practitioners with applied experience and clear academic supervision.'],
                        ['icon' => $block['data']['items'][3]['icon'] ?? 'fa-solid fa-display', 'title' => 'Flexible learning', 'body' => 'A simple platform for attendance, content, and support throughout your journey.'],
                        ['icon' => $block['data']['items'][4]['icon'] ?? 'fa-solid fa-lightbulb', 'title' => 'Direct practice', 'body' => 'Content that links knowledge to practice through tasks, sessions, and follow-up.'],
                    ],
                ]),
                'closing_cta' => [
                    'eyebrow' => '{platform_org}',
                    'title' => 'Start your learning journey today',
                    'body' => 'Choose a program, complete registration, and follow through until your certificate is issued.',
                    'primary_label' => 'Browse programs',
                    'primary_url' => 'courses.index',
                    'secondary_label' => 'Contact us',
                    'secondary_url' => 'contact',
                ],
                'breadcrumb' => array_merge($block['data'], ['title' => 'About Us', 'parent_label' => 'Home']),
                'about_intro' => array_merge($block['data'], [
                    'eyebrow' => 'Part of {platform_org}',
                    'title' => 'We build professional capability to academic standards',
                    'image_badge' => '{platform_name}',
                    'paragraphs' => [
                        '{platform_name} is the continuing education platform of {platform_org}. We deliver professional certificates, diplomas, and training programs designed around labor-market needs, in a structured digital environment for learners and organizations.',
                        'We work with practitioners and academic partners to close the gap between current skills and future requirements — through applied content, academic follow-up, and a clear path from enrollment to certification.',
                        'Whether you are advancing your career or upskilling a team, the platform offers flexible delivery, practical assignments, and support throughout the learning journey.',
                    ],
                    'highlights' => [
                        'Certificates that can be verified online',
                        'Programs aligned with labor-market needs',
                        'Dedicated paths for learners, organizations, and instructors',
                    ],
                    'primary_label' => 'Browse programs',
                    'primary_url' => 'courses.index',
                    'secondary_label' => 'Contact us',
                    'secondary_url' => 'contact',
                ]),
                'about_mvg' => [
                    'title' => 'Mission, vision, and goals',
                    'lead' => 'A clear frame that guides program design, academic partnerships, and our impact on learners and organizations.',
                    'items' => [
                        ['icon' => $block['data']['items'][0]['icon'] ?? '', 'title' => 'Our Mission', 'body' => 'Deliver flexible educational and training programs aligned with labor-market needs, in partnership with experts and academic institutions, to prepare professionals who can compete and create lasting impact.'],
                        ['icon' => $block['data']['items'][1]['icon'] ?? '', 'title' => 'Our Vision', 'body' => 'For {platform_name} to be the trusted reference in professional education — the platform individuals and organizations rely on to acquire future skills according to clear academic and professional standards.'],
                        ['icon' => $block['data']['items'][2]['icon'] ?? '', 'title' => 'Our Goals', 'body' => 'Build applied skills, support career growth, and expand flexible learning through high-quality content, modern learning technology, and follow-up through to certification.'],
                    ],
                ],
                'about_offerings' => [
                    'title' => 'What we offer',
                    'lead' => 'Three learning paths covering individual and organizational needs, with clear outcomes.',
                    'items' => [
                        ['icon' => 'fa-solid fa-certificate', 'title' => 'Professional certificates', 'body' => 'Focused programs that build measurable professional competence, ending with a certificate that can be verified on the platform.'],
                        ['icon' => 'fa-solid fa-graduation-cap', 'title' => 'Diplomas', 'body' => 'Deeper pathways that combine theory and practice, with academic standards and regular follow-up.'],
                        ['icon' => 'fa-solid fa-handshake', 'title' => 'Organizational training', 'body' => 'Custom programs for organizations to develop teams around operational needs, from request to delivery.'],
                    ],
                ],
                'about_audiences' => [
                    'title' => 'Who we serve',
                    'lead' => 'Clear gateways for every audience, with support and processes that match how they work.',
                    'items' => [
                        ['icon' => 'fa-solid fa-user-graduate', 'title' => 'Learners', 'body' => 'Explore programs, enroll, and track attendance, assignments, and certificates from the learner portal.', 'url' => 'courses.index', 'cta_label' => 'Browse programs'],
                        ['icon' => 'fa-solid fa-building', 'title' => 'Companies & organizations', 'body' => 'Submit a workforce development request and choose programs tailored to your operational needs.', 'url' => 'apply/company', 'cta_label' => 'Organization request'],
                        ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'Instructors & experts', 'body' => 'Join the academic staff and share your expertise across professional programs.', 'url' => 'apply/instructor', 'cta_label' => 'Apply as instructor'],
                    ],
                ],
                'about_features' => [
                    'title' => 'Why {platform_name}?',
                    'items' => [
                        ['icon' => 'fa-solid fa-building-columns', 'title' => 'Trusted academic affiliation', 'body' => 'The platform belongs to {platform_org} and is run within a clear educational framework and stated professional standards.'],
                        ['icon' => 'fa-solid fa-certificate', 'title' => 'Verifiable certificates', 'body' => 'After requirements are met, an electronic certificate is issued and can be checked on the verification page.'],
                        ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'Practitioners in the field', 'body' => 'Content is delivered by instructors and practitioners, with academic oversight that links knowledge to practice.'],
                        ['icon' => 'fa-solid fa-display', 'title' => 'Flexible learning experience', 'body' => 'Online attendance, assignment follow-up, and technical and academic support throughout the journey.'],
                    ],
                ],
                'about_stats' => array_merge($block['data'], ['title_prefix' => '', 'title_suffix' => 'in Numbers']),
                'company_profile' => array_merge($block['data'], [
                    'eyebrow' => 'Center profile',
                    'title' => 'Company profile',
                    'description' => 'Learn about {platform_name}, its programs, and academic model. Download the profile to share with your organization.',
                    'button_label' => 'Download profile',
                ]),
                'about_cta' => [
                    'eyebrow' => '{platform_org}',
                    'title' => 'Start your journey with the Continuing Learning Center',
                    'body' => 'Choose a program that matches your goal, or contact us to design a training path for your organization.',
                    'primary_label' => 'Browse programs',
                    'primary_url' => 'courses.index',
                    'secondary_label' => 'Contact us',
                    'secondary_url' => 'contact',
                ],
                'contact_breadcrumb' => array_merge($block['data'], ['title' => 'Contact Us', 'parent_label' => 'Home']),
                'contact_intro' => array_merge($block['data'], [
                    'title' => 'We Welcome Your Contact',
                    'body' => 'The {platform_org} team is ready to answer your questions about training programs, registration, and technical support.',
                    'buttons' => [
                        ['label' => 'FAQ', 'style' => 'outline-primary', 'link_type' => 'route', 'link' => 'support.faq'],
                        ['label' => 'Open Support Ticket', 'style' => 'primary', 'link_type' => 'route', 'link' => 'support.ticket.new'],
                        ['label' => 'Track Ticket', 'style' => 'outline-secondary', 'link_type' => 'route', 'link' => 'support.ticket.search'],
                    ],
                ]),
                'contact_channels' => [
                    'items' => [
                        ['kind' => 'email', 'label' => 'Email', 'value' => 'info@domain.edu.sa', 'icon' => 'assets/contact-mail.svg', 'icon_type' => 'image', 'enabled' => true],
                        ['kind' => 'phone', 'label' => 'Phone', 'value' => '966543406744', 'icon' => 'assets/contact-phone.svg', 'icon_type' => 'image', 'enabled' => true],
                        ['kind' => 'whatsapp', 'label' => 'WhatsApp', 'value' => '966543406744', 'icon' => 'fa-brands fa-whatsapp', 'icon_type' => 'fontawesome', 'enabled' => true],
                        ['kind' => 'address', 'label' => 'Address', 'value' => '{platform_org}', 'icon' => 'assets/contact-map.svg', 'icon_type' => 'image', 'enabled' => true],
                    ],
                ],
                'contact_map_form' => array_merge($block['data'], [
                    'map_iframe_title' => '{platform_org}',
                    'form_title' => 'Stay in Touch',
                    'field_name_label' => 'Name',
                    'field_email_label' => 'Email',
                    'field_phone_label' => 'Phone',
                    'field_reason_label' => 'Reason',
                    'field_message_label' => 'Message',
                    'field_message_hint' => '(Maximum 150 characters)',
                    'submit_label' => 'Send',
                    'reasons' => [
                        ['value' => 'asking', 'label' => 'Inquiry'],
                        ['value' => 'partnership', 'label' => 'Partnership'],
                        ['value' => 'complain', 'label' => 'Complaint'],
                    ],
                ]),
                default => $block['data'],
            };

            return $copy;
        }, $blocks);
    }
}
