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
                'title' => 'مركز التعلم المستمر',
                'subtitle_lines' => [
                    'دورات ودبلومات تدريبية احترافية',
                    'بجامعة الامير مقرن',
                ],
                'image' => 'assets/1857921787411122.jpeg',
                'search_enabled' => true,
            ]),
            self::block('popular_fields', 'catalog_section', true, [
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
            self::block('mission_vision_goals', 'cards_grid', true, [
                'title' => 'من نحن',
                'lead' => 'رسالة ورؤية وأهداف مركز التعلم المستمر في تطوير المهارات وبناء القدرات.',
                'items' => [
                    [
                        'icon' => 'assets/1853033571057247.png',
                        'title' => 'مهمتنا',
                        'body' => 'تقديم برامج تعليمية وتدريبية مبتكرة ومرنة مبنية على احتياجات سوق العمل، بالشراكة مع الخبراء والجهات الأكاديمية، بهدف تأهيل كوادر وطنية قادرة على المنافسة عالميًا وتحقيق التنمية المستدامة.',
                    ],
                    [
                        'icon' => 'assets/1853033393294593.png',
                        'title' => 'رؤيتنا',
                        'body' => 'أن يكون مركز التعلم المستمر المرجع الرائد إقليميًا في تقديم التعليم الاحترافي، وبوابة التحول المعرفي التي تمكّن الأفراد والمؤسسات من اكتساب مهارات المستقبل وفق أعلى المعايير الأكاديمية والمهنية.',
                    ],
                    [
                        'icon' => 'assets/1853033717546615.png',
                        'title' => 'أهدافنا',
                        'body' => 'يهدف مركز التعلم المستمر إلى تقديم برامج تعليمية احترافية مواكبة لسوق العمل تنمّي المهارات التطبيقية وتدعم التطور المهني، من خلال محتوى عالي الجودة وتقنيات تعليم حديثة.',
                    ],
                ],
            ]),
            self::block('skills_program', 'image_cards', true, [
                'section_id' => 'section-mahara',
                'title' => 'برنامج مهارات',
                'items' => [
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات عامة - باحثين عن عمل'],
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات عامة - موظفين على رأس العمل'],
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات مهنية - باحثين عن عمل'],
                    ['image' => '', 'url' => 'courses.index', 'title' => 'مهارات مهنية - الموظفين على رأس العمل'],
                ],
                'cta_label' => 'جميع البرامج',
                'cta_url' => 'courses.index',
            ]),
            self::block('partners', 'logo_carousel', true, [
                'title' => 'شركاء النجاح',
                'logos' => [
                    ['image' => 'assets/1853384885027491.png', 'alt' => ''],
                    ['image' => 'assets/1853385108613939.png', 'alt' => ''],
                    ['image' => 'assets/1853384983114238.png', 'alt' => ''],
                ],
            ]),
            self::block('accredited', 'logo_carousel', true, [
                'title' => 'معتمدون لدى',
                'logos' => [
                    ['image' => 'assets/1857913315552753.png', 'alt' => ''],
                    ['image' => 'assets/516e9932-3a38-4c92-a79c-99606a4c6dd9.png', 'alt' => ''],
                ],
            ]),
            self::block('news', 'news_cards', true, [
                'title' => 'الأخبار والفعاليات',
                'badge' => 'الاخبار والفعاليات',
                'source' => 'latest_articles',
                'limit' => 6,
            ]),
            self::block('testimonials', 'testimonials', true, [
                'title' => 'آراء العملاء',
                'items' => [
                    ['quote' => 'استفدت كثيرًا من البرامج المقدمة، خاصة في المهارات المهنية، وساعدني ذلك على الاستعداد لسوق العمل بثقة أكبر', 'name' => 'محمد السبيعي', 'avatar' => 'assets/1853038435618862.png', 'rating' => 5],
                    ['quote' => 'برنامج مهارات قدم لي تجربة تدريبية متكاملة من حيث المحتوى والتنظيم والدعم، وأنصح به كل باحث عن عمل', 'name' => 'سارة المطيري', 'avatar' => 'assets/1853038521958109.png', 'rating' => 5],
                    ['quote' => 'البرنامج ساهم في تطوير مهاراتي وربطني بفرص وظيفية مناسبة، وكان له أثر إيجابي واضح على مساري المهني', 'name' => 'خالد الحربي', 'avatar' => 'assets/1853038589759124.png', 'rating' => 5],
                    ['quote' => 'برنامج مهارات كان تجربة مميزة ساعدتني على تطوير مهاراتي المهنية وربطني بفرص عملية حقيقية، والتدريب كان منظم وذو محتوى عالي الجودة', 'name' => 'أحمد العتيبي', 'avatar' => 'assets/1853038251279600.png', 'rating' => 5],
                    ['quote' => 'منصة مركز التعلم المستمر وفرت لي محتوى تدريبيًا عمليًا وسهل الوصول إليه، وساعدني على تطوير مهاراتي بما يتوافق مع متطلبات سوق العمل', 'name' => 'نورة القحطاني', 'avatar' => 'assets/1853038521958109.png', 'rating' => 5],
                ],
            ]),
            self::block('faq', 'faq', true, [
                'title' => 'الأسئلة الشائعة',
                'items' => [
                    ['question' => 'ما هي منصة مركز التعلم المستمر؟', 'answer' => 'منصة مركز التعلم المستمر هي منصة تعليمية رقمية تقدم برامج تعليمية وتدريبية احترافية مرنة تهدف إلى تطوير المهارات وتعزيز المعرفة بما يتوافق مع احتياجات سوق العمل.'],
                    ['question' => 'من هم المستفيدون من منصة مركز التعلم المستمر؟', 'answer' => 'تستهدف المنصة الأفراد والطلاب والموظفين والقيادات، بالإضافة إلى الجهات الحكومية والخاصة الراغبة في تطوير مهارات كوادرها ورفع كفاءتها المهنية.'],
                    ['question' => 'هل تقدم المنصة برامج تعليمية حضورية وعن بُعد؟', 'answer' => 'تقدم منصة مركز التعلم المستمر برامج تعليمية وتدريبية عبر التعلم الإلكتروني عن بُعد، مع إمكانية تقديم برامج حضورية أو مدمجة حسب طبيعة البرنامج، مع الالتزام بمعايير الجودة في المحتوى والتقديم.'],
                    ['question' => 'كيف يمكن التسجيل في منصة مركز التعلم المستمر؟', 'answer' => 'يمكن التسجيل في البرامج من خلال الموقع الإلكتروني للمنصة أو عبر قنوات التواصل الرسمية المتاحة.'],
                ],
            ]),
            self::block('stats', 'stats', true, [
                'title_prefix' => 'منصة',
                'platform_name' => 'مركز التعلم المستمر',
                'title_suffix' => 'في أرقام',
                'items' => [
                    ['label' => 'عدد المتدربين', 'value' => 1000, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'عدد المدربين', 'value' => 43, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'عدد الدورات التدريبية', 'value' => 63, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'معدل الرضا', 'value' => 99, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                ],
            ]),
            self::block('platform_features', 'features_grid', true, [
                'title' => 'مميزات منصة مركز التعلم المستمر',
                'items' => [
                    ['icon' => 'assets/1853132469069541.png', 'title' => 'شهادات معتمدة', 'body' => 'شهادات موثوقة تعزز مسارك المهني وتزيد فرصك الوظيفية'],
                    ['icon' => 'assets/1853132752703292.png', 'title' => 'عشرات التخصصات', 'body' => 'برامج تدريبية متنوعة تلبي متطلبات سوق العمل'],
                    ['icon' => 'assets/1853133034226589.png', 'title' => 'مدربون احترافيون', 'body' => 'خبراء معتمدون بخبرة عملية محلية وعالمية'],
                    ['icon' => 'assets/1853133514491196.png', 'title' => 'سهولة الاستخدام', 'body' => 'تعلم مرن عبر منصة إلكترونية سهلة مع دعم مستمر'],
                    ['icon' => 'assets/1853382791824256.png', 'title' => 'التطبيق العملي المباشر', 'body' => 'محتوى تطبيقي يربط بين المعرفة النظرية والممارسة العملية'],
                ],
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
            ]),
            self::block('about_intro', 'rich_text_split', true, [
                'title' => 'عن المنصة',
                'image' => 'assets/1853032368970233.png',
                'paragraphs' => [
                    'منصة مركز التعلم المستمر هي منصة تعليمية متكاملة تابعة لجامعة الامير مقرن، تُعنى بتقديم برامج تعليمية وتدريبية احترافية تهدف إلى تنمية المهارات وبناء القدرات البشرية وفق متطلبات سوق العمل. توفر المنصة بيئة تعليمية رقمية حديثة تعتمد على أحدث أساليب التعلم، بما يضمن تجربة تعليمية مرنة وفعالة تلبي احتياجات المتعلمين والأفراد والمؤسسات.',
                    'تسعى المنصة إلى تصميم وتقديم برامج تدريبية وتطويرية متخصصة بالشراكة مع خبراء وممارسين في مختلف المجالات، بهدف رفع كفاءة الكوادر وتنمية مهاراتها العملية والتطبيقية. كما تعمل على ردم الفجوة بين مهارات سوق العمل الحالية والمهارات المطلوبة مستقبلًا، من خلال محتوى تدريبي عالي الجودة يواكب التطورات المهنية والتقنية، بما يعزز فرص التطور المهني والاستقرار الوظيفي ويسهم في تحسين بيئات العمل وزيادة الإنتاجية.',
                ],
            ]),
            self::block('mission_vision_goals', 'cards_grid', true, [
                'items' => [
                    [
                        'icon' => 'assets/1853033571057247.png',
                        'title' => 'مهمتنا',
                        'body' => 'تقديم برامج تعليمية وتدريبية مبتكرة ومرنة مبنية على احتياجات سوق العمل، بالشراكة مع الخبراء والجهات الأكاديمية، بهدف تأهيل كوادر وطنية قادرة على المنافسة عالميًا وتحقيق التنمية المستدامة.',
                    ],
                    [
                        'icon' => 'assets/1853033393294593.png',
                        'title' => 'رؤيتنا',
                        'body' => 'أن تكون منصة مركز التعلم المستمر المرجع الرائد إقليميًا في تقديم التعليم الاحترافي، وبوابة التحول المعرفي التي تمكّن الأفراد والمؤسسات من اكتساب مهارات المستقبل وفق أعلى المعايير الأكاديمية والمهنية.',
                    ],
                    [
                        'icon' => 'assets/1853033717546615.png',
                        'title' => 'أهدافنا',
                        'body' => 'تهدف منصة مركز التعلم المستمر إلى تقديم برامج تعليمية احترافية مواكبة لسوق العمل تنمّي المهارات التطبيقية وتدعم التطور المهني، من خلال محتوى عالي الجودة وتقنيات تعليم حديثة.',
                    ],
                ],
            ]),
            self::block('stats', 'stats', true, [
                'title_prefix' => '',
                'platform_name' => 'مركز التعلم المستمر',
                'title_suffix' => 'في أرقام',
                'items' => [
                    ['label' => 'عدد المتدربين', 'value' => 1000, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'عدد المدربين', 'value' => 43, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'عدد الدورات التدريبية', 'value' => 63, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                    ['label' => 'معدل الرضا', 'value' => 99, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                ],
            ]),
            self::block('company_profile', 'download_cta', true, [
                'title' => 'قم بتنزيل الملف التعريفي',
                'description' => 'لمزيد من المعلومات حول مركز التعلم المستمر، يرجى تحميل ملفنا التعريفي.',
                'button_label' => 'تحميل الملف',
                'file_url' => 'assets/website-designs/domain/img/company-profile.pdf',
            ]),
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function contact(string $locale = 'ar'): array
    {
        if ($locale === 'en') {
            return self::translateBlocks(self::contact('ar'), 'en');
        }

        $mapEmbedUrl = 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d5517.257392055345!2d41.699758!3d27.564384!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x157645de57c7ca57%3A0x792bf416b54fe11d!2z2KzYp9mF2LnYqSDYrdin2KbZhA!5e1!3m2!1sen!2sus!4v1767522926725!5m2!1sen!2sus';

        return [
            self::block('contact_breadcrumb', 'breadcrumb', true, [
                'title' => 'تواصل معنا',
                'parent_label' => 'الرئيسية',
                'background_image' => 'assets/banner-bg-03.png',
            ]),
            self::block('contact_intro', 'contact_intro', true, [
                'title' => 'نرحب بتواصلكم',
                'body' => 'يسر فريق جامعة الامير مقرن استقبال استفساراتكم بخصوص البرامج التدريبية، التسجيل، والدعم الفني. نسعى للرد خلال أوقات العمل الرسمية، ولطلبات تقنية يمكنكم فتح تذكرة دعم.',
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
                    ['kind' => 'address', 'label' => 'العنوان', 'value' => 'مقرن — المملكة العربية السعودية', 'icon' => 'assets/contact-map.svg', 'icon_type' => 'image', 'enabled' => true],
                ],
            ]),
            self::block('contact_map_form', 'contact_map_form', true, [
                'show_map' => true,
                'map_embed_url' => $mapEmbedUrl,
                'map_iframe_title' => 'موقع جامعة الامير مقرن',
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
                'title' => platform_name(),
                'subtitle_lines' => ['', ''],
                'image' => '',
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
                'title_prefix' => 'منصة',
                'platform_name' => platform_name(),
                'title_suffix' => 'في أرقام',
                'items' => [
                    ['label' => '', 'value' => 0, 'suffix' => '+', 'icon' => 'isax isax-global5'],
                ],
            ]),
            'features_grid' => self::block($id, 'features_grid', true, [
                'title' => '',
                'items' => [
                    ['icon' => '', 'title' => '', 'body' => ''],
                ],
            ]),
            'rich_text_split' => self::block($id, 'rich_text_split', true, [
                'title' => '',
                'image' => '',
                'paragraphs' => [''],
            ]),
            'download_cta' => self::block($id, 'download_cta', true, [
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
                    'title' => 'Continuing Learning Center',
                    'subtitle_lines' => [
                        'Professional training courses',
                        'University of Muqrin',
                    ],
                    'image' => $block['data']['image'],
                    'search_enabled' => $block['data']['search_enabled'] ?? true,
                ],
                'mission_vision_goals' => [
                    'title' => 'About Us',
                    'lead' => 'The mission, vision, and goals of the Continuing Learning Center.',
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
                'faq' => array_merge($block['data'], ['title' => 'FAQ']),
                'stats' => array_merge($block['data'], ['title_prefix' => 'Platform', 'platform_name' => 'Continuing Learning Center', 'title_suffix' => 'in Numbers']),
                'platform_features' => array_merge($block['data'], ['title' => 'Platform Features']),
                'breadcrumb' => array_merge($block['data'], ['title' => 'About Us', 'parent_label' => 'Home']),
                'about_intro' => array_merge($block['data'], ['title' => 'About Us', 'paragraphs' => ['Continuing Learning Center is an integrated educational platform affiliated with the Continuing Learning Center at the University of Ha\'il.']]),
                'company_profile' => array_merge($block['data'], ['title' => 'Download Company Profile', 'description' => 'For more information about Continuing Learning Center, download our profile.', 'button_label' => 'Download']),
                'contact_breadcrumb' => array_merge($block['data'], ['title' => 'Contact Us', 'parent_label' => 'Home']),
                'contact_intro' => array_merge($block['data'], [
                    'title' => 'We Welcome Your Contact',
                    'body' => 'Our team at the Continuing Learning Center at the University of Ha\'il is ready to answer your questions about training programs, registration, and technical support.',
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
                        ['kind' => 'address', 'label' => 'Address', 'value' => 'Ha\'il — Saudi Arabia', 'icon' => 'assets/contact-map.svg', 'icon_type' => 'image', 'enabled' => true],
                    ],
                ],
                'contact_map_form' => array_merge($block['data'], [
                    'map_iframe_title' => 'Continuing Learning Center — University of Ha\'il',
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
