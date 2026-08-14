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
                'legacy_slug' => 'client-request',
                'route' => 'admin.applications.client',
                'title' => 'تقديم طلب — عميل',
                'page_title' => 'طلب تسجيل فرد',
                'page_intro' => 'سجّل بياناتك وسيتواصل معك مستشار التدريب لمساعدتك في اختيار البرنامج المناسب وإكمال عملية التسجيل.',
                'meta_description' => 'قدّم طلب تسجيلك كفرد في برامج مركز التعلم المستمر — دورات الشهادات الاحترافية والزمالات. فريقنا يراجع طلبك ويتواصل معك خلال أوقات العمل.',
                'approved_role' => 'student',
                'sections' => [
                    'contact' => 'بيانات التواصل',
                    'program' => 'البرنامج المطلوب',
                ],
            ],
            'company' => [
                'label' => 'طلب شركة',
                'legacy_slug' => 'company-request',
                'route' => 'admin.applications.company',
                'title' => 'تقديم طلب — شركة',
                'page_title' => 'طلب تسجيل شركة',
                'approved_role' => null,
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
                'legacy_slug' => 'instructor-request',
                'route' => 'admin.applications.instructor',
                'title' => 'تقديم طلب — مدرب',
                'page_title' => 'طلب انضمام كمدرب',
                'page_intro' => 'إن كنت مدرباً متخصصاً وترغب بالانضمام إلى كادر مركز التعلم المستمر، عبّئ النموذج وأرفق سيرتك الذاتية. يراجع فريقنا الطلب ويتواصل معك عند القبول لتفعيل حساب بوابة المدرب.',
                'meta_description' => 'قدّم طلب انضمامك كمدرب في مركز التعلم المستمر — أرفق خبراتك وسيرتك الذاتية ليراجعها الفريق الأكاديمي.',
                'approved_role' => 'instructor',
                'sections' => [
                    'personal' => 'البيانات الشخصية',
                    'professional' => 'الخبرة المهنية',
                    'attachments' => 'المرفقات',
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
                'legacy_slug' => 'cooperative-training',
                'route' => 'admin.applications.cooperative',
                'title' => 'تقديم طلب — تدريب تعاوني',
                'page_title' => 'التدريب التعاوني',
                'approved_role' => null,
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

    /** @return array<string, string> */
    public static function educationLevels(): array
    {
        return [
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
    public static function regions(): array
    {
        return [
            'hail' => 'الامير مقرن',
            'riyadh' => 'الرياض',
            'makkah' => 'مكة المكرمة',
            'madinah' => 'المدينة المنورة',
            'qassim' => 'القصيم',
            'eastern' => 'الشرقية',
            'asir' => 'عسير',
            'tabuk' => 'تبوك',
            'jazan' => 'جازان',
            'najran' => 'نجران',
            'northern' => 'الحدود الشمالية',
            'jouf' => 'الجوف',
            'bahah' => 'الباحة',
        ];
    }

    /** @return array<string, string> */
    public static function englishLevels(): array
    {
        return [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            'fluent' => 'طليق',
        ];
    }

    /** @return array<string, string> */
    public static function genders(): array
    {
        return [
            'male' => 'ذكر',
            'female' => 'أنثى',
        ];
    }

    /** @return array<string, string> */
    public static function yesNo(): array
    {
        return [
            'yes' => 'نعم',
            'no' => 'لا',
        ];
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
                ['key' => 'company_name', 'label' => 'اسم الشركة', 'type' => 'text', 'required' => true, 'contact' => 'name'],
                ['key' => 'activity', 'label' => 'نشاط الشركة', 'type' => 'text', 'required' => true],
                ['key' => 'responsible_name', 'label' => 'اسم المسؤول', 'type' => 'text', 'required' => true],
                ['key' => 'responsible_phone', 'label' => 'جوال المسؤول', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
                ['key' => 'responsible_email', 'label' => 'بريد المسؤول', 'type' => 'email', 'required' => true, 'contact' => 'email'],
                ['key' => 'n_employee', 'label' => 'عدد الموظفين المراد تدريبهم', 'type' => 'number', 'required' => true],
                ['key' => 'message', 'label' => 'تفاصيل الطلب', 'type' => 'textarea', 'required' => true, 'rows' => 4],
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
                ['key' => 'l_name', 'label' => 'اسم العائلة', 'type' => 'text', 'required' => true, 'contact' => 'name', 'section' => 'personal', 'col' => 6],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true, 'section' => 'personal', 'col' => 6, 'placeholder' => '10 أرقام'],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email', 'section' => 'personal', 'col' => 6, 'placeholder' => 'example@email.com'],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone', 'section' => 'personal', 'col' => 6, 'placeholder' => '05xxxxxxxx', 'hint' => 'يفضّل رقم يمكن التواصل عليه عبر واتساب'],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders(), 'section' => 'personal', 'col' => 6],
                ['key' => 'nationality', 'label' => 'الجنسية', 'type' => 'text', 'required' => true, 'section' => 'personal', 'col' => 6, 'placeholder' => 'سعودي'],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions(), 'section' => 'personal', 'col' => 6],
                ['key' => 'job_title', 'label' => 'المسمى الوظيفي الحالي', 'type' => 'text', 'required' => true, 'section' => 'professional', 'col' => 6],
                ['key' => 'specialization', 'label' => 'التخصص التدريبي', 'type' => 'text', 'required' => true, 'section' => 'professional', 'col' => 6, 'placeholder' => 'مثال: إدارة المشاريع، الأمن السيبراني'],
                ['key' => 'education_level', 'label' => 'أعلى مؤهل علمي', 'type' => 'select', 'required' => true, 'options' => self::educationLevels(), 'section' => 'professional', 'col' => 6],
                ['key' => 'years_experience', 'label' => 'سنوات الخبرة التدريبية', 'type' => 'number', 'required' => true, 'min' => 0, 'section' => 'professional', 'col' => 6],
                ['key' => 'teaching_areas', 'label' => 'المجالات التي يمكنك تدريب فيها', 'type' => 'textarea', 'required' => true, 'rows' => 3, 'section' => 'professional', 'col' => 12, 'placeholder' => 'اذكر البرامج أو المهارات التي تقدّمها'],
                ['key' => 'certificates', 'label' => 'الشهادات المهنية ذات الصلة', 'type' => 'textarea', 'required' => false, 'rows' => 2, 'section' => 'professional', 'col' => 12, 'placeholder' => 'PMP، CompTIA، SHRM… (اختياري)'],
                ['key' => 'cv', 'label' => 'السيرة الذاتية (PDF)', 'type' => 'file', 'required' => true, 'section' => 'attachments', 'col' => 6],
                ['key' => 'certificates_file', 'label' => 'مرفق الشهادات (اختياري)', 'type' => 'file', 'required' => false, 'section' => 'attachments', 'col' => 6],
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
                ['key' => 'name', 'label' => 'اسم المتدرب', 'type' => 'text', 'required' => true, 'contact' => 'name'],
                ['key' => 'ssn', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true],
                ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
                ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email'],
                ['key' => 'region', 'label' => 'المنطقة', 'type' => 'select', 'required' => true, 'options' => self::regions()],
                ['key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => true, 'options' => self::genders()],
                ['key' => 'education_level', 'label' => 'المؤهل', 'type' => 'select', 'required' => true, 'options' => self::educationLevels()],
                ['key' => 'specialization', 'label' => 'التخصص', 'type' => 'text', 'required' => true],
                ['key' => 'gpa', 'label' => 'المعدل', 'type' => 'number', 'required' => true, 'step' => '0.01', 'max' => 5],
                ['key' => 'english_level', 'label' => 'مستوى الإنجليزية', 'type' => 'select', 'required' => true, 'options' => self::englishLevels()],
                ['key' => 'training_duration', 'label' => 'مدة التدريب', 'type' => 'select', 'required' => true, 'options' => ['1_month' => 'شهر', '2_months' => 'شهران', '3_months' => '3 أشهر', '6_months' => '6 أشهر']],
                ['key' => 'semester', 'label' => 'الفصل الدراسي', 'type' => 'select', 'required' => true, 'options' => ['fall' => 'خريف', 'spring' => 'ربيع', 'summer' => 'صيف']],
                ['key' => 'start_date', 'label' => 'تاريخ البدء المتوقع', 'type' => 'date', 'required' => true],
                ['key' => 'supervisor_name', 'label' => 'اسم المشرف الأكاديمي', 'type' => 'text', 'required' => true],
                ['key' => 'supervisor_phone', 'label' => 'جوال المشرف', 'type' => 'tel', 'required' => true],
                ['key' => 'supervisor_email', 'label' => 'بريد المشرف', 'type' => 'email', 'required' => true],
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
            $isFile = ($field['type'] ?? '') === 'file';
            $names[($isFile ? 'uploads.' : 'formData.').$field['key']] = $field['label'];
        }

        return $names;
    }
}
