<?php

namespace App\Support;

use App\Models\CmsPage;

class PublicCopy
{
    /** @return array<string, array{ar: string, en: string}> */
    public static function chromeMap(): array
    {
        return [
            'home' => ['ar' => 'الرئيسية', 'en' => 'Home'],
            'about' => ['ar' => 'عن المنصة', 'en' => 'About the platform'],
            'programs' => ['ar' => 'البرامج', 'en' => 'Programs'],
            'register' => ['ar' => 'التسجيل', 'en' => 'Registration'],
            'contact' => ['ar' => 'تواصل معنا', 'en' => 'Contact us'],
            'login' => ['ar' => 'الدخول', 'en' => 'Login'],
            'dashboard' => ['ar' => 'لوحة التحكم', 'en' => 'Dashboard'],
            'wishlist' => ['ar' => 'المفضلة', 'en' => 'Wishlist'],
            'cart' => ['ar' => 'السلة', 'en' => 'Cart'],
            'learner' => ['ar' => 'متدرب', 'en' => 'Learner'],
            'learning_list' => ['ar' => 'قائمة التعلم', 'en' => 'Learning list'],
            'my_orders' => ['ar' => 'طلبات الشراء', 'en' => 'Orders'],
            'settings' => ['ar' => 'الإعدادات', 'en' => 'Settings'],
            'logout' => ['ar' => 'تسجيل الخروج', 'en' => 'Log out'],
        ];
    }

    /** Known Arabic chrome / menu labels → English. */
    /** @return array<string, string> */
    public static function menuMap(): array
    {
        return [
            'الرئيسية' => 'Home',
            'عن المنصة' => 'About the platform',
            'البرامج التدريبية' => 'Training programs',
            'البرامج' => 'Programs',
            'الشهادات الاحترافية' => 'Professional certificates',
            'الدبلومات' => 'Diplomas',
            'الزمالات المهنية' => 'Professional fellowships',
            'تسجيل الطلبات' => 'Apply and register',
            'التقديم والتسجيل' => 'Apply and register',
            'التسجيل الأكاديمي' => 'Academic registration',
            'التسجيل' => 'Registration',
            'طلب عميل (فرد)' => 'Individual client request',
            'طلب شركة' => 'Organization request',
            'طلب مدرب' => 'Instructor application',
            'التدريب التعاوني' => 'Cooperative training',
            'برنامج وعد — موظف' => 'Waad program — employee',
            'برنامج وعد — باحث' => 'Waad program — job seeker',
            'تواصل معنا' => 'Contact us',
            'الشروط والأحكام' => 'Terms and Conditions',
            'سياسة الخصوصية' => 'Privacy Policy',
            'سياسة التدريب الإلكتروني' => 'E-learning Policy',
            'سياسات الحضور والتعلّم' => 'Attendance and Learning Policies',
            'سياسات الحضور والتعلم' => 'Attendance and Learning Policies',
            'سياسات الحضور والغياب' => 'Attendance and Absence Policies',
            'سياسات نزاهة التعلم' => 'Learning Integrity Policy',
            'سياسة نزاهة التعلم' => 'Learning Integrity Policy',
            'وثيقة الأدوار والمسؤوليات' => 'Roles and Responsibilities',
            'الكادر الإشرافي' => 'Supervisory Staff',
            'الكادر الإشرافي للبيئة التدريبية' => 'Supervisory Staff',
            'سياسة الدعم الفني' => 'Technical Support Policy',
            'سياسة الدعم الفني والتعليمي' => 'Technical Support Policy',
        ];
    }

    public static function chrome(string $key, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $row = self::chromeMap()[$key] ?? null;

        if (! $row) {
            return $key;
        }

        return $row[$locale] ?? $row['ar'];
    }

    public static function fromArabic(string $labelAr, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $labelAr = trim($labelAr);

        if ($labelAr === '' || $locale !== 'en') {
            return $labelAr;
        }

        if (! preg_match('/\p{Arabic}/u', $labelAr)) {
            return $labelAr;
        }

        return self::menuMap()[$labelAr] ?? $labelAr;
    }

    public static function pageTitle(CmsPage $page, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $page->loadMissing('translations');

        $exact = $page->translations->firstWhere('locale', $locale)?->title;

        if (filled($exact)) {
            return $exact;
        }

        $arabic = (string) ($page->translations->firstWhere('locale', 'ar')?->title ?? '');

        return self::fromArabic($arabic, $locale);
    }

    public static function register(string $key, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $row = self::registerMap()[$key] ?? null;

        if (! $row) {
            return $key;
        }

        return $row[$locale] ?? $row['ar'];
    }

    /** @return array<string, array{ar: string, en: string}> */
    public static function registerMap(): array
    {
        return [
            'eyebrow' => ['ar' => 'مركز التعلم المستمر', 'en' => 'Continuing Learning Center'],
            'title_academic' => ['ar' => 'التسجيل في البرامج المعتمدة', 'en' => 'Register for accredited programs'],
            'title_account' => ['ar' => 'إنشاء حساب جديد', 'en' => 'Create an account'],
            'lead_academic' => ['ar' => 'ثلاث خطوات واضحة: بياناتك، اختيار البرنامج، ثم خطة التقسيط والتأكيد.', 'en' => 'Three clear steps: your details, choose a program, then confirm the installment plan.'],
            'lead_account' => ['ar' => 'أنشئ حسابك للوصول إلى البرامج، الطلبات، والشهادات من بوابة المتدرب.', 'en' => 'Create an account to access programs, orders, and certificates from the learner portal.'],
            'side_title' => ['ar' => 'ابدأ رحلتك التعليمية', 'en' => 'Start your learning journey'],
            'side_text' => ['ar' => 'تسجيل موحّد يربطك ببرامج الجامعة العربية المفتوحة، مع متابعة أكاديمية وشهادات يمكن التحقق منها.', 'en' => 'One registration that connects you to Arab Open University programs, academic follow-up, and verifiable certificates.'],
            'feat_programs' => ['ar' => 'برامج ودبلومات معتمدة', 'en' => 'Accredited programs and diplomas'],
            'feat_certificate' => ['ar' => 'شهادات موثّقة قابلة للتحقق', 'en' => 'Verifiable certificates'],
            'feat_installments' => ['ar' => 'خطط تقسيط واضحة', 'en' => 'Clear installment plans'],
            'feat_support' => ['ar' => 'دعم أكاديمي وفني طوال الرحلة', 'en' => 'Academic and technical support throughout'],
            'step_profile' => ['ar' => 'بياناتك', 'en' => 'Your details'],
            'step_program' => ['ar' => 'البرنامج', 'en' => 'Program'],
            'step_plan' => ['ar' => 'التقسيط', 'en' => 'Installments'],
            'section_identity' => ['ar' => 'بيانات الهوية', 'en' => 'Identity'],
            'section_contact' => ['ar' => 'بيانات التواصل', 'en' => 'Contact'],
            'section_profile' => ['ar' => 'الملف الأكاديمي', 'en' => 'Academic profile'],
            'section_security' => ['ar' => 'أمان الحساب', 'en' => 'Account security'],
            'name' => ['ar' => 'الاسم بالكامل (عربي)', 'en' => 'Full name (Arabic)'],
            'national_id' => ['ar' => 'رقم الهوية', 'en' => 'National ID'],
            'national_id_hint' => ['ar' => '10 أرقام، يبدأ بـ 1 أو 2', 'en' => '10 digits, starting with 1 or 2'],
            'phone' => ['ar' => 'رقم الجوال', 'en' => 'Mobile number'],
            'email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'nationality' => ['ar' => 'الجنسية', 'en' => 'Nationality'],
            'city' => ['ar' => 'المدينة', 'en' => 'City'],
            'gender' => ['ar' => 'الجنس', 'en' => 'Gender'],
            'employment' => ['ar' => 'الحالة الوظيفية', 'en' => 'Employment status'],
            'study_period' => ['ar' => 'فترة الدراسة', 'en' => 'Study period'],
            'choose_nationality' => ['ar' => 'اختر الجنسية', 'en' => 'Select nationality'],
            'choose_city' => ['ar' => 'اختر المدينة', 'en' => 'Select city'],
            'password' => ['ar' => 'كلمة المرور', 'en' => 'Password'],
            'password_confirm' => ['ar' => 'تأكيد كلمة المرور', 'en' => 'Confirm password'],
            'password_hint' => ['ar' => '8 أحرف على الأقل، ولا تطابق رقم الهوية أو الجوال', 'en' => 'At least 8 characters, and not the same as your ID or mobile number'],
            'show_password' => ['ar' => 'إظهار كلمة المرور', 'en' => 'Show password'],
            'terms_prefix' => ['ar' => 'أوافق على', 'en' => 'I agree to the'],
            'terms' => ['ar' => 'الشروط والأحكام', 'en' => 'Terms and Conditions'],
            'privacy' => ['ar' => 'سياسة الخصوصية', 'en' => 'Privacy Policy'],
            'and' => ['ar' => 'و', 'en' => 'and'],
            'continue_program' => ['ar' => 'متابعة — اختيار البرنامج', 'en' => 'Continue — choose a program'],
            'create_account' => ['ar' => 'إنشاء الحساب', 'en' => 'Create account'],
            'processing' => ['ar' => 'جاري المعالجة…', 'en' => 'Processing…'],
            'choose_batch' => ['ar' => 'اختر الدفعة الدراسية', 'en' => 'Choose a study cohort'],
            'choose_batch_lead' => ['ar' => 'اختر البرنامج والدفعة المناسبة. الرسوم تظهر قبل الانتقال لخطة التقسيط.', 'en' => 'Pick the program and cohort. Tuition is shown before the installment plan.'],
            'no_batches' => ['ar' => 'لا توجد دفعات مفتوحة للتسجيل حالياً. يمكنك إنشاء الحساب والعودة لاحقاً، أو التواصل معنا.', 'en' => 'No cohorts are open for registration right now. You can create an account and return later, or contact us.'],
            'sar' => ['ar' => 'ر.س', 'en' => 'SAR'],
            'seats' => ['ar' => 'مقعد متاح', 'en' => 'seats left'],
            'semester' => ['ar' => 'الفصل', 'en' => 'Semester'],
            'back' => ['ar' => 'رجوع', 'en' => 'Back'],
            'plan_title' => ['ar' => 'خطة التقسيط والتأكيد', 'en' => 'Installment plan and confirmation'],
            'plan_lead' => ['ar' => 'راجع الرسوم، اختر الخطة المناسبة، ثم أكّد للانتقال إلى توقيع العقد وسداد الدفعة الأولى.', 'en' => 'Review tuition, choose a plan, then confirm to sign the contract and pay the first installment.'],
            'selected_program' => ['ar' => 'البرنامج المختار', 'en' => 'Selected program'],
            'payments' => ['ar' => 'دفعات', 'en' => 'payments'],
            'first_payment' => ['ar' => 'الدفعة الأولى', 'en' => 'First payment'],
            'plan_note' => ['ar' => 'بعد التأكيد ستوقّع عقد التقسيط إلكترونياً ثم تسدّد الدفعة الأولى. يمكنك أيضاً إكمال التحويل البنكي عبر فريق المبيعات.', 'en' => 'After confirmation you will sign the installment contract electronically, then pay the first installment. Bank transfer via the sales team is also available.'],
            'confirm' => ['ar' => 'تأكيد التسجيل والمتابعة', 'en' => 'Confirm registration'],
            'have_account' => ['ar' => 'لديك حساب بالفعل؟', 'en' => 'Already have an account?'],
            'login_link' => ['ar' => 'تسجيل الدخول', 'en' => 'Sign in'],
            'alt_title' => ['ar' => 'مسارات تسجيل أخرى', 'en' => 'Other registration paths'],
            'alt_lead' => ['ar' => 'هذه الصفحة للمتدربين. الجهات والمدربون لهم نماذج مستقلة.', 'en' => 'This page is for learners. Organizations and instructors have their own forms.'],
            'alt_client' => ['ar' => 'طلب فرد', 'en' => 'Individual request'],
            'alt_company' => ['ar' => 'طلب جهة', 'en' => 'Organization request'],
            'alt_instructor' => ['ar' => 'التقديم كمدرب', 'en' => 'Apply as instructor'],
            'secure_note' => ['ar' => 'بياناتك تُستخدم لإكمال التسجيل الأكاديمي وفق سياسات الجامعة.', 'en' => 'Your details are used to complete academic registration under university policies.'],
            'contact_us' => ['ar' => 'تواصل معنا', 'en' => 'Contact us'],
        ];
    }

    public static function cart(string $key, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $row = self::cartMap()[$key] ?? null;

        if (! $row) {
            return $key;
        }

        return $row[$locale] ?? $row['ar'];
    }

    /** @return array<string, array{ar: string, en: string}> */
    public static function cartMap(): array
    {
        return [
            'title' => ['ar' => 'سلة البرامج', 'en' => 'Your cart'],
            'intro' => ['ar' => 'راجع البرامج المختارة ثم أكمل الشراء. اضغط اسم البرنامج لعرض تفاصيله.', 'en' => 'Review the selected programs, then complete purchase. Open a program name to see its details.'],
            'intro_empty' => ['ar' => 'أضف برنامجاً من الدليل ثم عد إلى هنا لإكمال التسجيل والسداد.', 'en' => 'Add a program from the catalog, then return here to complete enrollment and payment.'],
            'items' => ['ar' => 'العناصر', 'en' => 'Items'],
            'total' => ['ar' => 'الإجمالي', 'en' => 'Total'],
            'empty_title' => ['ar' => 'السلة فارغة', 'en' => 'Your cart is empty'],
            'empty_hint' => ['ar' => 'لم تُضف أي برنامج بعد. تصفّح الشهادات الاحترافية والدبلومات واختر ما يناسبك.', 'en' => 'You have not added a program yet. Browse professional certificates and diplomas and choose what fits.'],
            'browse' => ['ar' => 'تصفّح البرامج', 'en' => 'Browse programs'],
            'wishlist' => ['ar' => 'المفضلة', 'en' => 'Wishlist'],
            'learning_list' => ['ar' => 'قائمة التعلم', 'en' => 'Learning list'],
            'items_title' => ['ar' => 'البرامج في السلة', 'en' => 'Programs in your cart'],
            'summary_title' => ['ar' => 'ملخص السلة', 'en' => 'Cart summary'],
            'program_count' => ['ar' => 'عدد البرامج', 'en' => 'Programs'],
            'checkout' => ['ar' => 'إتمام الشراء', 'en' => 'Checkout'],
            'secure' => ['ar' => 'الدفع يتم عبر البوابات المعتمدة أو التحويل البنكي وفق سياسات المركز.', 'en' => 'Payment is processed through approved gateways or bank transfer under center policy.'],
            'guest_title' => ['ar' => 'التسجيل وإتمام الشراء', 'en' => 'Register and checkout'],
            'guest_lead' => ['ar' => 'أدخل بياناتك لإنشاء حساب المتدرب والمتابعة للسداد. نرسل بيانات الدخول إلى بريدك.', 'en' => 'Enter your details to create a learner account and continue to payment. Sign-in details are sent to your email.'],
            'name' => ['ar' => 'الاسم الكامل', 'en' => 'Full name'],
            'name_ph' => ['ar' => 'الاسم كما تود ظهوره في الحساب', 'en' => 'Name as it should appear on the account'],
            'email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'phone' => ['ar' => 'رقم الجوال', 'en' => 'Mobile number'],
            'phone_hint' => ['ar' => 'يفضّل رقم يمكن التواصل عليه عبر واتساب', 'en' => 'A number reachable on WhatsApp is preferred'],
            'submit' => ['ar' => 'تسجيل والمتابعة للسداد', 'en' => 'Register and continue to payment'],
            'submitting' => ['ar' => 'جاري إنشاء الحساب…', 'en' => 'Creating your account…'],
            'trust_1' => ['ar' => 'كلمة مرور عشوائية تُرسل إلى بريدك', 'en' => 'A random password is emailed to you'],
            'trust_2' => ['ar' => 'دفع آمن عبر بوابات معتمدة أو تحويل بنكي', 'en' => 'Secure payment via approved gateways or bank transfer'],
            'trust_3' => ['ar' => 'يتم تسجيل دخولك مباشرة بعد إنشاء الحساب', 'en' => 'You are signed in immediately after registration'],
            'have_account' => ['ar' => 'لديك حساب؟', 'en' => 'Already have an account?'],
            'login' => ['ar' => 'تسجيل الدخول', 'en' => 'Sign in'],
            'how_title' => ['ar' => 'كيف تتم العملية؟', 'en' => 'How it works'],
            'how_1' => ['ar' => 'مراجعة البرامج في السلة', 'en' => 'Review the programs in your cart'],
            'how_2' => ['ar' => 'إنشاء الحساب أو تسجيل الدخول', 'en' => 'Create an account or sign in'],
            'how_3' => ['ar' => 'إكمال السداد وتفعيل الالتحاق', 'en' => 'Complete payment and activate enrollment'],
            'empty_before' => ['ar' => 'أضف برنامجاً واحداً على الأقل قبل التسجيل.', 'en' => 'Add at least one program before registering.'],
            'flash_created' => ['ar' => 'تم إنشاء حسابك وتسجيل دخولك. راجع بريدك لكلمة المرور ثم أكمل السداد.', 'en' => 'Your account was created and you are signed in. Check your email for the password, then complete payment.'],
            'flash_existing' => ['ar' => 'تم تسجيل دخولك. يمكنك متابعة إتمام الشراء.', 'en' => 'You are signed in. You can continue to checkout.'],
            'remove' => ['ar' => 'إزالة', 'en' => 'Remove'],
            'removing' => ['ar' => '…', 'en' => '…'],
            'remove_cart' => ['ar' => 'إزالة من السلة', 'en' => 'Remove from cart'],
            'remove_wishlist' => ['ar' => 'إزالة من المفضلة', 'en' => 'Remove from wishlist'],
            'view_details' => ['ar' => 'عرض تفاصيل البرنامج', 'en' => 'View program details'],
            'diploma' => ['ar' => 'دبلوم', 'en' => 'Diploma'],
            'certificate' => ['ar' => 'شهادة احترافية', 'en' => 'Professional certificate'],
            'program' => ['ar' => 'برنامج تدريبي', 'en' => 'Training program'],
            'online' => ['ar' => 'عن بعد', 'en' => 'Online'],
            'onsite' => ['ar' => 'حضوري', 'en' => 'On-site'],
            'sar' => ['ar' => 'ر.س', 'en' => 'SAR'],
            'continue' => ['ar' => 'متابعة التصفح', 'en' => 'Continue browsing'],
            'faq' => ['ar' => 'الأسئلة الشائعة', 'en' => 'FAQ'],
            'links_title' => ['ar' => 'روابط مفيدة', 'en' => 'Helpful links'],
        ];
    }

    public static function wishlist(string $key, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $row = self::wishlistMap()[$key] ?? null;

        if (! $row) {
            return $key;
        }

        return $row[$locale] ?? $row['ar'];
    }

    /** @return array<string, array{ar: string, en: string}> */
    public static function wishlistMap(): array
    {
        return [
            'title' => ['ar' => 'البرامج المفضلة', 'en' => 'Saved programs'],
            'intro' => ['ar' => 'البرامج التي حفظتها للمراجعة لاحقاً. افتح الاسم لعرض التفاصيل، ثم أضف ما يناسبك إلى السلة.', 'en' => 'Programs you saved for later. Open a name for details, then add what you want to the cart.'],
            'intro_empty' => ['ar' => 'احفظ البرامج التي تهمك من الدليل ثم عد إلى هنا لمراجعتها قبل الشراء.', 'en' => 'Save programs from the catalog, then return here to review them before purchase.'],
            'items' => ['ar' => 'المحفوظة', 'en' => 'Saved'],
            'empty_title' => ['ar' => 'لا توجد برامج محفوظة', 'en' => 'No saved programs'],
            'empty_hint' => ['ar' => 'عند تصفّح الدليل يمكنك حفظ البرنامج للمراجعة لاحقاً دون إضافته إلى السلة.', 'en' => 'While browsing the catalog you can save a program for later without adding it to the cart.'],
            'browse' => ['ar' => 'تصفّح البرامج', 'en' => 'Browse programs'],
            'cart' => ['ar' => 'عرض السلة', 'en' => 'View cart'],
            'items_title' => ['ar' => 'البرامج المحفوظة', 'en' => 'Saved programs'],
            'summary_title' => ['ar' => 'ملخص المفضلة', 'en' => 'Wishlist summary'],
            'saved_count' => ['ar' => 'عدد البرامج المحفوظة', 'en' => 'Saved programs'],
            'how_title' => ['ar' => 'كيف تستخدم المفضلة؟', 'en' => 'How the wishlist works'],
            'how_1' => ['ar' => 'حفظ البرنامج من صفحة الدليل', 'en' => 'Save a program from the catalog'],
            'how_2' => ['ar' => 'مراجعة التفاصيل هنا في أي وقت', 'en' => 'Review the details here anytime'],
            'how_3' => ['ar' => 'نقل ما يناسبك إلى السلة لإكمال الشراء', 'en' => 'Move what fits to the cart to complete purchase'],
            'point_1' => ['ar' => 'حفظ بلا التزام بالسداد', 'en' => 'Save without a payment commitment'],
            'point_2' => ['ar' => 'مقارنة البرامج قبل الاختيار', 'en' => 'Compare programs before you choose'],
            'point_3' => ['ar' => 'السلة تبقى منفصلة حتى تضيف البرنامج إليها', 'en' => 'The cart stays separate until you add a program'],
            'login_note' => ['ar' => 'سجّل الدخول للاحتفاظ بالمفضلة على حسابك.', 'en' => 'Sign in to keep your wishlist on your account.'],
            'login' => ['ar' => 'تسجيل الدخول', 'en' => 'Sign in'],
            'links_title' => ['ar' => 'روابط مفيدة', 'en' => 'Helpful links'],
            'faq' => ['ar' => 'الأسئلة الشائعة', 'en' => 'FAQ'],
            'learning_list' => ['ar' => 'قائمة التعلم', 'en' => 'Learning list'],
        ];
    }

    public static function apply(string $key, ?string $locale = null): string
    {
        $locale = self::locale($locale);
        $row = self::applyMap()[$key] ?? null;

        if (! $row) {
            return $key;
        }

        return $row[$locale] ?? $row['ar'];
    }

    public static function applyForType(string $key, string $type, ?string $locale = null): string
    {
        $typed = $key.'_'.$type;

        if (isset(self::applyMap()[$typed])) {
            return self::apply($typed, $locale);
        }

        return self::apply($key, $locale);
    }

    /** @return array<string, array{ar: string, en: string}> */
    public static function applyMap(): array
    {
        return [
            'home' => ['ar' => 'الرئيسية', 'en' => 'Home'],
            'choose' => ['ar' => 'اختر', 'en' => 'Select'],
            'submit' => ['ar' => 'إرسال الطلب', 'en' => 'Submit request'],
            'sending' => ['ar' => 'جاري الإرسال…', 'en' => 'Sending…'],
            'uploading' => ['ar' => 'جاري رفع الملف…', 'en' => 'Uploading…'],
            'cancel' => ['ar' => 'إلغاء والعودة للبرامج', 'en' => 'Cancel and back to programs'],
            'terms_prefix' => ['ar' => 'أوافق على', 'en' => 'I agree to the'],
            'terms' => ['ar' => 'الشروط والأحكام', 'en' => 'Terms and Conditions'],
            'privacy' => ['ar' => 'سياسة الخصوصية', 'en' => 'Privacy Policy'],
            'and' => ['ar' => 'و', 'en' => 'and'],
            'success_title' => ['ar' => 'تم إرسال طلبك بنجاح', 'en' => 'Your request was submitted'],
            'success_keep' => ['ar' => 'احتفظ برقم الطلب للمتابعة:', 'en' => 'Keep this reference number to track your request:'],
            'success_lead' => ['ar' => 'سيتواصل معك فريق التسجيل بعد مراجعة الطلب خلال أوقات العمل الرسمية.', 'en' => 'The registration team will contact you after reviewing the request during official working hours.'],
            'track' => ['ar' => 'متابعة الطلب', 'en' => 'Track request'],
            'browse' => ['ar' => 'تصفّح البرامج', 'en' => 'Browse programs'],
            'home_link' => ['ar' => 'العودة للرئيسية', 'en' => 'Back to home'],
            'course_request' => ['ar' => 'طلب تسجيل للبرنامج', 'en' => 'Program registration request'],
            'course_wanted' => ['ar' => 'البرنامج المطلوب', 'en' => 'Requested program'],
            'how_title' => ['ar' => 'كيف تتم المعالجة؟', 'en' => 'How it works'],
            'how_1' => ['ar' => 'تعبئة النموذج وإرسال الطلب', 'en' => 'Complete the form and submit'],
            'how_2' => ['ar' => 'مراجعة الطلب من فريق التسجيل', 'en' => 'The registration team reviews it'],
            'how_3' => ['ar' => 'التواصل معك لإتمام التسجيل أو توجيهك للبرنامج المناسب', 'en' => 'We contact you to complete enrollment or recommend a program'],
            'links_title' => ['ar' => 'روابط مفيدة', 'en' => 'Helpful links'],
            'track_prev' => ['ar' => 'متابعة طلب سابق', 'en' => 'Track a previous request'],
            'faq' => ['ar' => 'الأسئلة الشائعة', 'en' => 'FAQ'],
            'account_title' => ['ar' => 'حسابك', 'en' => 'Your account'],
            'account_lead' => ['ar' => 'أنت مسجّل الدخول. تم تعبئة بعض الحقول تلقائياً.', 'en' => 'You are signed in. Some fields were filled automatically.'],
            'profile' => ['ar' => 'الملف الشخصي', 'en' => 'Profile'],
            'secure' => ['ar' => 'بياناتك تُراجع وفق سياسات الجامعة ولا تُستخدم إلا لإكمال التسجيل.', 'en' => 'Your details are reviewed under university policy and used only to complete registration.'],
            'paths_title' => ['ar' => 'مسار الطلب', 'en' => 'Request path'],
            'path_client' => ['ar' => 'فرد', 'en' => 'Individual'],
            'path_company' => ['ar' => 'جهة', 'en' => 'Organization'],
            'path_instructor' => ['ar' => 'مدرب', 'en' => 'Instructor'],
            'path_cooperative' => ['ar' => 'تعاوني', 'en' => 'Cooperative'],
            'path_academic' => ['ar' => 'تسجيل أكاديمي', 'en' => 'Academic registration'],
            'need_account' => ['ar' => 'هل تريد حساب متدرب مباشرة؟', 'en' => 'Need a learner account now?'],
            'create_account' => ['ar' => 'إنشاء حساب', 'en' => 'Create account'],
            'how_1_company' => ['ar' => 'إرسال احتياج الجهة وعدد الكوادر', 'en' => 'Send the organization need and headcount'],
            'how_2_company' => ['ar' => 'مراجعة الطلب واقتراح البرامج المناسبة', 'en' => 'We review the request and propose suitable programs'],
            'how_3_company' => ['ar' => 'التواصل مع المسؤول لتنسيق التنفيذ والتعاقد', 'en' => 'We contact the representative to coordinate delivery'],
            'org_points_title' => ['ar' => 'ماذا نقدّم للجهات؟', 'en' => 'What we offer organizations'],
            'org_point_1' => ['ar' => 'برامج مخصصة وفق احتياج الكوادر', 'en' => 'Programs tailored to your workforce'],
            'org_point_2' => ['ar' => 'تدريب مجموعات داخل الجهة أو عن بُعد', 'en' => 'Group training on-site or online'],
            'org_point_3' => ['ar' => 'تنسيق مباشر مع مسؤول التدريب', 'en' => 'Direct coordination with your training lead'],
            'secure_company' => ['ar' => 'بيانات الجهة تُستخدم لتنسيق التدريب ولا تُشارك خارج فريق التسجيل.', 'en' => 'Organization details are used to coordinate training and are not shared outside the registration team.'],
            'org_cta_title' => ['ar' => 'استفسار قبل الإرسال؟', 'en' => 'Questions before you send?'],
            'org_cta_lead' => ['ar' => 'فريق البرامج يساعدك في تحديد الاحتياج وصياغة العرض المناسب لجهتك.', 'en' => 'The programs team can help define the need and shape a suitable proposal for your organization.'],
            'org_cta' => ['ar' => 'تواصل مع فريق البرامج', 'en' => 'Contact the programs team'],
            'how_1_instructor' => ['ar' => 'تعبئة البيانات المهنية وإرفاق السيرة الذاتية', 'en' => 'Complete your professional details and attach a CV'],
            'how_2_instructor' => ['ar' => 'مراجعة الطلب من الفريق الأكاديمي', 'en' => 'The academic team reviews the application'],
            'how_3_instructor' => ['ar' => 'التواصل عند القبول لتفعيل بوابة المدرب', 'en' => 'If accepted, we contact you to activate the instructor portal'],
            'instructor_points_title' => ['ar' => 'ماذا يعني الانضمام؟', 'en' => 'What joining means'],
            'instructor_point_1' => ['ar' => 'تقديم برامج الشهادات الاحترافية والزمالات', 'en' => 'Deliver professional certificate and fellowship programs'],
            'instructor_point_2' => ['ar' => 'التنسيق مع الفريق الأكاديمي على المحتوى والجداول', 'en' => 'Coordinate content and schedules with the academic team'],
            'instructor_point_3' => ['ar' => 'حساب بوابة المدرب بعد قبول الطلب', 'en' => 'Instructor portal access after approval'],
            'secure_instructor' => ['ar' => 'بياناتك ومرفقاتك تُراجع من الفريق الأكاديمي ولا تُستخدم إلا لتقييم طلب الانضمام.', 'en' => 'Your details and attachments are reviewed by the academic team and used only to assess this application.'],
            'instructor_cta_title' => ['ar' => 'استفسار عن الانضمام؟', 'en' => 'Questions about joining?'],
            'instructor_cta_lead' => ['ar' => 'يمكنك التواصل معنا قبل الإرسال إذا أردت معرفة مجالات الاحتياج الحالية.', 'en' => 'You can contact us before submitting if you want to know current teaching needs.'],
            'instructor_cta' => ['ar' => 'تواصل مع الفريق الأكاديمي', 'en' => 'Contact the academic team'],
            'how_1_cooperative' => ['ar' => 'إرسال البيانات الأكاديمية ومدة التدريب المطلوبة', 'en' => 'Send academic details and the requested training duration'],
            'how_2_cooperative' => ['ar' => 'مراجعة الطلب ومدى توفر مقعد التدريب', 'en' => 'We review the request and placement availability'],
            'how_3_cooperative' => ['ar' => 'التواصل معك ومع المشرف الأكاديمي لتنسيق الالتحاق', 'en' => 'We contact you and the academic supervisor to coordinate the placement'],
            'cooperative_points_title' => ['ar' => 'ماذا يشمل التدريب التعاوني؟', 'en' => 'What cooperative training includes'],
            'cooperative_point_1' => ['ar' => 'تدريب ميداني وفق التخصص الدراسي', 'en' => 'Field training aligned with your major'],
            'cooperative_point_2' => ['ar' => 'تنسيق مباشر مع المشرف الأكاديمي', 'en' => 'Direct coordination with the academic supervisor'],
            'cooperative_point_3' => ['ar' => 'متابعة خلال فترة التدريب المتفق عليها', 'en' => 'Follow-up during the agreed training period'],
            'secure_cooperative' => ['ar' => 'بياناتك وبيانات المشرف تُستخدم لتنسيق مقعد التدريب ولا تُشارك خارج فريق التسجيل.', 'en' => 'Your details and supervisor contacts are used to coordinate a placement and are not shared outside the registration team.'],
            'cooperative_cta_title' => ['ar' => 'استفسار عن التدريب التعاوني؟', 'en' => 'Questions about cooperative training?'],
            'cooperative_cta_lead' => ['ar' => 'فريق التدريب يساعدك في معرفة المدد المتاحة ومتطلبات التخصص قبل الإرسال.', 'en' => 'The training team can help with available durations and major requirements before you submit.'],
            'cooperative_cta' => ['ar' => 'تواصل مع فريق التدريب', 'en' => 'Contact the training team'],
        ];
    }

    protected static function locale(?string $locale): string
    {
        $locale ??= app()->getLocale();

        return in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';
    }
}
