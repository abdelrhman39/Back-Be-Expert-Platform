<?php

namespace App\Support;

class RegistrationApplicationOptions
{
    /** @return array<string, array<string, mixed>> */
    public static function types(): array
    {
        return [
            'client' => [
                'label' => 'طلب عميل (فرد)',
                'label_en' => 'Individual client request',
                'legacy_slug' => 'client-request',
                'route' => 'admin.applications.client',
                'title' => 'تقديم طلب — عميل',
                'page_title' => 'طلب تسجيل فرد',
                'page_title_en' => 'Individual registration request',
                'page_intro' => 'سجّل بياناتك وسيتواصل معك مستشار التدريب لمساعدتك في اختيار البرنامج المناسب وإكمال عملية التسجيل.',
                'page_intro_en' => 'Share your details and a training advisor will help you choose the right program and complete registration.',
                'meta_description' => 'قدّم طلب تسجيلك كفرد في برامج مركز التعلم المستمر — دورات الشهادات الاحترافية والزمالات. فريقنا يراجع طلبك ويتواصل معك خلال أوقات العمل.',
                'approved_role' => 'student',
                'sections' => [
                    'contact' => ['ar' => 'بيانات التواصل', 'en' => 'Contact details'],
                    'program' => ['ar' => 'البرنامج المطلوب', 'en' => 'Requested program'],
                ],
            ],
            'company' => [
                'label' => 'طلب شركة',
                'label_en' => 'Organization request',
                'legacy_slug' => 'company-request',
                'route' => 'admin.applications.company',
                'title' => 'تقديم طلب — شركة',
                'page_title' => 'طلب تسجيل جهة',
                'page_title_en' => 'Organization training request',
                'page_intro' => 'للشركات والجهات: أرسل احتياج التدريب وعدد الكوادر، وسيتواصل معك مستشار البرامج لتنسيق خطة مناسبة.',
                'page_intro_en' => 'For companies and organizations: share your training need and headcount, and a program advisor will coordinate a suitable plan.',
                'meta_description' => 'قدّم طلب تدريب لجهة أو شركة في مركز التعلم المستمر — برامج مخصصة للكوادر مع متابعة من فريق التسجيل.',
                'approved_role' => null,
                'sections' => [
                    'organization' => ['ar' => 'بيانات الجهة', 'en' => 'Organization details'],
                    'contact' => ['ar' => 'مسؤول التواصل', 'en' => 'Contact person'],
                    'request' => ['ar' => 'احتياج التدريب', 'en' => 'Training need'],
                ],
            ],
            'marketer' => [
                'label' => 'طلب مسوق',
                'legacy_slug' => 'marketer-request',
                'route' => 'admin.applications.marketer',
                'title' => 'تقديم طلب — مسوق',
                'page_title' => 'انضم كمسوق',
                'approved_role' => null,
            ],
            'instructor' => [
                'label' => 'طلب مدرب',
                'label_en' => 'Instructor application',
                'legacy_slug' => 'instructor-request',
                'route' => 'admin.applications.instructor',
                'title' => 'تقديم طلب — مدرب',
                'page_title' => 'طلب انضمام كمدرب',
                'page_title_en' => 'Join as an instructor',
                'page_intro' => 'إن كنت مدرباً متخصصاً وترغب بالانضمام إلى كادر مركز التعلم المستمر، عبّئ النموذج وأرفق سيرتك الذاتية. يراجع فريقنا الطلب ويتواصل معك عند القبول لتفعيل حساب بوابة المدرب.',
                'page_intro_en' => 'If you are a specialist instructor and want to join the Continuing Learning Center faculty, complete the form and attach your CV. Our team reviews applications and contacts you if accepted.',
                'meta_description' => 'قدّم طلب انضمامك كمدرب في مركز التعلم المستمر — أرفق خبراتك وسيرتك الذاتية ليراجعها الفريق الأكاديمي.',
                'approved_role' => 'instructor',
                'sections' => [
                    'personal' => ['ar' => 'البيانات الشخصية', 'en' => 'Personal details'],
                    'professional' => ['ar' => 'الخبرة المهنية', 'en' => 'Professional experience'],
                    'attachments' => ['ar' => 'المرفقات', 'en' => 'Attachments'],
                ],
            ],
            'employee' => [
                'label' => 'برنامج وعد — موظف',
                'legacy_slug' => 'employee-request',
                'route' => 'admin.applications.employee',
                'title' => 'تقديم طلب — موظف (وعد)',
                'page_title' => 'برنامج وعد للموظفين',
                'approved_role' => 'student',
            ],
            'job_seeker' => [
                'label' => 'برنامج وعد — باحث عن عمل',
                'legacy_slug' => 'job-seeker-request',
                'route' => 'admin.applications.job-seeker',
                'title' => 'تقديم طلب — باحث عن عمل',
                'page_title' => 'برنامج وعد لباحثي العمل',
                'approved_role' => 'student',
            ],
            'cooperative' => [
                'label' => 'التدريب التعاوني',
                'label_en' => 'Cooperative training',
                'legacy_slug' => 'cooperative-training',
                'route' => 'admin.applications.cooperative',
                'title' => 'تقديم طلب — تدريب تعاوني',
                'page_title' => 'طلب التدريب التعاوني',
                'page_title_en' => 'Cooperative training request',
                'page_intro' => 'للطلاب الراغبين بالتدريب التعاوني: أرسل بياناتك الأكاديمية ومدة التدريب وبيانات المشرف، وسيتواصل معك فريق التدريب لتنسيق المقعد.',
                'page_intro_en' => 'For students seeking cooperative training: share your academic details, preferred duration, and supervisor contacts. The training team will coordinate a placement.',
                'meta_description' => 'قدّم طلب التدريب التعاوني في مركز التعلم المستمر — بيانات أكاديمية، مدة التدريب، ومتابعة مع المشرف الأكاديمي.',
                'meta_description_en' => 'Apply for cooperative training at the Continuing Learning Center — academic details, training duration, and coordination with your supervisor.',
                'approved_role' => null,
                'sections' => [
                    'trainee' => ['ar' => 'بيانات المتدرب', 'en' => 'Trainee details'],
                    'academic' => ['ar' => 'البيانات الأكاديمية', 'en' => 'Academic details'],
                    'placement' => ['ar' => 'مدة التدريب والفصل', 'en' => 'Duration and term'],
                    'supervisor' => ['ar' => 'المشرف الأكاديمي', 'en' => 'Academic supervisor'],
                ],
            ],
            'fellowship' => [
                'label' => 'طلبات الزمالة',
                'legacy_slug' => 'fellowship-request',
                'route' => 'admin.applications.fellowship',
                'title' => 'تقديم طلب — زمالة',
                'page_title' => 'تقديم زمالة',
                'approved_role' => 'student',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'pending' => 'قيد الانتظار',
            'under_review' => 'قيد المراجعة',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::types()[$type]['label'] ?? $type;
    }

    public static function statusLabel(string $status): string
    {
        return self::statuses()[$status] ?? $status;
    }

    public static function listRoute(string $type): string
    {
        $route = self::types()[$type]['route'] ?? 'admin.applications.client';

        return route($route);
    }

    public static function typeFromRoute(?string $routeName): string
    {
        foreach (self::types() as $key => $meta) {
            if (($meta['route'] ?? null) === $routeName) {
                return $key;
            }
        }

        return 'client';
    }

    public static function typeFromLegacySlug(string $slug): ?string
    {
        foreach (self::types() as $key => $meta) {
            if (($meta['legacy_slug'] ?? null) === $slug) {
                return $key;
            }
        }

        return null;
    }

    public static function approvedRoleForType(string $type): ?string
    {
        return self::types()[$type]['approved_role'] ?? null;
    }

    public static function pageTitle(string $type, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $meta = self::types()[$type] ?? [];

        if ($locale === 'en') {
            return $meta['page_title_en'] ?? $meta['page_title'] ?? $type;
        }

        return $meta['page_title'] ?? $type;
    }

    public static function pageIntro(string $type, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $meta = self::types()[$type] ?? [];

        if ($locale === 'en') {
            return $meta['page_intro_en'] ?? $meta['page_intro'] ?? '';
        }

        return $meta['page_intro'] ?? '';
    }

    public static function metaDescription(string $type, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $meta = self::types()[$type] ?? [];

        if ($locale === 'en') {
            return $meta['meta_description_en'] ?? $meta['meta_description'] ?? '';
        }

        return $meta['meta_description'] ?? '';
    }

    /** @return array<string, string>|null */
    public static function localizedSections(string $type, ?string $locale = null): ?array
    {
        $locale ??= app()->getLocale();
        $sections = self::types()[$type]['sections'] ?? null;

        if (! is_array($sections) || $sections === []) {
            return null;
        }

        $out = [];

        foreach ($sections as $key => $title) {
            $out[$key] = is_array($title)
                ? ($title[$locale] ?? $title['ar'] ?? $key)
                : $title;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    public static function localizeField(array $field, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $en = self::fieldCopyEn()[$field['key'] ?? ''] ?? [];

        if ($locale === 'en') {
            foreach (['label', 'placeholder', 'hint', 'checkbox_label'] as $attr) {
                $override = $field[$attr.'_en'] ?? null;

                if (filled($override)) {
                    $field[$attr] = $override;
                } elseif (filled($en[$attr] ?? null)) {
                    $field[$attr] = $en[$attr];
                }
            }
        }

        if (($field['key'] ?? '') === 'item_type') {
            $field['options'] = $locale === 'en'
                ? ['course' => 'Course / professional certificate', 'fellowship' => 'Professional fellowship']
                : ['course' => 'دورة / شهادة احترافية', 'fellowship' => 'زمالة مهنية'];
        } elseif (isset($field['options']) && is_array($field['options'])) {
            $field['options'] = match ($field['key'] ?? '') {
                'education_level', 'qualification' => self::educationLevels($locale),
                'region' => self::regions($locale),
                'gender' => self::genders($locale),
                'currently_work', 'driving_certified' => self::yesNo($locale),
                'english_level', 'computer_level' => self::englishLevels($locale),
                'training_duration' => self::trainingDurations($locale),
                'semester' => self::semesters($locale),
                default => $field['options'],
            };
        }

        return $field;
    }

    /** @return array<string, array{label?: string, placeholder?: string, hint?: string}> */
    protected static function fieldCopyEn(): array
    {
        return [
            'name' => ['label' => 'Full name', 'placeholder' => 'Name as on national ID'],
            'email' => ['label' => 'Email', 'placeholder' => 'name@example.com'],
            'phone' => ['label' => 'Mobile number', 'placeholder' => '5xxxxxxxx', 'hint' => 'A Saudi number reachable on WhatsApp is preferred'],
            'education_level' => ['label' => 'Education level'],
            'item_type' => ['label' => 'Program type'],
            'interested_programs' => [
                'label' => 'Programs of interest',
                'placeholder' => 'Name the course or fellowship, or more than one program',
                'hint' => 'You can list several programs in one field',
            ],
            'company_name' => ['label' => 'Organization name', 'placeholder' => 'Official company or entity name'],
            'activity' => ['label' => 'Organization activity', 'placeholder' => 'e.g. technology, HR, healthcare, education'],
            'region' => ['label' => 'Region'],
            'responsible_name' => ['label' => 'Representative name', 'placeholder' => 'Name of the organization representative'],
            'responsible_phone' => ['label' => 'Contact mobile', 'placeholder' => '5xxxxxxxx', 'hint' => 'A number reachable on WhatsApp is preferred'],
            'responsible_email' => ['label' => 'Contact email', 'placeholder' => 'name@company.com'],
            'n_employee' => ['label' => 'Number of people to train', 'hint' => 'Seats or employees included in this request'],
            'message' => [
                'label' => 'Training need',
                'placeholder' => 'Programs needed, learner level, delivery location or format, and preferred timing',
                'hint' => 'Clearer details help us recommend a better plan',
            ],
            'f_name' => ['label' => 'First name', 'placeholder' => 'As on national ID'],
            'l_name' => ['label' => 'Family name', 'placeholder' => 'As on national ID'],
            'ssn' => ['label' => 'National ID', 'placeholder' => '10 digits', 'hint' => '10-digit national ID or Iqama'],
            'nationality' => ['label' => 'Nationality', 'placeholder' => 'Saudi'],
            'gender' => ['label' => 'Gender'],
            'job_title' => ['label' => 'Current job title', 'placeholder' => 'e.g. Assistant professor, training specialist'],
            'specialization' => ['label' => 'Training specialization', 'placeholder' => 'e.g. project management, cybersecurity'],
            'years_experience' => ['label' => 'Years of training experience', 'hint' => 'Years delivering training, not only industry work'],
            'teaching_areas' => [
                'label' => 'Areas you can teach',
                'placeholder' => 'Programs or skills you deliver',
                'hint' => 'List the topics you are ready to teach',
            ],
            'certificates' => [
                'label' => 'Relevant professional certificates',
                'placeholder' => 'PMP, CompTIA, SHRM… (optional)',
            ],
            'cv' => ['label' => 'CV (PDF)', 'hint' => 'PDF up to 5 MB'],
            'certificates_file' => ['label' => 'Certificates file (optional)', 'hint' => 'PDF or image, up to 5 MB'],
            'english_level' => ['label' => 'English level'],
            'gpa' => ['label' => 'GPA', 'hint' => 'Out of 5'],
            'training_duration' => ['label' => 'Training duration'],
            'semester' => ['label' => 'Academic term'],
            'start_date' => ['label' => 'Expected start date'],
            'supervisor_name' => ['label' => 'Supervisor name', 'placeholder' => 'Supervisor full name'],
            'supervisor_phone' => ['label' => 'Supervisor mobile', 'placeholder' => '5xxxxxxxx'],
            'supervisor_email' => ['label' => 'Supervisor email', 'placeholder' => 'supervisor@university.edu'],
        ];
    }

    /** @return array<string, string> */
    public static function educationLevels(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? [
                'secondary' => 'Secondary',
                'diploma' => 'Diploma',
                'bachelor' => 'Bachelor',
                'university' => 'University',
                'master' => 'Master',
                'phd' => 'PhD',
                'intermediate' => 'Intermediate',
            ]
            : [
                'secondary' => 'ثانوي',
                'diploma' => 'دبلوم',
                'bachelor' => 'بكالوريوس',
                'university' => 'جامعي',
                'master' => 'ماجستير',
                'phd' => 'دكتوراه',
                'intermediate' => 'متوسط',
            ];
    }

    /** @return array<string, string> */
    public static function regions(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? [
                'riyadh' => 'Riyadh',
                'makkah' => 'Makkah',
                'madinah' => 'Madinah',
                'qassim' => 'Qassim',
                'eastern' => 'Eastern Province',
                'asir' => 'Asir',
                'tabuk' => 'Tabuk',
                'hail' => 'Hail',
                'jazan' => 'Jazan',
                'najran' => 'Najran',
                'northern' => 'Northern Borders',
                'jouf' => 'Al Jouf',
                'bahah' => 'Al Bahah',
            ]
            : [
                'riyadh' => 'الرياض',
                'makkah' => 'مكة المكرمة',
                'madinah' => 'المدينة المنورة',
                'qassim' => 'القصيم',
                'eastern' => 'الشرقية',
                'asir' => 'عسير',
                'tabuk' => 'تبوك',
                'hail' => 'حائل',
                'jazan' => 'جازان',
                'najran' => 'نجران',
                'northern' => 'الحدود الشمالية',
                'jouf' => 'الجوف',
                'bahah' => 'الباحة',
            ];
    }

    /** @return array<string, string> */
    public static function englishLevels(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? [
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
                'fluent' => 'Fluent',
            ]
            : [
                'beginner' => 'مبتدئ',
                'intermediate' => 'متوسط',
                'advanced' => 'متقدم',
                'fluent' => 'طليق',
            ];
    }

    /** @return array<string, string> */
    public static function trainingDurations(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? [
                '1_month' => '1 month',
                '2_months' => '2 months',
                '3_months' => '3 months',
                '6_months' => '6 months',
            ]
            : [
                '1_month' => 'شهر',
                '2_months' => 'شهران',
                '3_months' => '3 أشهر',
                '6_months' => '6 أشهر',
            ];
    }

    /** @return array<string, string> */
    public static function semesters(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? [
                'fall' => 'Fall',
                'spring' => 'Spring',
                'summer' => 'Summer',
            ]
            : [
                'fall' => 'خريف',
                'spring' => 'ربيع',
                'summer' => 'صيف',
            ];
    }

    /** @return array<string, string> */
    public static function genders(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ['male' => 'Male', 'female' => 'Female']
            : ['male' => 'ذكر', 'female' => 'أنثى'];
    }

    /** @return array<string, string> */
    public static function yesNo(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ['yes' => 'Yes', 'no' => 'No']
            : ['yes' => 'نعم', 'no' => 'لا'];
    }

    /** @return array<int, array<string, mixed>> */
    public static function fieldsFor(string $type): array
    {
        return match ($type) {
            'client' => [
                ['key' => 'name', 'label' => 'الاسم الرباعي', 'type' => 'text', 'required' => true, 'contact' => 'name', 'section' => 'contact', 'placeholder' => 'الاسم كما في الهوية الوطنية', 'col' => 6],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email', 'section' => 'contact', 'placeholder' => 'example@email.com', 'col' => 6],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone', 'section' => 'contact', 'placeholder' => '05xxxxxxxx', 'hint' => 'يفضّل رقم سعودي يمكن التواصل عليه عبر واتساب', 'col' => 6],
                ['key' => 'education_level', 'label' => 'المؤهل العلمي', 'type' => 'select', 'required' => true, 'options' => self::educationLevels(), 'section' => 'program', 'col' => 6],
                ['key' => 'item_type', 'label' => 'نوع البرنامج', 'type' => 'select', 'required' => true, 'options' => ['course' => 'دورة / شهادة احترافية', 'fellowship' => 'زمالة مهنية'], 'section' => 'program', 'col' => 6],
                ['key' => 'interested_programs', 'label' => 'البرامج المهتم بها', 'type' => 'textarea', 'required' => true, 'rows' => 4, 'section' => 'program', 'col' => 12, 'placeholder' => 'اذكر اسم الدورة أو الزمالة، أو أكثر من برنامج إن رغبت', 'hint' => 'يمكنك ذكر عدة برامج في حقل واحد'],
            ],
            'company' => [
                ['key' => 'company_name', 'label' => 'اسم الجهة', 'type' => 'text', 'required' => true, 'contact' => 'name', 'section' => 'organization', 'col' => 6, 'placeholder' => 'الاسم الرسمي للشركة أو الجهة'],
                ['key' => 'activity', 'label' => 'نشاط الجهة', 'type' => 'text', 'required' => true, 'section' => 'organization', 'col' => 6, 'placeholder' => 'مثال: تقنية، موارد بشرية، صحة، تعليم'],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions(), 'section' => 'organization', 'col' => 6],
                ['key' => 'responsible_name', 'label' => 'اسم المسؤول', 'type' => 'text', 'required' => true, 'section' => 'contact', 'col' => 6, 'placeholder' => 'اسم ممثل الجهة'],
                ['key' => 'responsible_phone', 'label' => 'جوال المسؤول', 'type' => 'tel', 'required' => true, 'contact' => 'phone', 'section' => 'contact', 'col' => 6, 'placeholder' => '5xxxxxxxx', 'hint' => 'يفضّل رقم يمكن التواصل عليه عبر واتساب'],
                ['key' => 'responsible_email', 'label' => 'بريد المسؤول', 'type' => 'email', 'required' => true, 'contact' => 'email', 'section' => 'contact', 'col' => 6, 'placeholder' => 'name@company.com'],
                ['key' => 'n_employee', 'label' => 'عدد المراد تدريبهم', 'type' => 'number', 'required' => true, 'min' => 1, 'section' => 'request', 'col' => 6, 'hint' => 'عدد المقاعد أو الموظفين المستهدفين'],
                ['key' => 'message', 'label' => 'تفاصيل الاحتياج', 'type' => 'textarea', 'required' => true, 'rows' => 5, 'section' => 'request', 'col' => 12, 'placeholder' => 'البرامج المطلوبة، مستوى المتدربين، الموقع أو نمط التنفيذ، والتوقيت المفضل', 'hint' => 'كلّما كانت التفاصيل أوضح، كانت توصيتنا أدق'],
            ],
            'marketer' => [
                ['key' => 'f_name', 'label' => 'الاسم الأول', 'type' => 'text', 'required' => true],
                ['key' => 'l_name', 'label' => 'اسم العائلة', 'type' => 'text', 'required' => true, 'contact' => 'name'],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email'],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
                ['key' => 'nationality', 'label' => 'الجنسية', 'type' => 'text', 'required' => true],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions()],
                ['key' => 'area', 'label' => 'الحي / المدينة', 'type' => 'text', 'required' => true],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders()],
                ['key' => 'identity_attachment', 'label' => 'مرفق الهوية', 'type' => 'file', 'required' => true],
                ['key' => 'profile_attachment', 'label' => 'صورة شخصية', 'type' => 'file', 'required' => true],
                ['key' => 'national_address', 'label' => 'العنوان الوطني', 'type' => 'file', 'required' => true],
            ],
            'instructor' => [
                ['key' => 'f_name', 'label' => 'الاسم الأول', 'type' => 'text', 'required' => true, 'section' => 'personal', 'col' => 6, 'placeholder' => 'كما في الهوية'],
                ['key' => 'l_name', 'label' => 'اسم العائلة', 'type' => 'text', 'required' => true, 'contact' => 'name', 'section' => 'personal', 'col' => 6, 'placeholder' => 'كما في الهوية'],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true, 'section' => 'personal', 'col' => 6, 'placeholder' => '10 أرقام', 'hint' => 'رقم الهوية الوطنية أو الإقامة'],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email', 'section' => 'personal', 'col' => 6, 'placeholder' => 'example@email.com'],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone', 'section' => 'personal', 'col' => 6, 'placeholder' => '5xxxxxxxx', 'hint' => 'يفضّل رقم يمكن التواصل عليه عبر واتساب'],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders(), 'section' => 'personal', 'col' => 6],
                ['key' => 'nationality', 'label' => 'الجنسية', 'type' => 'text', 'required' => true, 'section' => 'personal', 'col' => 6, 'placeholder' => 'سعودي'],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions(), 'section' => 'personal', 'col' => 6],
                ['key' => 'job_title', 'label' => 'المسمى الوظيفي الحالي', 'type' => 'text', 'required' => true, 'section' => 'professional', 'col' => 6, 'placeholder' => 'مثال: أستاذ مساعد، أخصائي تدريب'],
                ['key' => 'specialization', 'label' => 'التخصص التدريبي', 'type' => 'text', 'required' => true, 'section' => 'professional', 'col' => 6, 'placeholder' => 'مثال: إدارة المشاريع، الأمن السيبراني'],
                ['key' => 'education_level', 'label' => 'أعلى مؤهل علمي', 'type' => 'select', 'required' => true, 'options' => self::educationLevels(), 'section' => 'professional', 'col' => 6],
                ['key' => 'years_experience', 'label' => 'سنوات الخبرة التدريبية', 'type' => 'number', 'required' => true, 'min' => 0, 'section' => 'professional', 'col' => 6, 'hint' => 'سنوات تقديم التدريب، لا سنوات العمل فقط'],
                ['key' => 'teaching_areas', 'label' => 'مجالات التدريب', 'type' => 'textarea', 'required' => true, 'rows' => 3, 'section' => 'professional', 'col' => 12, 'placeholder' => 'اذكر البرامج أو المهارات التي تقدّمها', 'hint' => 'الموضوعات التي يمكنك تقديمها فور الانضمام'],
                ['key' => 'certificates', 'label' => 'الشهادات المهنية ذات الصلة', 'type' => 'textarea', 'required' => false, 'rows' => 2, 'section' => 'professional', 'col' => 12, 'placeholder' => 'PMP، CompTIA، SHRM… (اختياري)'],
                ['key' => 'cv', 'label' => 'السيرة الذاتية (PDF)', 'type' => 'file', 'required' => true, 'section' => 'attachments', 'col' => 6, 'accept' => '.pdf,application/pdf', 'hint' => 'ملف PDF بحد أقصى 5 ميغابايت'],
                ['key' => 'certificates_file', 'label' => 'مرفق الشهادات (اختياري)', 'type' => 'file', 'required' => false, 'section' => 'attachments', 'col' => 6, 'accept' => '.pdf,.jpg,.jpeg,.png', 'hint' => 'PDF أو صورة، بحد أقصى 5 ميغابايت'],
            ],
            'employee' => [
                ['key' => 'f_name', 'label' => 'الاسم الأول', 'type' => 'text', 'required' => true],
                ['key' => 'l_name', 'label' => 'اسم العائلة', 'type' => 'text', 'required' => true, 'contact' => 'name'],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email'],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
                ['key' => 'age', 'label' => 'العمر', 'type' => 'number', 'required' => true, 'min' => 20],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders()],
                ['key' => 'hr_category', 'label' => 'فئة برنامج وعد', 'type' => 'text', 'required' => true],
                ['key' => 'current_last_job', 'label' => 'الوظيفة الحالية / الأخيرة', 'type' => 'text', 'required' => true],
                ['key' => 'nationality', 'label' => 'الجنسية', 'type' => 'text', 'required' => true],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions()],
                ['key' => 'city', 'label' => 'المدينة', 'type' => 'text', 'required' => true],
                ['key' => 'currently_work', 'label' => 'يعمل حالياً؟', 'type' => 'radio', 'required' => true, 'options' => self::yesNo()],
                ['key' => 'driving_certified', 'label' => 'رخصة قيادة؟', 'type' => 'radio', 'required' => true, 'options' => self::yesNo()],
                ['key' => 'cv', 'label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true],
                ['key' => 'job_experience', 'label' => 'الخبرات العملية', 'type' => 'textarea', 'required' => true, 'rows' => 3],
                ['key' => 'langs', 'label' => 'اللغات', 'type' => 'textarea', 'required' => true, 'rows' => 2],
                ['key' => 'certificates', 'label' => 'الشهادات', 'type' => 'textarea', 'required' => true, 'rows' => 2],
            ],
            'job_seeker' => [
                ['key' => 'name', 'label' => 'الاسم الكامل', 'type' => 'text', 'required' => true, 'contact' => 'name'],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email'],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
                ['key' => 'age', 'label' => 'العمر', 'type' => 'number', 'required' => true],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions()],
                ['key' => 'city', 'label' => 'المدينة', 'type' => 'text', 'required' => true],
                ['key' => 'neighborhood', 'label' => 'الحي', 'type' => 'text', 'required' => true],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders()],
                ['key' => 'qualification', 'label' => 'المؤهل', 'type' => 'select', 'required' => true, 'options' => self::educationLevels()],
                ['key' => 'specialization', 'label' => 'التخصص', 'type' => 'text', 'required' => true],
                ['key' => 'educational_authority', 'label' => 'الجهة التعليمية', 'type' => 'text', 'required' => true],
                ['key' => 'english_level', 'label' => 'مستوى الإنجليزية', 'type' => 'select', 'required' => true, 'options' => self::englishLevels()],
                ['key' => 'computer_level', 'label' => 'مستوى الحاسب', 'type' => 'select', 'required' => true, 'options' => self::englishLevels()],
                ['key' => 'cv', 'label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true],
                ['key' => 'other_skills', 'label' => 'مهارات أخرى', 'type' => 'textarea', 'required' => false, 'rows' => 3],
            ],
            'cooperative' => [
                ['key' => 'name', 'label' => 'اسم المتدرب', 'label_en' => 'Trainee name', 'type' => 'text', 'required' => true, 'contact' => 'name', 'section' => 'trainee', 'col' => 6, 'placeholder' => 'الاسم كما في الهوية الوطنية', 'placeholder_en' => 'Name as on national ID'],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true, 'section' => 'trainee', 'col' => 6, 'placeholder' => '10 أرقام', 'hint' => 'رقم الهوية الوطنية أو الإقامة'],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone', 'section' => 'trainee', 'col' => 6, 'placeholder' => '5xxxxxxxx', 'hint' => 'يفضّل رقم يمكن التواصل عليه عبر واتساب'],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email', 'section' => 'trainee', 'col' => 6, 'placeholder' => 'example@email.com'],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions(), 'section' => 'trainee', 'col' => 6],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders(), 'section' => 'trainee', 'col' => 6],
                ['key' => 'education_level', 'label' => 'المؤهل', 'type' => 'select', 'required' => true, 'options' => self::educationLevels(), 'section' => 'academic', 'col' => 6],
                ['key' => 'specialization', 'label' => 'التخصص', 'label_en' => 'Academic major', 'type' => 'text', 'required' => true, 'section' => 'academic', 'col' => 6, 'placeholder' => 'مثال: إدارة أعمال، تقنية معلومات', 'placeholder_en' => 'e.g. business administration, IT'],
                ['key' => 'gpa', 'label' => 'المعدل', 'type' => 'number', 'required' => true, 'step' => '0.01', 'min' => 0, 'max' => 5, 'section' => 'academic', 'col' => 6, 'hint' => 'من 5'],
                ['key' => 'english_level', 'label' => 'مستوى الإنجليزية', 'type' => 'select', 'required' => true, 'options' => self::englishLevels(), 'section' => 'academic', 'col' => 6],
                ['key' => 'training_duration', 'label' => 'مدة التدريب', 'type' => 'select', 'required' => true, 'options' => self::trainingDurations(), 'section' => 'placement', 'col' => 6],
                ['key' => 'semester', 'label' => 'الفصل الدراسي', 'type' => 'select', 'required' => true, 'options' => self::semesters(), 'section' => 'placement', 'col' => 6],
                ['key' => 'start_date', 'label' => 'تاريخ البدء المتوقع', 'type' => 'date', 'required' => true, 'section' => 'placement', 'col' => 6],
                ['key' => 'supervisor_name', 'label' => 'اسم المشرف الأكاديمي', 'type' => 'text', 'required' => true, 'section' => 'supervisor', 'col' => 6, 'placeholder' => 'الاسم الكامل للمشرف'],
                ['key' => 'supervisor_phone', 'label' => 'جوال المشرف', 'type' => 'tel', 'required' => true, 'section' => 'supervisor', 'col' => 6, 'placeholder' => '5xxxxxxxx'],
                ['key' => 'supervisor_email', 'label' => 'بريد المشرف', 'type' => 'email', 'required' => true, 'section' => 'supervisor', 'col' => 6, 'placeholder' => 'supervisor@university.edu'],
            ],
            'fellowship' => [
                ['key' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true, 'contact' => 'name'],
                ['key' => 'email', 'label' => 'البريد', 'type' => 'email', 'required' => true, 'contact' => 'email'],
                ['key' => 'phone', 'label' => 'الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
                ['key' => 'national_id', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true],
                ['key' => 'education_level', 'label' => 'المؤهل', 'type' => 'select', 'required' => true, 'options' => self::educationLevels()],
                ['key' => 'specialization', 'label' => 'التخصص', 'type' => 'text', 'required' => true],
                ['key' => 'motivation', 'label' => 'دوافع التقديم', 'type' => 'textarea', 'required' => true],
            ],
            default => [],
        };
    }

    /** @return array<string, mixed> */
    public static function validationRules(string $type): array
    {
        $rules = [
            'terms' => ['accepted'],
        ];

        foreach (self::fieldsFor($type) as $field) {
            $isFile = ($field['type'] ?? '') === 'file';
            $key = ($isFile ? 'uploads.' : 'formData.').$field['key'];
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules = match ($field['type']) {
                'email' => array_merge($fieldRules, ['email', 'max:255']),
                'tel', 'text' => array_merge($fieldRules, ['string', 'max:255']),
                'textarea' => array_merge($fieldRules, ['string', 'max:5000']),
                'number' => array_merge($fieldRules, ['numeric']),
                'date' => array_merge($fieldRules, ['date']),
                'select', 'radio' => array_merge($fieldRules, ['string', 'max:64']),
                'file' => array_merge($fieldRules, ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx']),
                default => array_merge($fieldRules, ['string']),
            };

            if (isset($field['min'])) {
                $fieldRules[] = 'min:'.$field['min'];
            }

            if (isset($field['max'])) {
                $fieldRules[] = 'max:'.$field['max'];
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    /** @return array<string, string> */
    public static function attributeNames(string $type): array
    {
        $names = ['terms' => 'الموافقة على الشروط'];

        foreach (self::fieldsFor($type) as $field) {
            $field = self::localizeField($field);
            $isFile = ($field['type'] ?? '') === 'file';
            $names[($isFile ? 'uploads.' : 'formData.').$field['key']] = $field['label'];
        }

        return $names;
    }
}
