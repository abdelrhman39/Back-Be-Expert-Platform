<?php

namespace App\Support;

class TaibahCopy
{
    public static function applies(): bool
    {
        return IdentityThemes::activeKey() === 'taibah-green';
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public static function applyToBlock(array $block, ?string $locale = null): array
    {
        if (! self::applies()) {
            return $block;
        }

        $locale ??= app()->getLocale();
        $isEn = $locale === 'en';
        $id = (string) ($block['id'] ?? '');
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];

        if ($id === 'hero') {
            $data['title'] = $isEn ? 'Applied College' : 'الكلية التطبيقية';
            $data['subtitle_lines'] = $isEn
                ? [
                    'Applied diploma programs and flexible courses aligned with labor-market needs.',
                    'Delivered with public, private, and nonprofit partners at Taibah University.',
                ]
                : [
                    'برامج تطبيقية بدرجة الدبلوم المتوسط، ودورات تدريبية مرنة تواكب سوق العمل.',
                    'بالشراكة مع القطاعات العامة والخاصة وغير الربحية في جامعة طيبة.',
                ];
        }

        if (in_array($id, ['mission_vision_goals', 'about_mvg'], true)) {
            $data['title'] = $isEn ? 'The Applied College' : 'نبذة عن الكلية التطبيقية';
            $data['lead'] = $isEn
                ? 'An academic college at Taibah University that prepares national talent for the public and private sectors.'
                : 'كلية أكاديمية في جامعة طيبة تُعد كوادر وطنية للعمل في القطاعين العام والخاص.';
            $data['items'] = self::collegePillars($isEn, $data['items'] ?? []);
        }

        if (in_array($id, ['platform_features', 'about_features'], true)) {
            $data['eyebrow'] = $isEn ? 'Why the college' : 'لماذا الكلية';
            $data['title'] = $isEn ? 'Why the Applied College?' : 'لماذا تختار الكلية التطبيقية؟';
            $data['lead'] = $isEn
                ? 'Diploma programs shaped around local and national labor-market needs.'
                : 'برامج دبلوم صُممت لتواكب احتياج سوق العمل المحلي والوطني.';
            $data['items'] = self::collegeReasons($isEn, $data['items'] ?? []);
        }

        if ($id === 'about_intro') {
            $data['eyebrow'] = $isEn ? 'Taibah University' : 'جامعة طيبة';
            $data['title'] = $isEn ? 'Applied knowledge for the labor market' : 'معرفة تطبيقية تواكب سوق العمل';
            $data['image_badge'] = $isEn ? 'Applied College' : 'الكلية التطبيقية';
            $data['paragraphs'] = $isEn
                ? [
                    'The Applied College at Taibah University offers applied diploma programs and flexible training courses aligned with current and future labor-market needs, regionally and nationally.',
                    'The programs are built with public, private, and nonprofit partners so graduates are ready to work in both sectors.',
                    'Beside the college, the Institute of Research and Consulting connects the university with organizations through research, advice, and workforce development.',
                ]
                : [
                    'تقدم الكلية التطبيقية بجامعة طيبة برامج الدبلوم المتوسط ودورات تدريبية مرنة، متوائمة مع متطلبات سوق العمل الحالية والمستقبلية على المستوى المناطقي والوطني.',
                    'تُصمَّم هذه البرامج بالشراكة مع القطاعات العامة والخاصة وغير الربحية، لإعداد كوادر وطنية مؤهلة للعمل في القطاعين.',
                    'وإلى جانب الكلية، يصل معهد البحوث والاستشارات الجامعة بقطاعات المجتمع عبر البحث والاستشارة وتنمية القدرات.',
                ];
            $data['highlights'] = $isEn
                ? [
                    'Associate diploma programs',
                    'Partnerships with public and private sectors',
                    'Institute of Research and Consulting',
                ]
                : [
                    'برامج الدبلوم المتوسط',
                    'شراكة مع القطاعين العام والخاص',
                    'معهد البحوث والاستشارات',
                ];
        }

        if ($id === 'company_profile') {
            $data['eyebrow'] = $isEn ? 'College profile' : 'ملف الكلية';
        }

        if ($id === 'about_cta') {
            $data['title'] = $isEn ? 'Start with the Applied College' : 'ابدأ رحلتك مع الكلية التطبيقية';
        }

        $block['data'] = $data;

        return $block;
    }

    /** @return list<array{title: string, text: string}> */
    public static function institutePillars(bool $isEn): array
    {
        if ($isEn) {
            return [
                ['title' => 'Vision', 'text' => 'Localize knowledge and transfer technology so scientific solutions raise the quality of performance and operations.'],
                ['title' => 'Mission', 'text' => 'Put the institute’s people and knowledge at the service of business and society, and build research partnerships at home and abroad.'],
                ['title' => 'Role', 'text' => 'Activate contracted research, support productive and service sectors, and act as an expert house for public and private organizations.'],
            ];
        }

        return [
            ['title' => 'الرؤية', 'text' => 'توطين المعرفة ونقل التقنية لتقديم حلول علمية ترفع كفاءة الأداء والتشغيل.'],
            ['title' => 'الرسالة', 'text' => 'توظيف قدرات المعهد البشرية والمعرفية في خدمة قطاع الأعمال والمجتمع، وبناء شراكات بحث وتطوير محلياً ودولياً.'],
            ['title' => 'الدور', 'text' => 'تنشيط البحث التعاقدي، ومساندة القطاعات الإنتاجية والخدمية، والعمل بيت خبرة للجهات الحكومية والخاصة.'],
        ];
    }

    /** @return list<string> */
    public static function programs(bool $isEn): array
    {
        if ($isEn) {
            return [
                'Network Systems Management',
                'Database Management',
                'Executive Secretarial Studies',
                'Digital Marketing',
                'Cybersecurity',
                'Financial Accounting',
            ];
        }

        return [
            'إدارة أنظمة الشبكات',
            'إدارة قواعد البيانات',
            'السكرتارية التنفيذية',
            'التسويق الرقمي',
            'الأمن السيبراني',
            'المحاسبة المالية',
        ];
    }

    /** @return list<string> */
    public static function branches(bool $isEn): array
    {
        if ($isEn) {
            return ['Yanbu', 'AlUla', 'Badr', 'Khaybar', 'Al Hanakiyah', 'Mahd Al Dhahab'];
        }

        return ['ينبع', 'العلا', 'بدر', 'خيبر', 'الحناكية', 'مهد الذهب'];
    }

    /**
     * @param  list<array<string, mixed>>  $existing
     * @return list<array<string, mixed>>
     */
    protected static function collegePillars(bool $isEn, array $existing): array
    {
        $copy = $isEn
            ? [
                ['مهمتنا', 'Mission', 'Offer associate diplomas and flexible courses that stay aligned with the labor market, together with public, private, and nonprofit partners.'],
                ['رؤيتنا', 'Vision', 'An academic environment that prepares future leaders and national talent for the public and private sectors.'],
                ['أهدافنا', 'Goals', 'Match programs to regional and national needs, build practical skills, and extend training through the college branches.'],
            ]
            : [
                ['مهمتنا', 'مهمتنا', 'تقديم برامج الدبلوم المتوسط والدورات التدريبية المتوائمة مع سوق العمل، بالشراكة مع القطاعات العامة والخاصة وغير الربحية.'],
                ['رؤيتنا', 'رؤيتنا', 'بيئة أكاديمية تمكّن قادة المستقبل وتهيئ كوادر وطنية مؤهلة للعمل في القطاعين العام والخاص.'],
                ['أهدافنا', 'أهدافنا', 'مواءمة البرامج مع الاحتياج المناطقي والوطني، وتنمية المهارات العملية، وتوسيع التأهيل عبر فروع الكلية.'],
            ];

        $items = [];

        foreach ($copy as $index => [$unused, $title, $body]) {
            $icon = $existing[$index]['icon'] ?? 'fa-solid fa-circle';
            $items[] = [
                'icon' => $icon,
                'title' => $title,
                'body' => $body,
            ];
        }

        return $items;
    }

    /**
     * @param  list<array<string, mixed>>  $existing
     * @return list<array<string, mixed>>
     */
    protected static function collegeReasons(bool $isEn, array $existing): array
    {
        $copy = $isEn
            ? [
                ['Associate diplomas', 'Programs at the associate level, designed for local and national labor-market needs.'],
                ['Both sectors', 'Graduates prepared for work in the public and private sectors.'],
                ['Real partnerships', 'Training built with public, private, and nonprofit organizations.'],
                ['Six branches', 'The college reaches Yanbu, AlUla, Badr, Khaybar, Al Hanakiyah, and Mahd Al Dhahab.'],
                ['Applied fields', 'Networks, databases, secretarial studies, digital marketing, cybersecurity, and accounting.'],
            ]
            : [
                ['دبلوم متوسط', 'برامج بدرجة الدبلوم المتوسط أو المشارك، مصممة لاحتياج السوق المحلي والوطني.'],
                ['القطاعان العام والخاص', 'إعداد كوادر وطنية مؤهلة للعمل في الجهتين.'],
                ['شراكات حقيقية', 'تنفيذ بالشراكة مع القطاعات العامة والخاصة وغير الربحية.'],
                ['ستة فروع', 'تنتشر الكلية في ينبع والعلا وبدر وخيبر والحناكية ومهد الذهب.'],
                ['تخصصات تطبيقية', 'الشبكات، قواعد البيانات، السكرتارية، التسويق الرقمي، الأمن السيبراني، والمحاسبة.'],
            ];

        $items = [];

        foreach ($copy as $index => [$title, $body]) {
            $icon = $existing[$index]['icon'] ?? 'fa-solid fa-circle';
            $items[] = [
                'icon' => $icon,
                'title' => $title,
                'body' => $body,
            ];
        }

        return $items;
    }
}
