<?php

namespace App\Support;

class CmsBlockRegistry
{
    /** @return array<string, array{label: string, description?: string}> */
    public static function types(): array
    {
        return [
            'hero' => ['label' => 'بانر الصفحة الرئيسية', 'description' => 'السلايدر العلوي مع نموذج البحث'],
            'catalog_section' => ['label' => 'قسم كatalog ديناميكي', 'description' => 'دورات أو دبلومات من قاعدة البيانات'],
            'cards_grid' => ['label' => 'بطاقات (مهمة / رؤية / أهداف)', 'description' => 'شبكة بطاقات بأيقونة وعنوان ونص'],
            'image_cards' => ['label' => 'بطاقات صور', 'description' => 'برنامج مهارات وبرامج مشابهة'],
            'logo_carousel' => ['label' => 'شعارات (شركاء / اعتماد)', 'description' => 'عرض شعارات متحركة'],
            'news_cards' => ['label' => 'الأخبار والفعاليات', 'description' => 'أحدث المقالات المنشورة'],
            'testimonials' => ['label' => 'آراء العملاء', 'description' => 'شهادات وتقييمات'],
            'faq' => ['label' => 'الأسئلة الشائعة', 'description' => 'أكورديون أسئلة وأجوبة'],
            'stats' => ['label' => 'المنصة في أرقام', 'description' => 'عدادات وإحصائيات'],
            'features_grid' => ['label' => 'مميزات المنصة', 'description' => 'شبكة مميزات بأيقونات'],
            'rich_text_split' => ['label' => 'نص + صورة', 'description' => 'مقدمة عن المنصة'],
            'download_cta' => ['label' => 'تنزيل ملف تعريفي', 'description' => 'دعوة لتحميل PDF'],
            'breadcrumb' => ['label' => 'مسار التنقل', 'description' => 'عنوان الصفحة الداخلية'],
            'contact_intro' => ['label' => 'مقدمة تواصل معنا', 'description' => 'نص ترحيبي وأزرار سريعة'],
            'contact_channels' => ['label' => 'قنوات التواصل', 'description' => 'بطاقات البريد والجوال وواتساب والعنوان'],
            'contact_map_form' => ['label' => 'الخريطة ونموذج التواصل', 'description' => 'خريطة Google ونموذج إرسال الرسالة'],
        ];
    }

    /** @return list<string> */
    public static function blockPageTypes(): array
    {
        return ['home', 'about', 'contact'];
    }

    public static function label(string $type): string
    {
        return self::types()[$type]['label'] ?? $type;
    }

    /** @return list<string> */
    public static function catalogSources(): array
    {
        return ['popular_fields', 'certificates', 'diplomas'];
    }
}
